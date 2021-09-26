<?php
/**
 * Authentication class
 * Check updates and validates for API Key before updating
 **/
 
 
class NM_Auth_PPOM_Texter {
    
	var $portal_url;
    var $api_endpoint;
    var $plugin_path;
    
    /**
	 * the static object instace
	 */
	private static $ins = null;
	
    function __construct($path, $redirect_url, $plugin_id) {
    	
        
        $this -> plugin_path	= $path;
        $this -> redirect_url	= $redirect_url;
        $this -> plugin_id		= $plugin_id;
        
        $this -> portal_url     = 'https://clients.najeebmedia.com/login/';
        
        $this -> api_endpoint	= 'https://clients.najeebmedia.com/wp-json/nmclient/v1/validate-apikey';
        // $this -> api_endpoint	= 'https://theproductionarea.net/wp-json/nmclient/v1/validate-apikey';
        
        add_action('admin_menu', array($this, 'show_api_key_menu'));
        
        add_action('wp_ajax_nm_validate_api', array($this, 'validate_api'));
        
        // validation response received
        add_action('wp_ajax_nm_regiter_'.$this -> plugin_id, array($this, 'register_server'));
        
        //Auto update notification
		// add_filter('upgrader_pre_install', array($this, 'verify_plugin_before_update'), 10, 2);
		
		// Debuging
		// $this->remove_plugin_keys();
    }
	
	public static function get_instance($path, $redirect_url, $plugin_id)
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new NM_Auth_PPOM_Texter($path, $redirect_url, $plugin_id);
		return self::$ins;
	}
	
	function remove_plugin_keys () {
		
		$plugin_key = $this->get_plugin_keyname();
		delete_option( $plugin_key );
	}
	
	
	function get_plugin_path() {
		
		return $this->plugin_path;
	}
	
	function verify_plugin_before_update($response, $hooks) {
		
		if( isset($hooks['plugin']) && $hooks['plugin'] != $this->plugin_path )
			return $response;
			
		$apikey = get_option( $this->get_plugin_keyname() );
		$action = 'checking_api';
		
		$response = $this -> verify_form_server( $apikey, $action );
		$response = json_decode($response['body'], true);
		
		if( isset($response['status']) && $response['status']!= 'success' ) {
			
			return new WP_Error( 'plugin_not_verified', __( 'Your plugin license is not valid, Download plugin API KEY from <a target="_blank" href="'.esc_url($this -> portal_url).'">Client Portal</a> or visit <a target="_blank" href="https://najeebmedia.com">NajeebMedia</a>', "nm-auth" ) );
		}
		
		return $response;
	}
	
	
	function show_api_key_menu() {
	    
	    if( $this -> api_key_found() ) {
	        
	        return;
	    }
	    
	    
	    $plugin_data = $this->get_plugin_info();
	    // var_dump($plugin_data); exit;
	    $page_title = $plugin_data['Name'] . ' - Please verify your plugin.';
	    $menu_title = 'PPOM - API Key';
	    $cap        = 'manage_options';
	    $slug       = $plugin_data['TextDomain'].'_auth';
	    $call_back  = 'validate_plugin';
	    $menu_logo	= null;
	    $menu_pos   = 88;
	    
	    $menu = add_menu_page($page_title, $menu_title, $cap, $slug, array($this, $call_back), $menu_logo,$menu_pos);
	    
	    add_action('admin_print_scripts-'.$menu, array($this, 'load_scripts_admin'));
						
	}
	
	function load_scripts_admin() {
	    
	    $plugin_url		= plugin_dir_url( __FILE__ );
	    $script_source  = $plugin_url .'validate.js';
	    wp_enqueue_script ( 'nm-validation', $script_source, 'jquery' );
	    
	    $auth_vars = array('redirect_url'=>$this->redirect_url);
	    wp_localize_script('nm-validation', 'auth_vars', $auth_vars);	
	}
	
	
	function validate_api() {
	    
	    if ( 
            ! isset( $_POST['validation_nonce'] ) 
            || ! wp_verify_nonce( $_POST['validation_nonce'], 'valiation_checking' ) 
        ) {
        
           print 'Sorry, your nonce did not verify.';
           exit;
        
        }
       
    	$apikey = trim( sanitize_text_field($_REQUEST['plugin_api_key']));
    	$action	= sanitize_text_field( $_REQUEST['_xtion_'] );
    	
	    $response = $this -> verify_form_server( $apikey, $action );
	    
	    $response = apply_filters('nm_validation_respose', $response);
	    
	    wp_send_json( $response );
	}
	
	
	function verify_form_server($apikey, $action) {
		
		$domain	= $_SERVER['HTTP_HOST'];
    	$ip		= $_SERVER['REMOTE_ADDR'];
    	
    	// removing www. part
    	$domain	= trim($domain, 'www.');
        
        $post_data = array('body'	=> array( 'apikey' => $apikey, 
        										'action' => $action, 
        										'domain' => $domain,
        										'ip'	=> $ip));
        										
        $response = wp_remote_post( $this->api_endpoint, $post_data );
        $resp = array();
        if( is_wp_error( $response ) ) {
        	$resp = array('body'=> "{\"message\":\"Plugin activated successfully\",\"status\":\"success\"}");
        	$response = $resp;
        }
        
        return $response;
	}
	
	/**
	 * after getting response from Endpoint, register current machine
	 **/
	function register_server( $response ) {
		
		update_option( $this->get_plugin_keyname(), $_POST['apikey']);
		
		die(0);
	}
	
	// update checker
	function check_update() {
		
		$plugin_id = $this->plugin_id;
		include_once( dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php' );
		
		$plugin_data = $this->get_plugin_info();
		$plugin_path = WP_PLUGIN_DIR . '/' . $this->plugin_path;
		
		$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		    // 'http://wordpresspoets.com/wp-update-server/?action=get_metadata&slug=nm-woocommerce-checkout-field-manager',
		    "https://s3.amazonaws.com/nm-plugins/{$plugin_id}/plugin-info.json",
		    $plugin_path,
		    // 'nm-woocommerce-checkout-field-manager'
		    $plugin_data['TextDomain']
		);	
		
	}
	
	// Get plugin info
	function get_plugin_info() {
		
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->plugin_path );
		
		return $plugin_info;
	}
	
	
	/**
	 * checking if api key is set
	 **/
	function api_key_found() {
	    
	   //var_dump($this->get_plugin_keyname(), get_option( $this->get_plugin_keyname() )); exit;
	   if( get_option( $this->get_plugin_keyname() ) )
	    	return true;
	    else
	    	return false;
	}
	
	
	/**
	 * rendering validation form
	 **/
	function validate_plugin() {
	    
	    echo '<div class="wrap">';
		echo '<h2>' . __ ( 'Provide API key below (Please use Purchase Code for CodeCanyone):', 'nm-auth' ) . '</h2>';
		echo '<p>' . __ ( 'If you don\'t know your API key, please login into your: <a target="_blank" href="'.esc_url($this -> portal_url).'">Client Portal</a>', 'nm-auth' ) . '</p>';
		echo '<p>' . __ ( 'OR Enter Purchase Code from CodeCanyone', 'nm-auth' ) . '</p>';
		
		echo '<form onsubmit="return validate_api_wooproduct(this)">';
			echo '<p><label for="plugin_api_key">'.__('Entery API key', 'nm-auth').':</label><br /><input type="text" name="plugin_api_key" id="plugin_api_key" /></p>';
			wp_nonce_field( 'valiation_checking', 'validation_nonce' );
			echo '<input type="hidden" name="_xtion_" value="checking_api">';
			echo '<input type="hidden" id="nm_action" value="nm_regiter_'.$this -> plugin_id.'">';
			echo '<p><input type="submit" class="button-primary button" /></p>';
			echo '<p id="nm-sending-api" style="display:none">Please wait ...</p>';
		echo '</form>';
		
		echo '</div>';
	}
	
	function get_plugin_keyname() {
		
		$plugin_data = $this->get_plugin_info();
		return '_x_'.$plugin_data['TextDomain'].'_x_';
	}
}

if( ! function_exists('NM_AUTH_TEXTER') ) {
	function NM_AUTH_TEXTER($path, $redirect_url, $plugin_id) {
		// return new NM_Auth($path, $redirect_url, $plugin_id);
		return NM_Auth_PPOM_Texter::get_instance($path, $redirect_url, $plugin_id);
	}
}
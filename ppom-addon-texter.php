<?php
/**
 * Plugin Name: PPOM Addon For Texter
 * Plugin URI: http://najeebmedia.com
 * Description: An addon to PPOM
 * Version: 1.0
 * Author: Najeeb Ahmad
 * Author URI: http://najeebmedia.com
 * Text Domain: ppom-texter
 * License: GPL2
 */

/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

// Authencation checking
if( ! class_exists('NM_Auth_PPOM_Texter') ) {
	$_auth_class = dirname(__FILE__).'/Auth/auth.php';
	if( file_exists($_auth_class))
		include_once($_auth_class);
	else
		die('Reen, Reen, BUMP! not found '.$_auth_class);
}
 
/* 
**============= Define constant ================ 
*/
define('TEXTER_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('TEXTER_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define('TEXTER_WP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __DIR__ ) ));
define('TEXTER_VERSION', 2.0 );

/**
 * Plugin API Validation
 * *** DO NOT REMOVE These Lines
 * */
define('TEXTER_PLUGIN_PATH', "ppom-addon-texter/ppom-addon-texter.php");
define('TEXTER_REDIRECT_URL', admin_url( 'admin.php?page=ppom' ));
define('TEXTER_PLUGIN_ID', 5534);
define('TEXTER_TEXT_DOMAIN', 'ppom-texter');
NM_AUTH_TEXTER(TEXTER_PLUGIN_PATH, TEXTER_REDIRECT_URL, TEXTER_PLUGIN_ID);
 
class PPOM_Texter {
    
    /**
	 * the static object instace
	 */
	private static $ins = null;
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
    
    function __construct() {
       
        if( ! $this->is_plugin_validated() ) {
            add_action( 'admin_notices', array($this, 'plugin_notice_not_validated') );
            return '';
        }
     
        // init texter cpt name nm_ppom_texter 
        add_action( 'init', array($this, 'ppom_design_cpt_register') );

        // texter metaboxes add
        add_action( 'add_meta_boxes', array($this, 'ppom_design_display_metabox' ) );
        add_action( 'add_meta_boxes', array($this, 'ppom_texter_panel_setting_metabox' ) );

        // texter saving meta hook
        add_action( 'save_post', array($this, 'save_ppom_design_data') );

        // enqueued media script
        add_action ( 'admin_enqueue_scripts', function () {
    		if (is_admin ())
        		wp_enqueue_media ();
		});

        add_action('ppom_hooks_inputs', array($this, 'hook_input_scripts'), 10, 2 );
        
        // rendering inputs
        add_action('ppom_rendering_inputs', array($this, 'render_input_texter'), 10, 5 );

        // File path
        add_filter('nm_input_class-texter', array($this, 'addon_path_texter'), 10, 2);

        // Loading all input in PRO
        add_filter('ppom_all_inputs', array($this, 'load_addon'), 10, 2);

        add_action( 'admin_init', array($this, 'render_files_in_orders') );
        
        // Adding meta to cart form product page
		add_filter ( 'woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2);
		add_action ( 'woocommerce_add_order_item_meta', array($this, 'add_order_item_meta'), 10, 3);
		
        // Show item meta data on cart/checkout pages.
		add_filter ( 'woocommerce_get_item_data', array($this, 'add_item_meta'), 10, 2 );
		
    }
    
    /* 
    **====== Add texter cart meta ========= 
    */
    function add_cart_item_data( $cart, $product_id ) {
        
        if( isset($_POST['ppom_texter']) ) {
            
            $cart['ppom_texter'] = $_POST['ppom_texter'];
        }
        
        return $cart;
    }
    
    /* 
    **====== Add texter order meta ========= 
    */
    function add_order_item_meta($item_id, $cart_item, $cart_item_key) {
		
		if ( ! isset ( $cart_item['ppom_texter'] )) {
			return;
		}
		
	    $texter_meta = array();
		$product_id = $cart_item['product_id'];
		foreach($cart_item['ppom_texter'] as $key => $val) {
			    
		    $file_name = "{$key}.png";
		    $file_name = ppom_file_get_name($file_name, $product_id);
			
			$meta_image = ppom_create_thumb_for_meta($file_name, $product_id);
		   	
		   	// only image thumb
			$key = "texter_image_{$key}";
			ppom_add_order_item_meta($item_id, $key, $meta_image);
    			
		}
		
		// order meta
		$texter_meta[$product_id] = $cart_item['ppom_texter'];
		// All texter info
		$key = "texter_all";
		$order_id = wc_get_order_id_by_order_item_id($item_id);
		update_post_meta($order_id, $key, $texter_meta);
		
	}

    /* 
    **====== Add texter meta ========= 
    */
    function add_item_meta($item_meta, $cart_item) {
		
		if( isset($cart_item['ppom_texter']) ) {
			
			foreach($cart_item['ppom_texter'] as $key => $val) {
			    
			    if( ! isset($val['image_data_url']) ) continue;
			    
			    $product_id = $cart_item['product_id'];
			    
			    $file_name = "{$key}.png";
			    $file_name = ppom_file_get_name($file_name, $product_id);
				
				$hidden 	= false;
				
				
				$image_name = $this->save_texter_image($file_name, $val['image_data_url'], $product_id);
				$meta_image = ppom_create_thumb_for_meta($file_name, $product_id);
				$item_meta[] = array('name'	=> ($key), 'value' => $val, 'hidden' => $hidden, 'display'=>$meta_image);
			}
		}
		
		return $item_meta;
	}
	
    /* 
    **====== Save create texter frontent image ========= 
    */
	function save_texter_image( $file_name, $image_data, $product_id) {
	    
	    $text_destination = ppom_get_dir_path() . $file_name;
	    
	    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image_data));
    	
    	file_put_contents( $text_destination, $image_data);
    	if( file_exists($text_destination) ) {
    	    
    	    $file_dir_path = ppom_get_dir_path();
    	    $thumb_size  = ppom_get_thumbs_size();
    	    $thumb_dir_path = ppom_create_image_thumb($file_dir_path, $file_name, $thumb_size);
    	}
    	
    	return $text_destination;
	}

    /* 
    **============= Load scripts ================ 
    */
    function hook_input_scripts($field, $data_name) {

        if( $field['type'] != 'texter' ) return '';
        
        if( $field['type'] == 'texter' ) {
            // ppom_pa($field);
            // font-awesom file
            wp_enqueue_style('texter-font-awsm', TEXTER_URL."/css/font-awesome/css/font-awesome.css");
            
            // Remodel files
            wp_enqueue_style('texter-model', TEXTER_URL."/css/jquery.remodal.css");
            wp_enqueue_script('texter-model', TEXTER_URL."/js/jquery.remodal.js", array('jquery'), TEXTER_VERSION, true);
            
            // Font select files
            wp_enqueue_style('texter-f-family', TEXTER_URL."/css/fontselect-alternate.css");
            wp_enqueue_script('texter-f-family', TEXTER_URL."/js/jquery.fontselect.js", array('jquery'), TEXTER_VERSION, true);
            
            // Texter frontent files
            wp_enqueue_style('texter-style', TEXTER_URL."/css/texter-frontent.css");
            wp_enqueue_script('texter-js', TEXTER_URL."/js/texter-frontent.js", array('jquery'), TEXTER_VERSION, true);

            // Canvas file load
            // wp_enqueue_script('texter-canvas', TEXTER_URL."/js/canvas2.js", array('jquery'), TEXTER_VERSION, true);
            wp_enqueue_script('texter-canvas', TEXTER_URL."/js/html2canvas.js", array('jquery'), TEXTER_VERSION, true);

            // Iris color picker load
            wp_enqueue_script(
                'iris',
                admin_url( 'js/iris.min.js' ),
                array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
                false,
                1
            );
            
            
        }
    }

    /* 
    **============= Texter inputs path load ================ 
    */
    function addon_path_texter($path, $type) {
        
        if( file_exists(TEXTER_PATH. '/classes/input.texter.php') ) {
             $path = TEXTER_PATH. "/classes/input.{$type}.php";
        }
        return $path;
    }

    /* 
    **============= Loading all PRO inputs ================ 
    */
    function load_addon( $inputs_array, $inputObj) {
       
       // checking fixedprice addon is enable
        $inputs_array['texter'] = $inputObj->get_input ( 'texter' );

        return $inputs_array;
    }

    /* 
    **============= Frontent meta rendering ================ 
    */
    function render_input_texter($meta, $data_name, $classes, $field_label, $options) {
        
        if( $meta['type'] != 'texter' ) return '';

        $meta['id'] = $data_name;
        $input_wrapper_class = 'ppom-texter-frontent-wrapper form-group';
        $input_wrapper_class = apply_filters('ppom_input_wrapper_class', $input_wrapper_class, $meta);

        $meta['texter_label'] = $field_label;

        $ppom_template = '';
        $template_vars = array();
        $template_vars = array('texter_meta' => $meta, 'texter_class'=> $input_wrapper_class);

        $ppom_template = '/shortcode/ppom-textbox-render.php';

        $this->load_template ( $ppom_template, $template_vars );
    }

    /* 
    **============= Saving texter meta via save hook ================ 
    */
    function save_ppom_design_data( $post_id ) {

    	// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;

		$post_type = get_post_type($post_id);
		if ( "nm_ppom_texter" != $post_type ) return;

		if( isset($_POST['ppom_design']) || isset($_POST['ppom_image_upload']) ) {

			update_post_meta($post_id, 'ppom_design_meta', $_POST['ppom_design']);
			update_post_meta($post_id, 'ppom_image_upload', $_POST['ppom_image_upload']);
		}
    }

    /* 
    **============= Getting last textbox id  ================ 
    */
    function get_last_box_id( $setting_meta ) {

    	return max(array_keys($setting_meta));
    }
    
    /* 
    **============= Load templates function ================ 
    */
    function load_template( $template_name, $vars = null) {

        if( $vars != null && is_array($vars) ){
            extract( $vars );
        };

        $template_path =  TEXTER_PATH . "/templates/{$template_name}";
        if( file_exists( $template_path ) ){
            require ( $template_path );
        } else {
            die( "Error while loading file {$template_path}" );
        }
    }

    /* 
    **============= Texter CPT ================ 
    */
	function ppom_design_cpt_register() {

		$labels = array(
				'name'               => _x( 'PPOM Texter', 'ppom-texter' ),
				'add_new'            => _x( 'Add New', 'ppom-texter' ),
				'add_new_item'       => __( 'Add New', 'ppom-texter' ),
				'edit'				 => __('Edit us', 'ppom-texter' ),
				'new_item'           => __( 'New Texter', 'ppom-texter' ),
				'edit_item'          => __( 'Edit Texter', 'ppom-texter' ),
				'view'				 => __('View', 'ppom-texter' ),
				'view_item'          => __( 'Texter', 'ppom-texter' ),
				'all_items'          => __( 'Texters', 'ppom-texter' ),
				'search_items'       => __( 'Search Texters', 'ppom-texter' ),
				'not_found'          => __( 'No Texter found.', 'ppom-texter' ),
				'not_found_in_trash' => __( 'No Texters found in Trash.', 'ppom-texter' ),
				'parent'			 => __( 'Parent Texter', 'ppom-texter' ),
			);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'PPOM Texters', 'ppom-texter' ),
	        'public' => true,
	        'menu_position' => 20,
	        'menu_icon' => 'dashicons-welcome-learn-more',
	        'has_archive' => true,
	        'supports' => array('title')
		);

		register_post_type( 'nm_ppom_texter', $args );
	}

    /* 
    **============= Add textbox design metabox ================ 
    */
    function ppom_design_display_metabox(  ){
    
        add_meta_box( 
            'ppom_design_id',
            __( 'PPOM' , 'ppom-texter' ),
            array($this, 'ppom_design_display' ),
            'nm_ppom_texter',
            'normal',
            'default'
        );
    }

    /* 
    **====== Callback function textbox design metabox ========= 
    */
    function ppom_design_display(){
        

    	wp_enqueue_style('ppom-font-awsm', TEXTER_URL."/css/font-awesome/css/font-awesome.css");
    	wp_enqueue_style('ppom-design-style', TEXTER_URL."/css/texter-admin.css");
    	wp_enqueue_script('ppom-design-js', TEXTER_URL."/js/texter-admin.js", array('jquery','jquery-ui-core','jquery-ui-resizable'), TEXTER_VERSION, true);
    	wp_enqueue_style('ppom-jquery-ui-css', TEXTER_URL."/css/jquery-ui.css");

    	// wordpress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    	
        // load shorcode templatse
        $this->load_template ( '/admin/ppom-design.php' );
    }

    /* 
    **====== Add ppom texter setting panel metabox ========= 
    */
    function ppom_texter_panel_setting_metabox(  ){
    
        add_meta_box(
            'ppom_textbox_setting_panel',
            __( 'Textbox Settings', 'ppom-texter' ),
            array($this, 'ppom_texter_panel_setting' ),
            'nm_ppom_texter',
            'side',
            'default'
        );
    }

    /* 
    **====== Setting panel callback function ========= 
    */
    function ppom_texter_panel_setting(){

        // load shorcode templatse
        $this->load_template ( '/admin/textbox-setting.php' );
    }

    /* 
    **====== Create texter oarder meta on metabox in woocommerce order menu ========= 
    */
    function render_files_in_orders() {
    
        add_meta_box( 'texter_order_id', __('Texter Orders', 'nm-cofm'),
        array($this,'display_texter_order_meta'),
        'shop_order', 'normal', 'default');
        
    }

    /* 
    **====== Woocommerce order metabox callback function ========= 
    */
    function display_texter_order_meta($order){

        wp_enqueue_style('texter-order', TEXTER_URL."/css/order.css");

        $ppom_template = '';
        
        $texter_meta = get_post_meta($order->ID, "texter_all", true);
        // ppom_pa($texter_meta);
        
        $template_vars = array();
        $template_vars = array('order_meta' => $texter_meta,
            'order' => $order);

        $ppom_template = '/shortcode/texter-order.php';

        $this->load_template ( $ppom_template, $template_vars );
    }
    
    function is_plugin_validated() {
        $return = false;
        if( NM_AUTH_TEXTER(TEXTER_PLUGIN_PATH, TEXTER_REDIRECT_URL, TEXTER_PLUGIN_ID) -> api_key_found() ) 
            $return = true;
        return $return;
    }
    
    // Admin notices if PPOM is not validated 
    function plugin_notice_not_validated() {
        $page = TEXTER_TEXT_DOMAIN.'_auth';
        $ppom_install_url = admin_url( "admin.php?page={$page}" );
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>'.__( 'PPOM Addon For Texter version is not validated, please provide valid api key to unlock all fields.', 'TEXTER_TEXT_DOMAIN' );
        printf(__('<a class="button" href="%s">%s</a>','ppom-texter'),esc_url($ppom_install_url), 'Add API Key');
        echo '</p>';
        echo '</div>';
    }
        
}

add_action('plugins_loaded', 'Texter');
function Texter() {
    return PPOM_Texter::get_instance();
}
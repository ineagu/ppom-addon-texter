# Auth
N-Media Plugin Authentication Handling Script

* Checkout this repo inside plugin root or copy the Auth directory into plugin
* Remove .git directory, otherwise it will not be added into main plugin
* Paste following script into plugin main file after INCLUDING the files etc


<pre>
// Authencation checking
if( ! class_exists('NM_Auth_CLASSNAME') ) {
	$_auth_class = dirname(__FILE__).'/Auth/auth.php';
	if( file_exists($_auth_class))
		include_once($_auth_class);
	else
		die('Reen, Reen, BUMP! not found '.$_auth_class);
}

/**
 * Plugin API Validation
 * *** DO NOT REMOVE These Lines
 * */
define('PLUGIN_SHORTNAME_PLUGIN_PATH', "plugin-dir-name/plugin-main-file.php");
define('PLUGIN_SHORTNAME_REDIRECT_URL', admin_url( 'admin.php?page=wc-settings&tab=wcgs_settings' ));
define('PLUGIN_SHORTNAME_PLUGIN_ID', 8080);
define('PLUGIN_SHORTNAME_TEXT_DOMAIN', 'wcgs');
NM_AUTH_SHORTNAME(PLUGIN_SHORTNAME_PLUGIN_PATH, PLUGIN_SHORTNAME_REDIRECT_URL, PLUGIN_SHORTNAME_PLUGIN_ID);
</pre>

* Rename the `NM_Auth_CLASSNAME` to plugin CLASSNAME
* Rename the `NM_AUTH_SHORTNAME` function plugin SHORTNAME
* Replace `PLUGIN_SHORTNAME_` with plugin shortname
* PLUGIN_SHORTNAME_PLUGIN_PATH: update this accordingly
* PLUGIN_SHORTNAME_REDIRECT_URL: set redirect_url accordingly, it will be redireted after success
* PLUGIN_SHORTNAME_PLUGIN_ID: set plugin ID
* PLUGIN_SHORTNAME_TEXT_DOMAIN: set plugin text domain name
* 
== Inside plugin main constructor ==
* Add following condition on top.

<pre>
if( ! $this->is_plugin_validated() ) {
    add_action( 'admin_notices', array($this, 'plugin_notice_not_validated') );
    return '';
}
</pre>

== Add following two function in the main plugin class in the end ==
* Make sure you replace the `NM_AUTH_SHORTNAME` and other constants
* Make sure you change the PLUGIN TITLE inside `plugin_notice_not_validated`

<pre>
function is_plugin_validated() {
    $return = false;
    if( NM_AUTH_SHORTNAME(PLUGIN_SHORTNAME_PLUGIN_PATH, PLUGIN_SHORTNAME_REDIRECT_URL, PLUGIN_SHORTNAME_PLUGIN_ID) -> api_key_found() ) 
        $return = true;
    return $return;
}
</pre>


// Admin notices if PPOM is not validated
function plugin_notice_not_validated() {
    
    $page = PLUGIN_SHORTNAME_TEXT_DOMAIN.'_auth';
    $ppom_install_url = admin_url( "admin.php?page={$page}" );
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p>'.__( 'PLUGIN TITLE version is not validated, please provide valid api key to unlock all fields.', 'PLUGIN_SHORTNAME_TEXT_DOMAIN' );
    printf(__('<a class="button" href="%s">%s</a>','PLUGIN_SHORTNAME_TEXT_DOMAIN'),esc_url($ppom_install_url), 'Add API Key');
    echo '</p>';
    echo '</div>';
}


== Now inside Auth/auth.php ==
* Change the plugin CLASSNAME in three areas, carefully
* Change the function name at end of this auth.php file

<pre>
if( ! function_exists('NM_AUTH_SHORTNAME') ) {
	function NM_AUTH_SHORTNAME($path, $redirect_url, $plugin_id) {
		// return new NM_Auth($path, $redirect_url, $plugin_id);
		return NM_Auth_CLASSNAME::get_instance($path, $redirect_url, $plugin_id);
	}
}
</pre>
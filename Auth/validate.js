function validate_api_wooproduct(form){
	
	jQuery(form).find("#nm-sending-api").show();
	
	var data = jQuery(form).serialize();
	data = data + '&action=nm_validate_api';
	
	jQuery.post(ajaxurl, data, function(resp) {

		var body_resp = jQuery.parseJSON(resp.body);
		
		jQuery(form).find("#nm-sending-api").html(body_resp.message);
		
		if( body_resp.status == 'success' ){
			
			// now registering local machine
			register_locat_machine();
		}
	}, 'json');
	
	
	return false;
}


function register_locat_machine(){
	
	var apikey = jQuery("#plugin_api_key").val();
	jQuery("#nm-sending-api").html('Registering machine ...');
	var data = {'apikey': apikey, 'action': jQuery("#nm_action").val()};
	
	jQuery.post(ajaxurl, data, function(resp) {
	
		jQuery("#nm-sending-api").html('All done ...');
		window.location = auth_vars.redirect_url;
	});
}
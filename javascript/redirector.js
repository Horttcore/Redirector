jQuery(function(){
	
	function switchRedirectType(){
		var redirectType = jQuery('input[name=redirect_type]:checked').val();
		switch ( redirectType ) {
			case 'none' :
				jQuery('#redirect_settings_page').slideUp('fast');
				jQuery('#redirect_settings_url').slideUp('fast');
				break;
			case 'redirect_page' :
				jQuery('#redirect_settings_page').slideDown('fast');
				jQuery('#redirect_settings_url').slideUp('fast');
				break;
			case 'redirect_url' :
				jQuery('#redirect_settings_page').slideUp('fast');
				jQuery('#redirect_settings_url').slideDown('fast');
				break;
			case 'redirect_child' :
				jQuery('#redirect_settings_page').slideUp('fast');
				jQuery('#redirect_settings_url').slideUp('fast');
				break;
		}
	}
	
	jQuery('input[name=redirect_type]').change(function(){
		switchRedirectType();
	});
	
	switchRedirectType();
});

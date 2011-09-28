jQuery(function(){
	current_redirection = jQuery('#redirector_type_set').val();
	if (current_redirection == '' || current_redirection == 'redirect_child')
		redirecttoggle('#redirect_settings_child');
	else if (current_redirection == 'redirect_url')
		redirecttoggle('#redirect_settings_url');
	else
		redirecttoggle('#redirect_settings_page');
});
function redirecttoggle(element) {
	jQuery(element).hide();
	if (element == 'none')
	{
		jQuery('#redirect_settings_page').hide('fast');
		jQuery('#redirect_settings_url').hide('fast');
	}
	else if (element == '#redirect_settings_page')
	{
		jQuery('#redirect_settings_page').show('slow');
		jQuery('#redirect_settings_url').hide();	
	}
	else if (element == '#redirect_settings_url')
	{
		jQuery('#redirect_settings_page').hide();
		jQuery('#redirect_settings_url').show('slow');	
	}
	else if (element == '#redirect_settings_child')
	{
		jQuery('#redirect_settings_page').hide('fast');
		jQuery('#redirect_settings_url').hide('fast');		
	}
}
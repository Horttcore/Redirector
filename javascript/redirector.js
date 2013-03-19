jQuery(function(){



	/**
	 *
	 * Cache elements
	 *
	 */
	var settings_page = jQuery('#redirect_settings_page');
	var settings_url = jQuery('#redirect_settings_url');
	var treeSelect = jQuery('#redirector_tree');
	var urlInput = jQuery('#redirector_url');
	var redirectPage = jQuery('#redirect_page');
	var redirectUrl = jQuery('#redirect_url');



	/**
	 *
	 * Update Redirector Metabox
	 *
	 */
	function update_redirector_metabox() {
		settings_url.hide();
		settings_page.hide();

		obj = jQuery('input[name=redirect_type]:checked');

		if ( 'page' == obj.data('type') ) {
			settings_page.show();
		} else if ( 'url' == obj.data('type') ) {
			settings_url.show();
		}
	}



	/**
	 *
	 * Update fields
	 *
	 */
	treeSelect.on('change', function(e){
		obj = jQuery(this);
		redirectPage.val(obj.val());
	});

	urlInput.on('change blur', function(e){
		obj = jQuery(this);
		redirectUrl.val(obj.val());
	});



	/**
	 *
	 * Show Settings
	 *
	 */
	jQuery('input[name=redirect_type]').click(function(e){
		update_redirector_metabox();
	});



	/**
	 *
	 * Run on DOM READY
	 *
	 */
	update_redirector_metabox();
});

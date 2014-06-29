jQuery(document).ready(function(){

	var Plugin = {

		init:function(){

			// Cache
			Plugin.body = jQuery('body');
			Plugin.radioButtons = jQuery('input[name="redirect-type"]');
			Plugin.radioOptions = jQuery('.redirector-redirect-type > div');

			Plugin.radioButtonChecked = false;
			Plugin.radioOptionActive = false;

			Plugin.redirectIDButton = jQuery('#redirector-set-post-id');
			Plugin.redirectID = jQuery('#redirector-post-id');
			Plugin.redirectIDPreview = jQuery('#redirector-post-id-preview');

			Plugin.url = jQuery('input[name="redirector-url"]');
			Plugin.urlPreview = jQuery('#redirector-url-preview');

			Plugin.thickboxWindow = false;
			Plugin.thickboxTitle = false;
			Plugin.thickboxContent = false;

			// Bindings
			Plugin.bindings();
		},

		bindings:function(){

			// Toggle options
			Plugin.toggleOptions();
			Plugin.radioButtons.change(function(){

				Plugin.toggleOptions( jQuery(this ) );

			});

			// Open thickbox
			Plugin.redirectIDButton.click(function(e){
				e.preventDefault();
				Plugin.resetSearch();
				setTimeout( function(){
					Plugin.setThickboxSize();
				}, 500 );
			});

			// Select redirector post
			Plugin.body.on( 'click', '.select-redirector-post-id', function(e){
				e.preventDefault();
				Plugin.redirectID.val( jQuery(this).data('id') );
				Plugin.redirectIDPreview.html( jQuery(this).parents('tr:first').find('.item-title a').clone() )
				jQuery('.tb-close-icon').trigger('click');
				Plugin.resetSearch();
			});

			// URL preview
			Plugin.url.keyup(function(){
				Plugin.urlPreview.text( Plugin.url.val() )
				Plugin.urlPreview.prop( 'href', Plugin.url.val() )
			});

			// Search Post
			Plugin.body.on( 'click', '#redirector-search-post', function(e){
				e.preventDefault();
				Plugin.searchPost();
			});

			Plugin.body.on( 'keyup', '#redirector-search', function(e){

				if ( 13 !== e.which )
					return;

				e.preventDefault();
				Plugin.searchPost();

			}).on( 'change', '#redirector-search', function(e){

				if ( '' == jQuery(this).val() )
					Plugin.resetSearch();

			});

		},

		clearOptions:function( obj ) {

			if ( 'url' != Plugin.radioButtonChecked.val() ) {
				Plugin.url.val('');
				Plugin.urlPreview.prop('href','').text('');
			}

			if ( 'post' != Plugin.radioButtonChecked.val() ) {
				Plugin.redirectID.val('');
				Plugin.redirectIDPreview.html('');
			}

		},

		resetSearch:function(){

			var search = jQuery('#redirector-search'),
				recent = jQuery('#redirector-recent-posts'),
				result = jQuery('#redirector-search-result');

			search.val('');
			result.html('');
			recent.show();

		},

		searchPost:function() {

			var search = jQuery('#redirector-search'),
				button = jQuery('#redirector-search-post'),
				recent = jQuery('#redirector-recent-posts'),
				result = jQuery('#redirector-search-result')
				spinner = false;

			if ( '' != search.val() ) {

				// Add spinner
				button.after('<span class="spinner" id="redirector-spinner"></span>');

				// Hide recent posts
				recent.hide();

				// Search
				jQuery.post(ajaxurl, {action: 'redirector-search-posts', search: search.val(), nonce: redirector.searchNonce }, function(response){

					// Inject search result
					result.html( response.output );

					// Remove spinner
					button.next().remove();

				}, 'json' );

			} else {

				Plugin.resetSearch();

			}

		},

		setThickboxSize:function(){

			Plugin.thickboxWindow = jQuery('#TB_window');
			Plugin.thickboxTitle = jQuery('#TB_title');
			Plugin.thickboxContent = jQuery('#TB_ajaxContent');

			var css = {
				width: parseInt( Plugin.thickboxWindow.width() ) - parseInt( Plugin.thickboxContent.css('paddingLeft') ) - parseInt( Plugin.thickboxContent.css('paddingRight') ),
				height: parseInt( Plugin.thickboxWindow.height() ) - parseInt( Plugin.thickboxTitle.height() ) - parseInt( Plugin.thickboxContent.css('paddingBottom') ) - parseInt( Plugin.thickboxContent.css('paddingBottom') ),
				overflow: 'scroll'
			}

			Plugin.thickboxContent.css( css );

		},

		toggleOptions:function( obj ) {

			Plugin.radioButtonChecked = Plugin.radioButtons.filter(':checked');
			Plugin.radioOptionActive = Plugin.radioButtonChecked.parents('.redirector-redirect-type').find('> div');

			Plugin.clearOptions( obj );
			Plugin.radioOptions.hide();
			Plugin.radioOptionActive.show();

		}


	}

	Plugin.init();

});

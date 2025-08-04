/**
 * This file enables JavaScript related features shared by multiple menus. Specifically:
 *
 * - Sync between input elements of type range and number.
 * - Show and hide the various sections of forms.
 * - Show and hide the sub menu when the user hovers over the "admin-toolbar-menu-item-more" class.
 * - Allow to close the dismissible notice using the close button.
 * - Keep in sync the values of the item selectors with the hidden input field that is used to store the selected
 * items as a comma-separated list.
 *
 * @package ultimate-markdown-pro
 */

jQuery( document ).ready(
	function ($) {

		'use strict';

		/**
		 * Handle the sync between input elements of type range and number.
		 *
		 * Steps:
		 * 1. Iterate over all the input elements of type range using jQuery.each()
		 *
		 * 2. Add a change event listener to each iterated elements used to update the
		 * input element of type number that has the same data-range-sync-id attribute value.
		 *
		 * 3. Get the element of type number that has the same data-range-sync-id attribute value.
		 *
		 * 4. Add a change event listener to the element of type number that updates the
		 * input element of type range that has the same data-range-sync-id attribute value.
		 */
		$( document ).ready(
			function () {
				$( "input[type='range']" ).each(
					function () {
						const range  = $( this );
						const number = $( "input[type='number'][data-range-sync-id='" + range.attr( "data-range-sync-id" ) + "']" );
						range.change(
							function () {
								number.val( range.val() );
							}
						);
						number.change(
							function () {
								range.val( number.val() );
							}
						);
					}
				);
			}
		);

		$( document.body ).on(
			'click',
			'.group-trigger' ,
			function () {

				'use strict';

				// Open and close the various sections of the tables area.
				const target = $( this ).attr( 'data-trigger-target' );
				$( '.daextulmap-main-form__daext-form-section-body[data-section-id="' + target + '"]' ).toggleClass( 'daextulmap-main-form__daext-form-section-body-opened' );

				$( this ).find( '.expand-icon' ).toggleClass( 'arrow-down' );
				$( this ).find( '.expand-icon' ).toggleClass( 'arrow-up' );

			}
		);

		/**
		 * When the "admin-toolbar-menu-item-more" class is hovered, then show the "pop-sub-menu" sub menu.
		 *
		 * When the user does not hover over the "admin-toolbar-menu-item-more" or "pop-sub-menu" class, then hide the
		 * "pop-sub-menu" sub menu.
		 */
		$( document.body ).on(
			'mouseenter',
			'.daextulmap-admin-toolbar__menu-item-more',
			function () {
				$( '.daextulmap-admin-toolbar__pop-sub-menu' ).show();
			}
		);

		$( document.body ).on(
			'mouseleave',
			'.daextulmap-admin-toolbar__menu-item-more, .daextulmap-admin-toolbar__pop-sub-menu',
			function () {
				$( '.daextulmap-admin-toolbar__pop-sub-menu' ).hide();
			}
		);

		$( '.notice-dismiss-button' ).on(
			'click',
			function () {
				$( this ).parent().hide();
			}
		);

		/**
		 * Show the file name when the user selects a file using a custom file upload input.
		 *
		 * Ref:
		 *
		 * - https://stackoverflow.com/questions/572768/styling-an-input-type-file-button
		 * - https://stackoverflow.com/questions/2189615/how-to-get-file-name-when-user-select-a-file-via-input-type-file
		 */
		$( document ).on(
			'change',
			'.custom-file-upload-input',
			function () {

				const numFiles = parseInt($( this )[0].files.length, 10);
				let fileName = '';
				if(numFiles === 0){
					fileName = 'No file chosen';
				}else if(numFiles > 1){
					fileName = numFiles + ' files';

					if(numFiles > parseInt(window.DAEXTULMAP_PARAMETERS.maxFileUploads, 10)){

						// Show an alert message.
						alert('The maximum number of files allowed for upload is ' + window.DAEXTULMAP_PARAMETERS.maxFileUploads + '. If you need to upload more than 20 files, you can increase this limit by modifying the max_file_uploads directive in your php.ini file.');

						// Disable the submit button.
						$('#submit').prop('disabled', true);

						// Reset upload form.
						$('#import-upload-form')[0].reset();
						$('#upload').val('');

						return;

					}

				}else{
					const file     = $( this )[0].files[0];
					fileName = file.name;
				}

				$( this ).prev().text( fileName );

			}
		);

		/**
		 * Keep in sync the values of the item selectors with the hidden input field that is used to store the selected
		 * items as a comma-separated list.
		 */
		$( document ).on(
			'change',
			'.daextulmap-bulk-action-checkbox',
			function () {
				let selectedItems = [];
				$( '.daextulmap-bulk-action-checkbox:checked' ).each(
					function () {
						selectedItems.push( $( this ).val() );
					}
				);
				$( '#bulk-action-selected-items' ).val( selectedItems.join( ',' ) );
			}
		);

		$( document ).on(
			'change',
			'.daextulmap-cb-select-all',
			function () {
				let selectedItems = [];

				// Update all the standard checkboxes according to the value of the changed select all checkbox.
				const cbSelectAllValue = $( this ).prop( 'checked' );

				// Update all the select all checkboxes according to the value of the changed select all checkbox.
				$( '.daextulmap-cb-select-all' ).prop( 'checked', cbSelectAllValue );

				// Update the hidden input field that is used to store the selected items as a comma-separated list.
				$( '.daextulmap-bulk-action-checkbox' ).each(
					function () {
						$( this ).prop( 'checked', cbSelectAllValue );
						if ( cbSelectAllValue ) {
							selectedItems.push( $( this ).val() );
						}
					}
				);
				$( '#bulk-action-selected-items' ).val( selectedItems.join( ',' ) );

			}
		);

	}
);

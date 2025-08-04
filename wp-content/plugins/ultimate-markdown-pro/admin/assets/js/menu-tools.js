/**
 * This file is used to initialize Select2 and handle the selection-dependent fields.
 *
 * @package ultimate-markdown-pro
 */

jQuery( document ).ready(
	function ($) {

		'use strict';

		// Init select2 ---------------------------------------------------------------.
		initSelect2();

		function initSelect2(){

			'use strict';

			$( '#destination' ).select2();
			$( '#source' ).select2();
			$( '#post-types' ).select2();
			$( '#categories' ).select2();
			$( '#tags' ).select2();

		}

		$( '#source' ).on(
			'change',
			function () {

				'use strict';

				const source = parseInt( $( this ).val(), 10 );

				if (source === 0) {

					$( '.daextulmap-main-form__selection-dependent-posts' ).addClass( 'daext-display-none' );

				} else {

					$( '.daextulmap-main-form__selection-dependent-posts' ).removeClass( 'daext-display-none' );

				}

			}
		);

	}
);
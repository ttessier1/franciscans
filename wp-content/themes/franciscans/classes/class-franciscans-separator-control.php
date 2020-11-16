<?php
if ( class_exists( 'WP_Customize_Control' ) ) {

	if ( ! class_exists( 'Franciscans_Separator_Control' ) ) {
		/**
		 * Separator Control.
		 */
		class Franciscans_Separator_Control extends WP_Customize_Control {
			/**
			 * Render the hr.
			 */
			public function render_content() {
				echo '<hr/>';
			}

		}
	}
}

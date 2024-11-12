<?php
/**
 * Plugin Name: Snakey Forms
 * Plugin URI: https://www.linkedin.com/in/dm-ilch-dev/
 * Description: Contact forms with visual customization pushed to extreme.
 * Author: Dmitrii Ilchenko
 * Version: 0.0.1
 * Author URI: https://www.linkedin.com/in/dm-ilch-dev/
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// Abort if attempted to access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize global values.
const SNKFORMS_PREFIX = 'snkfrm';

// Plugin path constants.
const SNKFORMS_PLUGIN_PATH      = __DIR__;
const SNKFORMS_PLUGIN_PHP_PATH  = SNKFORMS_PLUGIN_PATH . '/src/php';
const SNKFORMS_PLUGIN_TEMPLATES = SNKFORMS_PLUGIN_PATH . '/templates/';

// Set up dependencies.
require_once SNKFORMS_PLUGIN_PATH . '/vendor/autoload.php';

// Initialize if there were no collisions found.
if ( ! class_exists( 'SnakeyForms' ) ) {
	/**
	 * Plugin main controller class.
	 */
	final class SnakeyFormsPlugin {

		// Initialization Methods.

		/**
		 * Construction method for the class.
		 */
		public function __construct() {
			// Initialize the post types.
			$success = $this->init_post_types();
			if ( is_string( $success ) ) {
				$this->throw_admin_message( $success );
				// Abort future code execution.
				return;
			}
		}


		// Private Methods.

		/**
		 * Initializes post types added with the plugin.
		 *
		 * @return bool|string true if initialization is successful, error if an error has occurred.
		 */
		private function init_post_types() {
			// Initialize form post type.
			$form_obj = new \SnakeyForms\PostTypes\SnakeyForm();
			if ( ! $form_obj->init() ) {
				return __( 'Plugin Error: Could not initialize required post types (already exist).', 'snakebytes' );
			}

			// Return true if the initialization went well.
			return true;
		}

		/**
		 * Displays admin message by attaching a hook.
		 *
		 * @param string $message  Notice message text description.
		 * @param string $severity Notice message severity level.
		 */
		private function throw_admin_message( string $message, string $severity = 'notice-error' ): void {
			add_action(
				'admin_notices',
				static function () use ( $message, $severity ): void {
					?>
					<div class="notice <?php echo esc_attr( $severity ); ?>">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				}
			);
		}
	}

	// Initialize plugin controller.
	new SnakeyFormsPlugin();
}
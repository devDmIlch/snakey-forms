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
const SNKFORMS_PREFIX     = 'snkfrm';
const SNKFORMS_PLUGIN_VER = '0.0.1';

// Plugin path constants.
const SNKFORMS_PLUGIN_FILE      = __FILE__;
const SNKFORMS_PLUGIN_PATH      = __DIR__;
const SNKFORMS_PLUGIN_PHP_PATH  = SNKFORMS_PLUGIN_PATH . '/src/php';
const SNKFORMS_PLUGIN_TEMPLATES = SNKFORMS_PLUGIN_PATH . '/templates/';

// Relative paths.
const SNKFORMS_SVG_PATH = WP_PLUGIN_URL . '/snakey-forms/assets/images/svg/';

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

			// Initialize DB table controller.
			$this->init_table_controller();

			// Register scripts and styles.
			add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts' ] );
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
		 * Initializes DB table controller class.
		 */
		private function init_table_controller() {
			$table_controller = new \SnakeyForms\DB\Table_Controller();
			$table_controller->init();
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

		/**
		 * Registers plugins scripts and styles.
		 */
		public function register_admin_scripts(): void {
			// TODO: personalize file delivery for different screens with get_current_screen().

			// Register scripts and styles.
			wp_enqueue_style(
				'snakey-forms-styles-generic',
				plugins_url( '/assets/build/custom.css', SNKFORMS_PLUGIN_FILE ),
				[],
				SNKFORMS_PLUGIN_VER
			);
			wp_enqueue_script(
				'snakey-forms-script-generic',
				plugins_url( '/assets/build/custom.js', SNKFORMS_PLUGIN_FILE ),
				[ 'wp-i18n' ],
				SNKFORMS_PLUGIN_VER,
				false
			);
		}
	}

	// Initialize plugin controller.
	new SnakeyFormsPlugin();
}

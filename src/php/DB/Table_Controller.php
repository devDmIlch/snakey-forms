<?php
/**
 * Database plugin table controller class file.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\DB;

/**
 * Database plugin table controller class.
 */
class Table_Controller {

	// Private Fields.

	/**
	 * DB table name.
	 *
	 * @var string $form_data_table
	 */
	private static $form_data_table = 'snakey_forms_posts_data';


	// Public Properties.

	/**
	 * Returns DB table name for 'form' posts extended data table.
	 *
	 * @return string DB name without prefix.
	 */
	public static function get_form_data_table(): string {
		return self::$form_data_table;
	}


	// Initialization Methods.

	/**
	 * Initialization method of the class.
	 */
	public function init(): void {
		// Initialize class hooks.
		$this->hooks();
	}

	/**
	 * Hook initialization methods of the class.
	 */
	protected function hooks(): void {
		// Initialize table (if it doesn't exist yet) after plugin activation.
		register_activation_hook( SNKFORMS_PLUGIN_FILE, [ $this, 'initialize_table' ] );
	}


	// Public Methods.

	/**
	 * Initializes database table required for the plugin work.
	 */
	public function initialize_table(): void {
		global $wpdb;

		// Check whether DB table exists.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . self::$form_data_table ) ) === $wpdb->prefix . self::$form_data_table ) { // phpcs:ignore
			return;
		}

		// Create DB table with additional data for 'form' posts.
		$wpdb->query( // phpcs:ignore
			$wpdb->prepare(
				// phpcs:disable
			'CREATE TABLE %i ( 
    				id bigint(20) unsigned AUTO_INCREMENT PRIMARY KEY,
                	post_id bigint(20) unsigned,
                	post_json longtext,
                	parent_style bigint(20) unsigned
				)',
				// phpcs:enable
				$wpdb->prefix . self::$form_data_table
			)
		);
	}
}

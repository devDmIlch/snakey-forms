<?php
/**
 * Basic Form post type
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\PostTypes;

use SnakeyForms\DB\Table_Controller;
use SnakeyForms\FormEditor\FormEditor;

/**
 * Form custom post type class.
 */
class SnakeyForm {

	// Private Fields.

	/**
	 * Post type slug name.
	 *
	 * @var string $slug.
	 */
	private $slug;

	/**
	 * Form Editor instance object.
	 *
	 * @var FormEditor $form_editor.
	 */
	private $form_editor;


	// Protected Properties.

	/**
	 * Initializes (if necessary) and returns form editor class object.
	 *
	 * @return FormEditor initialized form editor class object.
	 */
	protected function get_form_editor_obj(): FormEditor {
		// Initialize the form editor.
		if ( ! isset( $this->form_editor ) ) {
			$this->form_editor = new FormEditor();
			$this->form_editor->init();
		}

		return $this->form_editor;
	}


	// Initialization Methods.

	/**
	 * Initializes class instance.
	 *
	 * @return bool Whether the post type has been initialized successfully.
	 */
	public function init(): bool {
		$this->slug = SNKFORMS_PREFIX . '-form';
		// Check whether the post type exists and bail if it does.
		if ( post_type_exists( $this->slug ) ) {
			return false;
		}

		// Initialize field editor.
		$this->get_form_editor_obj();

		// Initialize hooks.
		$this->hooks();

		// Return true if no errors were encountered.
		return true;
	}

	/**
	 * Initializes class hooks.
	 */
	protected function hooks(): void {
		// Register post type.
		add_action( 'init', [ $this, 'register_post_type' ] );
		// Add form editor metabox.
		add_action( 'add_meta_boxes_' . $this->slug, [ $this, 'add_form_editor_metabox' ] );

		// Update post with data from editor metabox.
		add_filter( 'wp_insert_post_data', [ $this, 'on_post_submission' ], 10, 2 );
		// Remove saved extended data on post deletion.
		add_action( 'delete_post', [ $this, 'on_post_deletion' ], 10, 1 );
	}


	// Private Methods.

	/**
	 * Retrieves extended data from the extended posts data table.
	 *
	 * @param int $post_id ID of the post.
	 *
	 * @return array Array of additional data.
	 */
	private function get_extended_data( int $post_id ): array {
		$cache_name = 'post_' . $this->slug . '_extended_' . $post_id;
		// Check if cache exists.
		$post_data = wp_cache_get( $cache_name, SNKFORMS_PREFIX );
		if ( $post_data ) {
			return $post_data;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . Table_Controller::get_form_data_table();

		// Get post data from DB if it's not in the cache.
		$post_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * from %i WHERE post_id=%d', $table_name, $post_id ), ARRAY_A );
		// Save data in the cache.
		wp_cache_set( $cache_name, $post_data, SNKFORMS_PREFIX );

		if ( empty( $post_data ) ) {
			return [];
		}

		return $post_data[0];
	}

	/**
	 * Updates (or creates) DB record with extended post data.
	 *
	 * @param int   $post_id   ID of the post.
	 * @param array $post_data Extended post data values.
	 *
	 * @return bool true on success, false on failure.
	 */
	private function set_extended_data( int $post_id, array $post_data ): bool {
		$cache_name = 'post_' . $this->slug . '_extended_' . $post_id;
		// Delete old cache with extended data.
		wp_cache_delete( $cache_name, SNKFORMS_PREFIX );

		// Array of allowed keys for the table.
		$allowed_format = [
			'post_json'    => '%s',
			'parent_style' => '%d',
		];

		// Filter both $post_data supplied as an argument and $allowed_format array to match their keys.
		$allowed_format = array_intersect_key( $allowed_format, $post_data );
		$post_data      = array_intersect_key( $post_data, $allowed_format );

		global $wpdb;
		$table_name = $wpdb->prefix . Table_Controller::get_form_data_table();

		// Check whether the record for this post exists.
		$existing_data = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE post_id=%d', $table_name, $post_id ), ARRAY_A );

		// Either update existing or insert new record.
		if ( empty( $existing_data ) ) {
			$result = $wpdb->insert( $table_name, array_merge( [ 'post_id' => $post_id ], $post_data ), array_merge( [ 'post_id' => '%d' ], $allowed_format ) );
		} else {
			$result = $wpdb->update( $table_name, $post_data, [ 'post_id' => $post_id ], $allowed_format, [ 'post_id' => '%d' ] );
		}

		return ! ( false === $result );
	}

	/**
	 * Removed DB record with extended post data based on supplied post ID.
	 *
	 * @param int $post_id ID of the post.
	 *
	 * @return bool true on success, false on failure.
	 */
	public function delete_extended_data( int $post_id ): bool {
		$cache_name = 'post_' . $this->slug . '_extended_' . $post_id;
		// Delete old cache with extended data.
		wp_cache_delete( $cache_name, SNKFORMS_PREFIX );

		global $wpdb;
		$table_name = $wpdb->prefix . Table_Controller::get_form_data_table();

		// Check whether the record for this post exists.
		$result = $wpdb->delete( $table_name, [ 'post_id' => $post_id ], [ 'post_id' => '%d' ] );

		return ! ( false === $result );
	}


	// Public Methods.

	/**
	 * Registers form post type.
	 */
	public function register_post_type(): void {
		// Register post type.
		register_post_type(
			$this->slug,
			[
				'label'        => __( 'Forms', 'snakebytes' ),
				'description'  => __( 'Contact forms with customization', 'snakebytes' ),
				'public'       => true,
				'hierarchical' => false,
				'supports'     => [ 'title', 'author', 'revisions' ],
				'labels'       => [
					// TODO: Add labels.
				],
			]
		);
	}

	/**
	 * Adds metabox with custom form editor.
	 *
	 * @param \WP_Post $post_obj Edited post object.
	 */
	public function add_form_editor_metabox( \WP_Post $post_obj ): void {
		add_meta_box( 'snk-form-editor', __( 'Form Editor', 'snakebytes' ), [ $this, 'render_form_editor_content' ], $this->slug );
	}

	/**
	 * Processes data from 'form editor' metabox to save it as post content.
	 *
	 * @param array $data    An array of slashed, sanitized, and processed post data.
	 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
	 *
	 * @return array Array of data with modified content, if the nonce and post_type are valid.
	 */
	public function on_post_submission( array $data, array $postarr ) : array {
		// Check the post type.
		if ( $postarr['post_type'] !== $this->slug ) {
			return $data;
		}

		// Check nonce value.
		if ( ! wp_verify_nonce( $postarr['_wpnonce'], 'update-post_' . $postarr['post_ID'] ) ) {
			return $data;
		}

		// Sanitize json value of the post content.
		if ( ! empty( $_POST['form-input'] ) ) {
			$post_json = sanitize_text_field( wp_unslash( $_POST['form-input'] ) );
		}

		// Update post extended data.
		if ( ! empty( $post_json ) ) {
			$this->set_extended_data( $postarr['post_ID'], [ 'post_json' => $post_json ] );
		}

		// Overwrite the post content with data from post editor.
		if ( ! empty( $_POST['form-input'] ) ) {
			$data['post_content'] = $this->get_form_editor_obj()->get_form_content_from_json( $postarr['post_ID'], $post_json );
		}

		return $data;
	}

	/**
	 * Removes record from extended data table during post deletion.
	 *
	 * @param int $post_id ID of the deleted post.
	 */
	public function on_post_deletion( int $post_id ): void {
		// Remove extended data related to the post.
		$this->delete_extended_data( $post_id );
	}

	/**
	 * Renders content of the form editor.
	 *
	 * @param \WP_Post $post_obj Edited post object.
	 */
	public function render_form_editor_content( \WP_Post $post_obj ): void {
		// Get post data details.
		$post_data = $this->get_extended_data( $post_obj->ID );

		// Renders field shop.
		$this->get_form_editor_obj()->display_field_shop();
		// Renders content of the form.
		$this->get_form_editor_obj()->display_form_editor_content( $post_data['post_json'] );
	}
}

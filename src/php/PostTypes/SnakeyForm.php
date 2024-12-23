<?php
/**
 * Basic Form post type
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\PostTypes;

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
		add_filter( 'wp_insert_post_data', [ $this, 'filter_post_content' ], 10, 2 );
	}


	// Private Methods.

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
	public function filter_post_content( array $data, array $postarr ) : array {
		// Check the post type.
		if ( $postarr['post_type'] !== $this->slug ) {
			return $data;
		}

		// Check nonce value.
		if ( ! wp_verify_nonce( $postarr['_wpnonce'], 'update-post_' . $postarr['post_ID'] ) ) {
			return $data;
		}

		// Overwrite the post content with data from post editor.
		if ( ! empty( $_POST['form-input'] ) ) {
			$data['post_content'] = sanitize_text_field( wp_unslash( $_POST['form-input'] ) );
		}

		return $data;
	}

	/**
	 * Renders content of the form editor.
	 *
	 * @param \WP_Post $post_obj Edited post object.
	 */
	public function render_form_editor_content( \WP_Post $post_obj ): void {
		// Add input field.
		$this->get_form_editor_obj()->render_input();
		// Renders field shop.
		$this->get_form_editor_obj()->display_field_shop();
		// Renders content of the form.
		$this->get_form_editor_obj()->display_form_editor_content( $post_obj->post_content );
	}
}

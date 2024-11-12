<?php
/**
 * Basic Form post type
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\PostTypes;

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
		add_meta_box( 'form-editor', __( 'Form Editor', 'snakebytes' ), [ $this, 'render_form_editor_content' ], $this->slug );
	}

	/**
	 * Renders content of the form editor.
	 *
	 * @param \WP_Post $post_obj Edited post object.
	 */
	public function render_form_editor_content( \WP_Post $post_obj ): void {
		$form_editor_obj = new \SnakeyForms\FormEditor\FormEditor();
		$form_editor_obj->init();
		$form_editor_obj->display_form_editor_content();
	}
}


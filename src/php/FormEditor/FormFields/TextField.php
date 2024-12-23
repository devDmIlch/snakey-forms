<?php
/**
 * Text field for the form editor.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\FormEditor\FormFields;

use SnakeyForms\FormEditor\FormFields\Abstracts\A_GenericField;

/**
 * Text field for the form editor.
 */
class TextField extends A_GenericField {

	// Initialization Methods.

	/**
	 * Class initialization method.
	 */
	public function init(): void {
		// Initialize basic field data.
		$this->field_slug = 'text';
		$this->field_name = __( 'Text Field', 'snakebytes' );

		parent::init();
	}


	// Public Methods.

	/**
	 * Displays field preview for the field selector.
	 *
	 * @return string Template name for preview.
	 */
	public function get_field_preview_template(): string {
		return apply_filters( SNKFORMS_PREFIX . '_field_preview_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'previews/text-field.php' );
	}

	/**
	 * Displays field content based on its parameters.
	 *
	 * @return string Template name for field content.
	 */
	public function get_field_content_template(): string {
		return apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'fields/text-field.php' );
	}

	/**
	 * Displays field content based on its parameters.
	 *
	 * @return string Template name for field content.
	 */
	public function get_field_proto_template(): string {
		return apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'proto/text-field.php' );
	}

	/**
	 * Display field editor content.
	 *
	 * @param array $state Parameters of the field.
	 */
	public function render_field_editor( array $state ): void {
		echo 'text field editor';
		// TODO: Implement display_field_editor() method.
	}
}

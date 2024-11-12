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
	 */
	public function display_field_preview(): void {
		esc_html_e( 'Select: Text Field', 'snakebytes' );

		// TODO: Improve preview for this field.
	}

	/**
	 * Displays field content based on its parameters.
	 *
	 * @param array $state Parameters of the field.
	 */
	public function render_field_content( array $state ): void {
		$template_path = apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'fields/text-field.php' );
		// Load field template if it exists.
		load_template( $template_path, false, $state );
	}

	/**
	 * Display field editor content.
	 *
	 * @param array $state Parameters of the field.
	 */
	public function display_field_editor( array $state ): void {
		echo 'text field editor';
		// TODO: Implement display_field_editor() method.
	}
}

<?php
/**
 * Generic Field abstract class for implementation by other fields.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 **/

namespace SnakeyForms\FormEditor\FormFields\Abstracts;

use SnakeyForms\FormEditor\FormFields\Interfaces\I_FieldCustomizable;
use SnakeyForms\FormEditor\FormFields\Interfaces\I_FormField;

/**
 * Generic Fields abstract class. Extend this class to add custom field types for the form editor.
 */
abstract class A_GenericField implements I_FormField, I_FieldCustomizable {

	// Protected Fields.

	/**
	 * Field slug name (is unique).
	 *
	 * @var string $field_slug
	 */
	protected $field_slug;

	/**
	 * Field display name.
	 *
	 * @var string $field_name
	 */
	protected $field_name;


	// Public Properties.

	/**
	 * Returns field slug name.
	 *
	 * @return string field slug name.
	 */
	public function get_slug(): string {
		return $this->field_slug;
	}

	/**
	 * Returns field display name.
	 *
	 * @return string field display name.
	 */
	public function get_name(): string {
		return $this->field_name;
	}


	// Initialization Methods.

	/**
	 * Initializes class instance.
	 */
	public function init(): void {
		// Initialize class hooks.
		$this->hooks();
	}

	/**
	 * Initializes class hooks.
	 */
	protected function hooks(): void {

	}


	// Public Methods.

	/**
	 * Displays field prototype content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_proto( array $state ): void {
		// Get template location for the field.
		$template = apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'admin/proto/text-field.php' );
		// Render template content.
		load_template( $template, false, $state );
	}

	/**
	 * Displays field editor content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_editor( array $state ): void {
		// Get template location for the field.
		$template = apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'admin/editors/text-field.php' );
		// Render template content.
		load_template( $template, false, $state );
	}

	/**
	 * Displays field front-end content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_content( array $state ): void {
		// Get template location for the field.
		$template = apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'fields/text-field.php' );
		// Render template content.
		load_template( $template, false, [ 'state' => $state ] );
	}

	/**
	 * Displays field preview.
	 */
	public function get_field_preview(): void {
		// Get template location for the field.
		$template = apply_filters( SNKFORMS_PREFIX . '_field_template_' . $this->field_slug, SNKFORMS_PLUGIN_TEMPLATES . 'admin/previews/text-field.php' );
		// Render template content.
		load_template( $template, false );
	}
}

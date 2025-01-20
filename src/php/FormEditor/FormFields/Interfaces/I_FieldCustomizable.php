<?php
/**
 * Form Customization interface for fields in form editor.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\FormEditor\FormFields\Interfaces;

/**
 * Field customization interface for admin form editor.
 */
interface I_FieldCustomizable {

	/**
	 * Displays field editor content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_editor( array $state ): void;
}

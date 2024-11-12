<?php
/**
 * Form Field interface to be implemented.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\FormEditor\FormFields\Interfaces;

/**
 * Form Field interface.
 */
interface I_FormField {

	// Properties.

	/**
	 * Returns unique field slug name.
	 *
	 * @return string Field slug name.
	 */
	public function get_slug(): string;

	/**
	 * Returns display name of the field.
	 *
	 * @return string Field display name.
	 */
	public function get_name(): string;


	// Methods.

	/**
	 * Displays field preview view in field selector.
	 */
	public function display_field_preview(): void;

	/**
	 * Displays field content based on its parameters.
	 *
	 * @param array $state Parameters of the field.
	 */
	public function render_field_content( array $state ): void;

	/**
	 * Display field editor content.
	 *
	 * @param array $state Parameters of the field.
	 */
	public function display_field_editor( array $state ): void;
}

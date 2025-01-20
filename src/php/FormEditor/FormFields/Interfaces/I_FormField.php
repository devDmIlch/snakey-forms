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
	 * Displays field prototype content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_proto( array $state ): void;

	/**
	 * Displays field front-end content.
	 *
	 * @param array $state Field settings.
	 */
	public function get_field_content( array $state ): void;

	/**
	 * Displays field preview html.
	 */
	public function get_field_preview(): void;
}

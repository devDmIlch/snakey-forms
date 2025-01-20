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
	 *
	 * @return string Template name for preview.
	 */
	public function get_field_preview_template(): string;

	/**
	 * Displays field content based on its parameters.
	 *
	 * @return string Template name for field content.
	 */
	public function get_field_content_template(): string;

	/**
	 * Displays field prototype content.
	 *
	 * @return string Template name for field prototype.
	 */
	public function get_field_proto_template(): string;

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

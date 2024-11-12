<?php
/**
 * Generic Field abstract class for implementation by other fields.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 **/

namespace SnakeyForms\FormEditor\FormFields\Abstracts;

use SnakeyForms\FormEditor\FormFields\Interfaces\I_FormField;

/**
 * Generic Fields abstract class. Extend this class to add custom field types for the form editor.
 */
abstract class A_GenericField implements I_FormField {

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
}

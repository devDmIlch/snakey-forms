<?php
/**
 * Custom Form Editor class template file.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\FormEditor;

use SnakeyForms\FormEditor\FormFields\Interfaces\I_FormField;
use SnakeyForms\FormEditor\FormFields\TextField;

/**
 * Custom form editor class primarily for SneakyForm cpt.
 */
class FormEditor {

	// Private Fields.

	/**
	 * Array of available fields.
	 *
	 * @var I_FormField[] $r_fields
	 */
	private $r_fields;

	/**
	 * Array of existing form fields.
	 *
	 * @var array $form_fields
	 */
	private $form_fields;


	// Initialization Methods.

	/**
	 * Initializes instance of the class.
	 */
	public function init(): void {
		// Get the list of registered fields.
		$this->r_fields = apply_filters( SNKFORMS_PREFIX . '_registered_fields', $this->register_default_fields() );

		// TODO: Test data change with the real values later.
		$test_data = [
			[
				'type'  => 'text',
				'ref'   => null,
				'state' => [
					'name' => 'field_text',
					// Populate state with settings.
				],
			],
		];
		// Prototype data of the field.
		$this->form_fields = $this->parse_selected_field_states( $test_data, $this->r_fields );

	}


	// Private Methods.

	/**
	 * Registers default form fields provided by the plugin.
	 *
	 * @return I_FormField[] array of the form fields.
	 */
	private function register_default_fields(): array {
		// An array of registered fields.
		$r_fields = [];
		// Create an array of class references for the available fields.
		$text_field = new TextField();
		$text_field->init();
		$r_fields[] = $text_field;

		// Return the list of registered fields.
		return $r_fields;
	}

	/**
	 * Parses data about selected form fields.
	 *
	 * @param array $form_data Saved form data.
	 * @param array $r_fields  Registered form fields.
	 *
	 * @return array Parsed array data with references to their field type objects.
	 */
	private function parse_selected_field_states( array $form_data, array $r_fields ): array {
		return array_map(
			function ( array $s_field ) use ( $r_fields ) {
				// Go through each of the registered fields to determine the related one.
				foreach ( $r_fields as $field_obj ) {
					if ( $s_field['type'] === $field_obj->get_slug() ) {
						// Set reference for this field to a field object.
						$s_field['ref'] =& $field_obj;
						// Break the loop.
						break;
					}
				}

				return $s_field;
			},
			$form_data
		);
	}


	// Public Methods.

	/**
	 * Displays form content for post editor.
	 */
	public function display_form_editor_content(): void {
		foreach ( $this->form_fields as $field_data ) {
			$field_data['ref']->render_field_content( $field_data['state'] );
		}
	}

	/**
	 * Displays selector area with possible fields.
	 */
	public function display_field_shop(): void {

	}
}

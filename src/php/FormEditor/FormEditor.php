<?php
/**
 * Custom Form Editor class template file.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

namespace SnakeyForms\FormEditor;

use SnakeyForms\FormEditor\FormFields\Interfaces\I_FieldCustomizable;
use SnakeyForms\FormEditor\FormFields\Interfaces\I_FormField;
use SnakeyForms\FormEditor\FormFields\TextField;

/**
 * Custom form editor class primarily for SneakyForm cpt.
 */
class FormEditor {

	// Private Fields.

	/**
	 * Array of registered fields.
	 *
	 * @var I_FormField[] $r_fields
	 */
	private $r_fields;


	// Protected Properties.

	/**
	 * Returns (and initializes) an array of registered fields.
	 *
	 * @return I_FormField[] Array of registered fields.
	 */
	protected function get_r_fields(): array {
		// Get the list of registered fields.
		if ( ! isset( $this->r_fields ) ) {
			$this->r_fields = apply_filters( SNKFORMS_PREFIX . '_registered_fields', $this->register_default_fields() );
		}

		return $this->r_fields;
	}


	// Initialization Methods.

	/**
	 * Initializes instance of the class.
	 */
	public function init(): void {
		$this->get_r_fields();

		// Register hooks.
		$this->hooks();
	}


	/**
	 * Initializes class hooks.
	 */
	protected function hooks(): void {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
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
	 * Transforms form content in JSON format to regular HTML.
	 *
	 * @param int    $post_id   ID of the post.
	 * @param string $form_json Form content in JSON format.
	 *
	 * @return string Content in HTML.
	 */
	public function get_form_content_from_json( int $post_id, string $form_json ): string {
		// Parse selected fields data.
		$content = empty( $form_json ) ? [] : json_decode( $form_json, true );

		ob_start();
		foreach ( $this->parse_selected_field_states( $content, $this->r_fields ) as $field ) {
			$field['ref']->get_field_content( $field['state'] );
		}
		$content = ob_get_clean();

		// Prepare arguments for template to work with.
		$args = [
			'form_id' => $post_id,
			'content' => $content,
		];

		ob_start();
		load_template( SNKFORMS_PLUGIN_TEMPLATES . 'sections/form-container.php', false, $args );
		return ob_get_clean();
	}

	/**
	 * Displays form content for post editor.
	 *
	 * @param string|null $content Existing content of the form.
	 */
	public function display_form_editor_content( ?string $content ): void {
		// Load template for the editor content.
		load_template( SNKFORMS_PLUGIN_TEMPLATES . 'admin/sections/form-content.php', false );
		// Load template with input field for saving data.
		load_template( SNKFORMS_PLUGIN_TEMPLATES . 'admin/sections/form-input.php', false, [ 'value' => $content ?? '' ] );
	}

	/**
	 * Displays selector area with possible fields.
	 */
	public function display_field_shop(): void {
		$template_args = array_map(
			function ( $field ) {
				return [
					'name'     => $field->get_slug(),
					'callback' => [ $field, 'get_field_preview' ],
				];
			},
			$this->get_r_fields()
		);

		load_template( SNKFORMS_PLUGIN_TEMPLATES . 'admin/sections/field-shop.php', false, [ 'fields' => $template_args ] );
	}

	/**
	 * Registers REST API endpoints for editor.
	 */
	public function register_rest_routes(): void {
		// Register endpoint to retrieve fields.
		foreach ( $this->get_r_fields() as $field ) {
			// Default request Routes.
			$routes = [
				'get-field' => 'get_field_content',
				'get-proto' => 'get_field_proto',
			];

			// Check whether the field is customizable.
			if ( $field instanceof I_FieldCustomizable ) {
				$routes['get-editor'] = 'get_field_editor';
			}

			// Register routes.
			foreach ( $routes as $name => $callback ) {
				register_rest_route(
					SNKFORMS_PREFIX . '/v1',
					'/' . $name . '/' . $field->get_slug(),
					[
						[
							'methods'             => [ 'POST' ],
							'callback'            => function ( \WP_REST_Request $request ) use ( $field, $callback ) {
								// Get request parameters.
								$state = rest_sanitize_object( $request->get_param( 'state' ) );

								ob_start();
								[ $field, $callback ]( $state );
								$html = ob_get_clean();

								return [
									'status' => 200,
									'html'   => $html,
								];
							},
							// TODO: Add proper verification to this function.
							'permission_callback' => function ( \WP_REST_Request $request ) {
								return true;
							},
							'args'                => [
								[
									'state' => [
										'type'     => 'object',
										'required' => true,
									],
								],
							],
						],
					],
					false
				);
			}
		}
	}
}

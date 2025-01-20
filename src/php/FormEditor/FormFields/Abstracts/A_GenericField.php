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

	}

	/**
	 * Renders field content based on supplied REST $request parameters.
	 *
	 * @param \WP_REST_Request $request REST request parameters.
	 *
	 * @return array Request response.
	 */
	public function render_field_content( \WP_REST_Request $request ): array {
		// Get request parameters.
		$state = rest_sanitize_object( $request->get_param( 'state' ) );

		ob_start();
		load_template( $this->get_field_content_template(), false, $state );
		$html = ob_get_clean();

		return [
			'status' => 200,
			'html'   => $html,
		];
	}

	/**
	 * Renders field content based on supplied REST $request parameters.
	 *
	 * @param \WP_REST_Request $request REST request parameters.
	 *
	 * @return array Request response.
	 */
	public function render_field_proto( \WP_REST_Request $request ): array {
		// Get request parameters.
		$state = rest_sanitize_object( $request->get_param( 'state' ) );

		ob_start();
		$this->get_field_proto( $state );
		$html = ob_get_clean();

		return [
			'status' => 200,
			'html'   => $html,
		];
	}

	/**
	 * Renders field customization area content.
	 *
	 * @param \WP_REST_Request $request REST request parameters.
	 *
	 * @return array Request response.
	 */
	public function render_field_editor( \WP_REST_Request $request ): array {
		// Get request parameters.
		$state = rest_sanitize_object( $request->get_param( 'state' ) );

		ob_start();
		load_template( $this->get_field_editor_template(), false, $state );
		$html = ob_get_clean();

		return [
			'status' => 200,
			'html'   => $html,
		];
	}

	/**
	 * Registers endpoint routes for the plugin
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			SNKFORMS_PREFIX . '/v1',
			'/get-field/' . $this->get_slug(),
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'render_field_content' ],
					// Allow this endpoint to be called only by contributor+ level of users.
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

		register_rest_route(
			SNKFORMS_PREFIX . '/v1',
			'/admin/get-proto/' . $this->get_slug(),
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'render_field_proto' ],
					// Allow this endpoint to be called only by contributor+ level of users.
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

		register_rest_route(
			SNKFORMS_PREFIX . '/v1',
			'/admin/get-editor/' . $this->get_slug(),
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'render_field_editor' ],
					// Allow this endpoint to be called only by contributor+ level of users.
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

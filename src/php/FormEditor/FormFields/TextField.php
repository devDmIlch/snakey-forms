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
}

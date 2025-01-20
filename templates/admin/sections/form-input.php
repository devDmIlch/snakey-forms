<?php
/**
 * Form editor input field for saving.
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires current value of the input passed through the arguments.
if ( empty( $args ) ) {
	return;
}

?>
<input type="hidden" name="form-input" id="form-input" class="form-input" value="<?php echo esc_attr( $args['value'] ); ?>" tabindex="-1">

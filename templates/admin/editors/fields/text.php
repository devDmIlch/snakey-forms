<?php
/**
 * Text field for the field customization area.
 *
 * @package snakey-forms/plugin
 * @version 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="text-field customization-field">
	<label for="<?php echo esc_attr( $args['name'] ); ?>">
		<?php echo esc_html( $args['label'] ); ?>
	</label>
	<input
		type="<?php echo esc_attr( $args['type'] ?? 'text' ); ?>"
		name="<?php echo esc_attr( $args['name'] ); ?>"
		id="<?php echo esc_attr( $args['name'] ); ?>"
		value="<?php echo esc_attr( $args['value'] ?? '' ); ?>">
</div>

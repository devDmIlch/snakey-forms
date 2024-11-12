<?php
/**
 * Text Field template
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */


// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="snkfrm-field">
	<label for="<?php echo esc_attr( $args['name'] ); ?>" style="<?php echo esc_attr( implode( ';', $args['l-style'] ) ); ?>">
		<?php echo esc_html( $args['label'] ?? $args['name'] ); ?>
	</label>
	<input	name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>"
			type="text" class="<?php echo esc_attr( implode( ' ', $args['class'] ?? [] ) ); ?>"
			style="<?php echo esc_attr( implode( ';', $args['style'] ?? [] ) ); ?>"
			placeholder="<?php echo esc_attr( $args['placeholder'] ?? '' ); ?>"
			value="<?php echo esc_attr( $args['value'] ?? '' ); ?>"
	>
</div>

<?php
/**
 * Text Field prototype content template
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="snkfrm-field snkfrm-proto is-customizable" draggable="true" name="<?php echo esc_attr( $args['state']['name'] ); ?>" props="<?php echo esc_attr( $args['props'] ?? '' ); ?>">
	<div class="proto-controls">
		<div class="action-button move-up">
			<
		</div>
		<div class="action-button move-down">
			>
		</div>
		<div class="action-button remove">
			x
		</div>
	</div>
	<label for="<?php echo esc_attr( $args['state']['name'] ); ?>" style="<?php echo esc_attr( implode( ';', $args['state']['l-style'] ?? [] ) ); ?>">
		<?php echo esc_html( $args['state']['label'] ?? $args['state']['name'] ); ?>
	</label>
	<div	name="<?php echo esc_attr( $args['state']['name'] ); ?>" id="<?php echo esc_attr( $args['state']['name'] ); ?>"
			type="text" class="<?php echo esc_attr( implode( ' ', $args['state']['class'] ?? [] ) . ' snk-field snk-field-proto' ); ?>"
			style="<?php echo esc_attr( implode( ';', $args['state']['style'] ?? [] ) ); ?>"
			placeholder="<?php echo esc_attr( $args['state']['placeholder'] ?? '' ); ?>"
			value="<?php echo esc_attr( $args['state']['value'] ?? '' ); ?>"
	></div>
</div>

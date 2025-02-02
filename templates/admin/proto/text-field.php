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

$inner_styles = array_filter(
	$args,
	function ( $value, $key ): bool {
		if ( '' === $value ) {
			return false;
		}

		if ( str_contains( $key, 'padding' ) ) {
			return true;
		}

		if ( str_contains( $key, 'width' ) ) {
			return true;
		}

		if ( str_contains( $key, 'height' ) ) {
			return true;
		}

		if ( str_contains( $key, 'border' ) ) {
			return true;
		}

		return false;
	},
	ARRAY_FILTER_USE_BOTH
);

$inner_styles = implode(
	'; ',
	array_map(
		function ( $value, $key ) : string {
			return $key . ': ' . ( is_numeric( $value ) ? $value . 'px' : $value );
		},
		$inner_styles,
		array_keys( $inner_styles )
	)
);

?>
<div class="snkfrm-field snkfrm-proto is-customizable" draggable="true" name="<?php echo esc_attr( $args['name'] ); ?>">
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
	<label for="<?php echo esc_attr( $args['name'] ); ?>" style="<?php echo esc_attr( implode( ';', $args['l-style'] ?? [] ) ); ?>">
		<?php echo esc_html( $args['label'] ?? $args['name'] ); ?>
	</label>
	<div	name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>"
			type="text" class="<?php echo esc_attr( implode( ' ', $args['class'] ?? [] ) . ' snk-field snk-field-proto' ); ?>"
			style="<?php echo esc_attr( $inner_styles ); ?>"
			placeholder="<?php echo esc_attr( $args['placeholder'] ?? '' ); ?>"
			value="<?php echo esc_attr( $args['value'] ?? '' ); ?>"
	></div>
</div>

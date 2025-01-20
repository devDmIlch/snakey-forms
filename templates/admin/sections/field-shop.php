<?php
/**
 * Field selection shop template file
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div id="field-shop" class="snk-field-shop snk-box">
	<?php if ( is_array( $args['fields'] ) ) : ?>
		<?php foreach ( $args['fields'] as $field ) : ?>
			<div class="single-field-selectable" name="<?php echo esc_attr( $field['name'] ); ?>" draggable="true">
				<?php $field['callback'](); ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

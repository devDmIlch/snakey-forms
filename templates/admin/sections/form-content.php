<?php
/**
 * Form content template file
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div id="form-content" class="form-content">
	<?php if ( is_array( $args['fields'] ) ) : ?>
		<?php
		foreach ( $args['fields'] as $field ) {
			load_template(
				$field['template'],
				false,
				[
					'state' => $field['state'] ?? [],
					'props' => $field['props'],
				]
			);
		}
		?>
		<div class="form-placeholder">
			<?php esc_html_e( 'Drag over a field or select field in the menu', 'snakebytes' ); ?>
		</div>
	<?php endif; ?>
</div>

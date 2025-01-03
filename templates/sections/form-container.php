<?php
/**
 * Form container template
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

// TODO: Update tempalate. This is temporary one.
?>
<form class="snakey-forms" name="<?php echo esc_attr( SNKFORMS_PREFIX . '_' . $args['form_id'] ); ?>">
	<?php
	if ( ! empty( $args['fields'] ) ) {
		foreach ( $args['fields'] as $field ) {
			load_template( $field['template'], false, $field['state'] ?? [] );
		}
		?>
		<!-- TODO: Create functionality for 'Submit' button customization -->
		<input type="submit" value="<?php esc_html_e( 'Submit', 'snakebytes' ); ?>">
		<?php
	}
	?>
</form>


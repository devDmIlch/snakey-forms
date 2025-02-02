<?php
/**
 * Text Field editor customization template
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

?>
<div class="snkfrm-field-editor">
	<div class="color-picker" style="width: 300px">

	</div>
	<?php
	// Field Name.
	load_template(
		SNKFORMS_PLUGIN_TEMPLATES . 'admin/editors/fields/text.php',
		false,
		[
			'name'  => 'name',
			'label' => __( 'Field Name', 'snakebytes' ),
			'value' => $args['name'],
		]
	);

	// Field Customizer.
	load_template( SNKFORMS_PLUGIN_TEMPLATES . 'admin/editors/fields/resizer.php', false, $args );
	?>
</div>

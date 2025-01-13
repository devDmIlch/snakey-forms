<?php
/**
 * Field resizer for field customization area
 *
 * @package snakey-forms/plugin
 * @since 0.0.1
 */

// This template requires arguments.
if ( empty( $args ) ) {
	return;
}

// See figma for example (add to ./.dev/.. after finalization).

?>
<div class="snakey-resizer">
	<div class="field-visual-editor-wrap">
		<div class="field-visual-editor-bg">
			<div class="field-visual-editor">

				<!-- Container margin resizer -->
				<?php foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) : ?>
					<div class="resize-margin-<?php echo esc_attr( $side ); ?>"><div class="phantom-selector"></div></div>
				<?php endforeach; ?>

				<div class="field-container">
					<!-- Field label -->
					<label hidden for="field-label"><?php esc_html_e( 'Field label name', 'snakebytes' ); ?></label>
					<input type="text" name="field-label" id="field-label" class="field-label" value="<?php echo esc_attr( $args['label'] ?? $args['name'] ); ?>">

					<div class="container-controls">
						<!-- Field label orientation -->
						<div class="label-orientation">
							<img
									class="svg-image"
									src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'flip-label.svg' ); ?>"
									alt="<?php esc_html_e( 'Flip Label Orientation', 'snakebytes' ); ?>">
						</div>
						<!-- Field zooming -->
						<div class="field-zoom">
							<img
									class="svg-image"
									src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'zoom-in.svg' ); ?>"
									alt="<?php esc_html_e( 'Zoom In Field Editor', 'snakebytes' ); ?>">
						</div>
					</div>

					<!-- Field/Label gap size -->
					<div class="resize-label-gap"><div class="phantom-selector"></div></div>

					<div class="field-inner">
						<!-- Input field dimensions resizer -->
						<div class="resize-horizontal"><div class="phantom-selector"></div></div>
						<div class="resize-vertical"><div class="phantom-selector"></div></div>

						<!-- Field padding resizer -->
						<?php foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) : ?>
							<div class="resize-padding-<?php echo esc_attr( $side ); ?>"><div class="phantom-selector"></div></div>
						<?php endforeach; ?>

						<!-- Placeholder field -->
						<label hidden for="field-placeholder"><?php esc_html_e( 'Field placeholder name', 'snakebytes' ); ?></label>
						<input type="text" name="field-placeholder" id="field-placeholder" class="field-placeholder">

						<!-- Border customization -->
						<div class="field-border">

							<!-- Border controls -->
							<?php foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) : ?>
								<div class="micro-container border-controls border-<?php echo esc_attr( $side ); ?>">
									<?php $mod_dir = in_array( $side, [ 'top', 'bottom' ], true ) ? 'vert' : 'horz'; ?>
									<div class="control-button resize-border-<?php echo esc_attr( $side ); ?>">
										<img
												class="svg-image"
												src="<?php echo esc_url( SNKFORMS_SVG_PATH . "width-mod-$mod_dir.svg" ); ?>"
												alt="<?php esc_html_e( 'Border Width Modifier', 'snakebytes' ); ?>">
									</div>
									<div class="control-button select-color-<?php echo esc_attr( $side ); ?>">
										<img
												class="svg-image"
												src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'colour-mod.svg' ); ?>"
												alt="<?php esc_html_e( 'Border Color Modifier', 'snakebytes' ); ?>">
									</div>
									<div class="control-button lock-border-style lock-border-style-<?php echo esc_attr( $side ); ?>">
										<img
												class="svg-image"
												src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'corner-lock.svg' ); ?>"
												alt="<?php esc_html_e( 'Lock/Unlock Homogeneous Border Style', 'snakebytes' ); ?>">
									</div>
								</div>
							<?php endforeach; ?>

							<!-- Corner controls -->
							<?php foreach ( [ 'top-right', 'bottom-right', 'bottom-left', 'top-left' ] as $corner ) : ?>
								<div class="micro-container border-corner-controls border-<?php echo esc_attr( $corner ); ?>">
									<div class="control-button resize-corner-<?php echo esc_attr( $corner ); ?>">
										<img
												class="svg-image"
												src="<?php echo esc_url( SNKFORMS_SVG_PATH . "corner-mod-$corner.svg" ); ?>"
												alt="<?php esc_html_e( 'Border Corner Radius Modifier', 'snakebytes' ); ?>">
									</div>
									<div class="control-button lock-corner-style">
										<img
												class="svg-image"
												src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'corner-lock.svg' ); ?>"
												alt="<?php esc_html_e( 'Lock/Unlock Homogeneous Border Corner Style', 'snakebytes' ); ?>">
									</div>
								</div>
							<?php endforeach; ?>

							<!-- Customizer for smaller screens. -->
							<div class="micro-container customize-small-screen">
								<div class="call-border-customizer">
									<img
											class="svg-image"
											src="<?php echo esc_url( SNKFORMS_SVG_PATH . 'call-customizer.svg' ); ?>"
											alt="<?php esc_html_e( 'Display Border Customization Window', 'snakebytes' ); ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Advanced options includes properties like max/min values, that cannot be visualized -->
	<div class="adv-options explicit-options-list">
		<div class="adv-options-trigger">
			<?php esc_html_e( 'Advanced options', 'snakebytes' ); ?>
		</div>
		<div class="adv-options-target">
			<!-- Field Width Settings -->
			<?php foreach ( [ 'height' => __( 'Field Height', 'snakebytes' ), 'width' => __( 'Field Width', 'snakebytes' ) ] as $type => $label ) : // phpcs:ignore ?>
				<div class="options-section">
					<div class="section-text">
						<?php echo esc_html( $label ); ?>
					</div>
					<?php foreach ( [ 'min' => __( 'Min', 'snakebytes' ), 'base' => __( 'Base', 'snakebytes' ), 'max' => __( 'Max', 'snakebytes' ) ] as $dim => $dim_label ) : // phpcs:ignore ?>
						<?php $name = $dim . '-' . $type; ?>
						<label for="<?php echo esc_attr( $name ); ?>">
							<?php echo esc_html( $dim_label ); ?>
						</label>
						<input id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" class="explicit-option" type="number" min="0">
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Input fields for visual editor -->
	<div class="hidden resizer-fields">
		<!-- Container Margin -->
		<label for="resize-margin-top">
			<?php esc_html_e( 'Container Margin Top', 'snakebytes' ); ?>
		</label>
		<input id="resize-margin-top" name="resize-margin-top" type="number" min="0">

	</div>
</div>

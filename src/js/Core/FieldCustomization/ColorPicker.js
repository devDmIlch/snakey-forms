// WordPress functions.
const { __ } = wp.i18n;

const ColorPicker = {
	defaultColor: [255, 0, 0],

	// Color picker element.
	colorPicker: null,
	// Parent element to which the color picker is attached.
	attach: null,

	// Flag that indicates whether the color picker pop-up is shown to the user.
	isActive: false,

	// Set origin color (selected in the radial selector).
	originColor: [255, 0, 0],
	// Set real color (selected in the hue selector).
	realColor: [255, 0, 0],
	// Set alpha value for color.
	alpha: 1,

	// Currently active element.
	activeElement: null,

	/**
	 * Converts RGBA color array into string.
	 **/
	convertRGBAtoString(color) {
		return 'rgba(' + color.join(', ') + ')';
	},

	/**
	 * Extrapolates real color to an origin one. (The brighter the color, the more accurate extrapolation).
	 */
	extrapolateOriginColor(color) {
		// Get dimming offset based on the value of the largest number.
		const dimOffset = Math.max(...color) / 255;
		// Reverse dimming.
		color = color.map((val) => Math.round(val / dimOffset));

		// Save minimal number to calculate equalizing offset.
		const relBottom = Math.min(...color);
		// Reverse equalizing.
		color = color.map((val) => Math.round( val - relBottom * ((255 - val) / (255 - relBottom))));

		return color;
	},

	/**
	 * Parses color in string format. Supports #RGB, #RGBA, #RRGGBB, #RRGGBBAA, rgb(r, g, b), rgb(r, g, b / a), rgba(r, g, b, a) formats.
	 **/
	parseStringColor: require('color-parse').default,

	/**
	 * Attaches color picker to the DOM element.
	 **/
	mount(element, color = { values: this.defaultColor, alpha: 1 }, attach = document.body) {
		// Initialize color picker.
		if (!this.colorPicker) {
			this.init(attach);
		}
		// Save color for this element.
		element.colorRef = color;

		// Show the color picker on clicking event.
		element.addEventListener('click', () => {
			// Check whether the pop-up is shown, but for other element.
			if (!this.isActive || element.isSameNode(this.activeElement)) {
				// Show/Hide color picker by toggling class.
				this.isActive = this.colorPicker.classList.toggle('shown');
			}
			// Update current active element.
			this.activeElement = this.isActive ? element : null;
			// Bail if the active element is not selected.
			if (!this.isActive) {
				// Save color for this element.
				element.colorRef = {
					origin: this.originColor,
					values: this.realColor,
					alpha: this.alpha,
				}

				return;
			}

			// Set color of the current element.
			this.originColor = element.colorRef.origin ?? this.extrapolateOriginColor(element.colorRef.values);
			this.realColor = element.colorRef.values;
			this.alpha = element.colorRef.alpha;
			// Optionally move color picker to the new attach element.
			if (!attach.isSameNode(attach)) {
				attach.insertAdjacentElement('beforeend', this.colorPicker);
			}
			// Dispatch an event about updated state.
			this.colorPicker.dispatchEvent(new CustomEvent('activation'));

			// Gap between trigger element and color picker.
			const delta = 25;
			// Find the location of the element.
			const rect = element.getBoundingClientRect();
			// Check whether it's possible to set the position to the left of the element.
			if (rect.left - this.colorPicker.offsetWidth - delta > 0) {
				this.colorPicker.style.left = rect.left - this.colorPicker.offsetWidth - delta + 'px';
			} else {
				this.colorPicker.style.left = rect.right + delta + 'px';
			}
			// Check whether it's possible to set the position to the left of the element.
			if (rect.top - this.colorPicker.offsetHeight - delta > 0) {
				this.colorPicker.style.top = rect.top - this.colorPicker.offsetHeight - delta + 'px';
			} else {
				this.colorPicker.style.top = rect.bottom + delta + 'px';
			}
		});
	},

	/**
	 * Initializes color picker.
	 **/
	init(attach) {
		// Initialize parent DOM element.
		this.colorPicker = document.createElement('div');
		this.colorPicker.classList.add('color-picker');
		attach.insertAdjacentElement('beforeend', this.colorPicker);

		// Initialize closing button.
		const closeButton = document.createElement('div');
		closeButton.classList.add('close-picker');
		closeButton.innerHTML = 'X';
		this.colorPicker.insertAdjacentElement('beforeend', closeButton);

		// Add area for selectors.
		const selectorArea = document.createElement('div');
		selectorArea.classList.add('selector-area');
		this.colorPicker.insertAdjacentElement('afterbegin', selectorArea);

		// Create radial selector for primary color.
		const radialSelector = document.createElement('div');
		radialSelector.classList.add('radial-selector');
		selectorArea.insertAdjacentElement('beforeend', radialSelector);

		// Create non-selectable area for radial selector.
		const radialNonSelectable = document.createElement('div');
		radialNonSelectable.classList.add('radial-inner');
		selectorArea.insertAdjacentElement('beforeend', radialNonSelectable);

		// Create square selector for hue.
		const hueSelector = document.createElement('div');
		hueSelector.classList.add('hue-selector');
		selectorArea.insertAdjacentElement('beforeend', hueSelector);

		// Create color preview.
		const colorPreview = document.createElement('div');
		colorPreview.classList.add('hue-preview');
		selectorArea.insertAdjacentElement('beforeend', colorPreview);
		// Previewer default offset from the cursor position.
		const previewerOffset = 20;

		// Create color alpha selector.
		const alphaSelector = document.createElement('div');
		alphaSelector.classList.add('alpha-selector');
		this.colorPicker.insertAdjacentElement('beforeend', alphaSelector);
		// Offset distance that allows user to select absolute values [0, 1] without pixel precision.
		const alphaOffset = 5;

		// Create area with number values.
		const numberArea = document.createElement('div');
		numberArea.classList.add('numeral-area');
		this.colorPicker.insertAdjacentElement('beforeend', numberArea);
		// Insert content of the numbers area.
		numberArea.insertAdjacentHTML('afterbegin',
			'<div class="val-wrap">' +
			'<label for="val-red">R</label><input id="val-red" name="val-red" class="val-area color-val" type="number" min="0" step="1" max="255">' +
			'</div>' +
			'<div class="val-wrap">' +
			'<label for="val-grn">G</label><input id="val-grn" name="val-grn" class="val-area color-val" type="number" min="0" step="1" max="255">' +
			'</div>' +
			'<div class="val-wrap">' +
			'<label for="val-blu">B</label><input id="val-blu" name="val-blu" class="val-area color-val" type="number" min="0" step="1" max="255">' +
			'</div>' +
			'<div class="val-wrap">' +
			'<label for="val-red">A</label><input id="val-alpha" name="val-alpha" class="val-area alpha-val" type="number" min="0" step="0.01" max="1">' +
			'</div>'
		);
		const colorInput = numberArea.querySelectorAll('.val-area');

		// Sets background for the 'hue selector' area.
		const setHueBackground = (color) => {
			hueSelector.style.backgroundImage = 'linear-gradient(transparent, #000), linear-gradient(90deg, #fff, rgb(' + color.join(', ') +'))';
		}

		// Sets background for the 'alpha selector' area.
		const setAlphaBackground = (color) => {
			alphaSelector.style.backgroundImage = 'linear-gradient(90deg, transparent ' + alphaOffset + 'px, rgb(' + color.join(', ') + ') calc(100% - ' + alphaOffset + 'px)';
		}

		// Sets input numbers with colors.
		const setInputValues = (color, alpha = null) => {
			// Set colors.
			color.forEach((val, index) => {
				colorInput[index].value = val;
			});
			// Set alpha.
			if (alpha || alpha === 0) {
				colorInput[3].value = alpha;
			}
		}


		// Initialize closing button.
		closeButton.addEventListener('click', (e) => {
			// Update the indication flag.
			this.isActive = false;
			// Remove class to hide the pop-up.
			this.colorPicker.classList.remove('shown');
			// Add last saved color.
			this.activeElement.colorRef = {
				origin: this.originColor,
				values: this.realColor,
				alpha: this.alpha,
			}
			// Remove current updated element.
			this.activeElement = null;
		});

		// Initialize radial selector.
		radialSelector.addEventListener('click', (e) => {
			const rect = radialSelector.getBoundingClientRect();

			let angle = Math.atan2(e.clientX - (rect.x + rect.width / 2), (rect.y + rect.height / 2) - e.clientY);
			if (angle < 0) {
				angle = 2 * Math.PI + angle;
			}
			this.originColor = [255, 0, 0];

			// Replace this loop of abomination with a proper formula. I'm too stupid :/
			let index = 1;
			let remDeg = 255 * (angle / Math.PI * 3);
			while (remDeg > 0) {
				this.originColor[index < 3 ? index : 0] += Math.min(255, remDeg);
				remDeg -= this.originColor[index < 3 ? index : 0];

				this.originColor[index - 1] -= Math.min(255, remDeg);
				remDeg -= 255 - this.originColor[index - 1];

				++index;
			}
			this.originColor = this.originColor.map((val) => Math.round(val));

			// Update color for the hue selector.
			setHueBackground(this.originColor);
			// Update color for the alpha selector.
			setAlphaBackground(this.originColor);
		});


		// Get the dimensions properties for hue selector.
		let hueSelectorRect = {};
		// Get computed styles to account for border.
		const borderWidth = parseInt(window.getComputedStyle(hueSelector).getPropertyValue('border-width'));

		this.colorPicker.addEventListener('activation', () => {
			// Update position of the hue selector after activation.
			hueSelectorRect = hueSelector.getBoundingClientRect();

			// Update colors on activation.
			setHueBackground(this.originColor);
			setAlphaBackground(this.realColor);
			setInputValues(this.realColor, this.alpha);
		});

		// Gets hue value based on cursor position.
		const getCurrentHue = (e) => {
			// Equalize the RGB values based on selected X axis.
			const modX = (e.clientX - hueSelectorRect.x - borderWidth) / (hueSelectorRect.width - borderWidth * 2);
			let color = this.originColor.map((val) => val + (255 - val) * (1 - (modX < 0 ? 0 : modX > 1 ? 1 : modX)));

			// Decrease the RGB values based on selected Y axis.
			const modY = (e.clientY - hueSelectorRect.y - borderWidth) / (hueSelectorRect.height - borderWidth * 2);
			color = color.map((val) => val * (1 - (modY < 0 ? 0 : modY > 1 ? 1 : modY)));

			// Round the values.
			color = color.map((val) => Math.round(val));

			return color;
		}

		// Initialize hue selector.
		hueSelector.addEventListener('mousemove', (e) => {
			const hoveredColor = getCurrentHue(e);

			// Update position of the preview.
			colorPreview.style.left = e.clientX + previewerOffset + 'px';
			colorPreview.style.top = e.clientY + previewerOffset + 'px';
			// Update color for the preview.
			colorPreview.style.backgroundColor = 'rgb(' + hoveredColor.join(', ') + ')';
		});

		// Initialize hue select.
		hueSelector.addEventListener('click', (e) => {
			// Reset alpha selector.
			this.alpha = 1;

			// Set clicked on color.
			this.realColor = getCurrentHue(e);
			// Set values in the input.
			setInputValues(this.realColor);
			// Set background for alpha selector.
			setAlphaBackground(this.realColor);

			// Dispatch a custom event.
			if (this.activeElement) {
				this.activeElement.dispatchEvent(new CustomEvent('change', { detail: { color: [...this.realColor, this.alpha] } }));
			}
		});

		// Hide previewer on hue selector leaving.
		hueSelector.addEventListener('mouseleave', () => {
			colorPreview.classList.remove('visible');
		});

		// Show previewer on entering hue selector.
		hueSelector.addEventListener('mouseenter', () => {
			colorPreview.classList.add('visible');
		});
		
		
		// Set alpha value on clicking selector.
		alphaSelector.addEventListener('click', (e) => {
			const rect = alphaSelector.getBoundingClientRect();
			// Calculate alpha based on the X axis.
			let alphaVal = Number(((e.clientX - rect.x - alphaOffset) / (rect.width - 2 * alphaOffset)).toFixed(2));
			// Round the values if they exceed [0, 1].
			this.alpha = alphaVal < 0 ? 0 : alphaVal > 1 ? 1 : alphaVal;

			// Set value in the input.
			setInputValues([], this.alpha);

			// Dispatch a custom event to notify trigger about the change.
			if (this.activeElement) {
				this.activeElement.dispatchEvent(new CustomEvent('change', { detail: { color: [...this.realColor, this.alpha] } }));
			}
		});

		// Initialize inputs.
		colorInput.forEach((input, index) => {
			const maxValue = parseInt(input.getAttribute('max'));
			const minValue = parseInt(input.getAttribute('min'));
			const step = parseFloat(input.getAttribute('step'));

			// Update the colors value on update.
			input.addEventListener('change', (e) => {
				// Make sure the value is in valid range.
				let inputValue = Number(input.value);
				if (isNaN(inputValue)) {
					inputValue = 0;
				}
				if (inputValue > maxValue) {
					inputValue = maxValue;
				}
				if (inputValue < minValue) {
					inputValue = minValue;
				}
				input.value = inputValue - inputValue % step;

				if (index < 3) {
					// Update color for rgb values.
					this.realColor[index] = inputValue;
					// Extrapolate origin color.
					this.originColor = this.extrapolateOriginColor(this.realColor);
					// Update background for hue and alpha selectors to reflect new colors.
					setHueBackground(this.originColor);
					setAlphaBackground(this.realColor);
				} else {
					// Update alpha value otherwise.
					this.alpha = inputValue;
				}

				// Dispatch a custom event to notify trigger about the change.
				if (this.activeElement) {
					this.activeElement.dispatchEvent(new CustomEvent('change', { detail: { color: [...this.realColor, this.alpha] } }));
				}
			});
		});

		// TODO: Add color history.
	}
}

export default ColorPicker;

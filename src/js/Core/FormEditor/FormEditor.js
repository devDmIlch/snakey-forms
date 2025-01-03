
// External Dependencies.
import axios from 'axios';

// Internal Dependencies.
import FieldCustomization from "../FieldCustomization/FieldCustomization";

// Form controller class.
const formDirector = {
	// Form DOM Element wrapper.
	formDOMEl: null,

	// List of form fields.
	formFields: [],
	// List of shop fields. (Shop fields are the selectable fields user can add to the form)
	shopFields: [],

	// Currently dragged over field.
	hoveredField: null,
	// Save relative position to avoid unnecessary calls.
	prevRelPos: null,
	// Delay before checking new dragged position to reduce the overload of browser.
	refreshActionDelay: 100,
	// Scheduled drag refresh action.
	scheduledRefreshAction: null,
	// Threshold at which the element starts to account for horizontal position.
	vertThreshold: 0.4,
	// Threshold at which the relative position should be updated.
	relPosUpdThreshold: 0.05,


	// Initialization Functions.

	initForm(formDOMEl) {
		// Check if the form element is valid.
		if (!formDOMEl) {
			return;
		}

		// Save form wrapper element to use later.
		this.formDOMEl = formDOMEl;
		// Find form fields containing within the wrapper.
		this.formFields = Array.from(formDOMEl.querySelectorAll('.snkfrm-field')) ?? [];
		// Find shop fields available for user.
		this.shopFields = Array.from(formDOMEl.querySelectorAll('.single-field-selectable')) ?? [];

		// Find input field.
		this.inputField = formDOMEl.querySelector('.form-input');
		// Find form content container.
		this.formContent = formDOMEl.querySelector('#form-content');

		// Initialize selected form fields.
		this.formFields.forEach((field) => { this.initFieldDragging(field); this.initFormField(field) });
		// Initialize selectable fields.
		this.shopFields.forEach((field) => this.initFieldSelectable(field));

		// Initialize placeholder.
		this.placeholderEl = formDOMEl.querySelector('.form-placeholder');
		this.initFieldDragging(this.placeholderEl);
	},

	// Initializes selectable field from the shop.
	initFieldSelectable(field) {
		const fieldType = field.getAttribute('name');

		const insertField = (event, relField = null) => {
			// Get relative position of the cursor prematurely.
			const relPos = relField ? this.getElementCursorRelPosition(relField, event) : null;

			let fieldDefaultName = 'field_' + (this.formFields.length + 1);
			// Check if the name is valid to avoid collisions.
			while (!this.validateFieldName(fieldDefaultName)) {
				fieldDefaultName = fieldDefaultName + '_new';
			}

			// Prepare default field state.
			const fieldState = {
				type: fieldType,
				state: {
					name: fieldDefaultName,
				},
			};

			axios.post('/wp-json/snkfrm/v1/admin/get-proto/' + fieldType, { state: fieldState.state }, {}).then((response) => {
				// Remove loading class.
				field.classList.remove('loading');
				// Create placeholder variable for the new element.
				let newField;

				// Insert field based on dropped position.
				if (relField) {
					// Check if hovered over placeholder.
					if (relField.isSameNode(this.placeholderEl)) {
						relField.insertAdjacentHTML('beforebegin', response.data.html);
						newField = relField.previousElementSibling;
						// Insert element into array of elements.
						this.formFields = [newField];
					} else {
						// Get index of the current field.
						const fieldIndex = this.getNodeArrayIndex(this.formFields, relField);
						// Find the right position to insert element.
						if (Math.abs(relPos.x - 0.5) < this.vertThreshold) {
							// Insert element based on vertical position.
							if (relPos.y > 0.5) {
								relField.insertAdjacentHTML('afterend', response.data.html);
								newField = relField.nextElementSibling;
								// Insert element into array with references
								this.formFields.splice(fieldIndex + 1, 0, newField);
							} else {
								relField.insertAdjacentHTML('beforebegin', response.data.html);
								newField = relField.previousElementSibling;
								// Insert element into array with references
								this.formFields.splice(fieldIndex, 0, newField);
							}
						} else {
							// Insert element based on horizontal position.
							if (relPos.x > 0.5) {
								relField.insertAdjacentHTML('afterend', response.data.html);
								newField = relField.nextElementSibling;
								// Insert element into array with references.
								this.formFields.splice(fieldIndex + 1, 0, newField);
							} else {
								relField.insertAdjacentHTML('beforebegin', response.data.html);
								newField = relField.previousElementSibling;
								// Insert element into array with references.
								this.formFields.splice(fieldIndex, 0, newField);
							}
						}
					}
				}

				// Insert field as the last.
				if (!relField) {
					if (this.formFields.length < 1) {
						// Insert element inside of the container for content.
						this.formContent.insertAdjacentHTML('afterbegin', response.data.html);
						// Save field to push it to the references array.
						newField = this.formContent.childNodes[0];
					} else {
						// Get the last element in the array of field element references.
						const targetField = this.formFields[this.formFields.length - 1];
						// Insert field in the DOM.
						targetField.insertAdjacentHTML('afterend', response.data.html);
						// Save field to push it to the references array.
						newField = targetField.nextElementSibling;
					}
					// Insert field in the array with references.
					this.formFields.push(newField);
				}

				// Initialize new field.
				this.initFormField(newField);
				// Initialize dragging actions.
				this.initFieldDragging(newField);

				// Save initial field state.
				newField.props = fieldState;
				// Save changes.
				this.saveFields();
			}).catch((error) => {
				// TODO: Do some proper error handling later.
				console.log(error);
			});
		}

		field.addEventListener('click', (e) => {
			// Insert selected field.
			insertField(e);
		});

		field.addEventListener('dragend', (e) => {
			// Check whether the user is still hovering over the last field.
			if (!this.hoveredField || !this.checkHoveredFieldPos(e)) {
				return;
			}

			// Insert selected field.
			insertField(e, this.hoveredField);
		});
	},

	// Initializes dragging properties of a field in the content area.
	initFieldDragging(field) {
		field.addEventListener('dragover', (e) => {
			// Set property to indicate that the item's still has cursor over it.
			field.dragHovered = true;

			if (!this.scheduledRefreshAction) {
				this.scheduledRefreshAction = setTimeout(() => {
					// Save field user is trying to drag over element.
					this.hoveredField = field;
					// Remove scheduled action.
					this.scheduledRefreshAction = null;

					// Update insert position property, if the cursor is still over element.
					if (field.dragHovered) {
						this.updateElementInsertProp(field, e);
					}
				}, this.refreshActionDelay);
			}
		});

		field.addEventListener('dragleave', () => {
			// Set property to indicate that the item's no longer has cursor over it.
			field.dragHovered = false;
			// Remove dragged class.
			field.removeAttribute('insert');
		});

		if ('true' !== field.getAttribute('draggable')) {
			return;
		}

		field.addEventListener('dragend', (e) => {
			// Check if the drop position is valid.
			if (!this.hoveredField || !this.checkHoveredFieldPos(e)) {
				return;
			}

			// Get index of the dragged element in the references array.
			const fieldIndex = this.getNodeArrayIndex(this.formFields, field);
			// Remove field from references array.
			this.formFields.splice(fieldIndex, 1);

			// Get relative position of the cursor.
			const relPos = this.getElementCursorRelPosition(this.hoveredField, e);
			// Get index of the target element in the references array.
			const targetFieldIndex = this.getNodeArrayIndex(this.formFields, this.hoveredField);

			// Find the right position to insert element.
			if (Math.abs(relPos.x - 0.5) < this.vertThreshold) {
				// Insert element based on vertical position.
				if (relPos.y > 0.5) {
					this.hoveredField.insertAdjacentElement('afterend', field);
					// Insert element into array of elements.
					this.formFields.splice(targetFieldIndex + 1, 0, field);
				} else {
					this.hoveredField.insertAdjacentElement('beforebegin', field);
					// Insert element into array of elements.
					this.formFields.splice(targetFieldIndex, 0, field);
				}
			} else {
				// Insert element based on horizontal position.
				if (relPos.x > 0.5) {
					this.hoveredField.insertAdjacentElement('afterend', field);
					// Insert element into array of elements.
					this.formFields.splice(targetFieldIndex + 1, 0, field);
				} else {
					this.hoveredField.insertAdjacentElement('beforebegin', field);
					// Insert element into array of elements.
					this.formFields.splice(targetFieldIndex, 0, field);
				}
			}

			// Save new fields position.
			this.saveFields();
		});
	},

	// Initializes actions with the selected field.
	initFormField(field) {
		// Initialize the properties if they are supplied.
		if (field.getAttribute('props')) {
			field.props = JSON.parse(field.getAttribute('props'));
		}

		// Initialize field controls.
		const fieldControls = field.querySelector('.proto-controls');
		if (fieldControls) {

			const removeButton = fieldControls.querySelector('.remove');
			if (removeButton) {
				removeButton.addEventListener('click', () => {
					// Remove field in the references array.
					this.formFields.splice(this.getNodeArrayIndex(this.formFields, field), 1);
					// Remove field in the form itself.
					field.remove();
					// Save updated fields.
					this.saveFields();
				});
			}

			const moveUpButton = fieldControls.querySelector('.move-up');
			if (moveUpButton) {
				moveUpButton.addEventListener('click', () => {
					// Check whether previous field exists.
					if (!field.previousElementSibling) {
						return;
					}

					// Get the position of the element.
					const fieldPos = this.getNodeArrayIndex(this.formFields, field);
					// Swap elements in the references array.
					[this.formFields[fieldPos], this.formFields[fieldPos - 1]] = [this.formFields[fieldPos - 1], this.formFields[fieldPos]];
					// Change element in the DOM.
					field.previousElementSibling.insertAdjacentElement('beforebegin', field);
					// Save updated fields.
					this.saveFields();
				});
			}

			const moveDownButton = fieldControls.querySelector('.move-down');
			if (moveDownButton) {
				moveDownButton.addEventListener('click', () => {
					// Check whether next field exists and it's not a placeholder.
					if (!field.nextElementSibling || this.getNodeArrayIndex(this.formFields, field.nextElementSibling) < 0) {
						return;
					}

					// Get the position of the element.
					const fieldPos = this.getNodeArrayIndex(this.formFields, field);
					// Swap elements in the references array.
					[this.formFields[fieldPos], this.formFields[fieldPos + 1]] = [this.formFields[fieldPos + 1], this.formFields[fieldPos]];
					// Change element in the DOM.
					field.nextElementSibling.insertAdjacentElement('afterend', field);
					// Save updated fields.
					this.saveFields();
				});
			}
		}

		// Check whether the field should be customizable.
		if (!field.classList.contains('is-customizable')) {
			return;
		}

		// Call field customizer window on click.
		field.addEventListener('click', () => {
			FieldCustomization.callCustomizer(field.props.type, field.props.state, field, (state) => this.updateFieldContent(field, state));
		});
	},

	// Updates field content.
	updateFieldContent(field, state) {
		// Update field state in the element.
		field.props.state = state;

		// Update fields value.
		this.saveFields();

		// Request new content for the field.
		axios.post('/wp-json/snkfrm/v1/admin/get-proto/' + field.props.type, { state: state }, {}).then((response) => {
			// Create element from the received html.
			const template = document.createElement('template');
			template.innerHTML = response.data.html;

			// Replace field content with updated values.
			field.innerHTML = template.content.firstChild.innerHTML;
		}).catch((error) => {
			// TODO: Some proper error handling.
			console.log(error);
		});
	},

	// Worker Functions.

	updateElementInsertProp(element, event) {
		const relPos = this.getElementCursorRelPosition(element, event);

		if (Math.abs(relPos.x - 0.5) < this.vertThreshold) {
			element.setAttribute('insert', relPos.y > 0.5 ? 'bottom' : 'top');
		} else {
			element.setAttribute('insert', relPos.x > 0.5 ? 'right' : 'left');
		}
	},

	getElementCursorRelPosition(element, event) {
		const elRect = element.getBoundingClientRect();

		return {
			x: (event.clientX - elRect.left) / element.offsetWidth,
			y: (event.clientY - elRect.top) / element.offsetHeight,
		};
	},

	getNodeArrayIndex(nodeList, element) {
		for (let i = 0; i < nodeList.length; ++i) {
			if (nodeList[i].isSameNode(element)) {
				return i;
			}
		}
		// Return -1 if element was not found.
		return -1;
	},


	// Helper functions.

	validateFieldName(name) {
		let isValid = true;

		// Go through the fields to make sure the name is not a duplicate.
		this.formFields.forEach((field) => {
			if (field.props.state.name === name) {
				isValid = false;
			}
		});

		return isValid;
	},

	checkHoveredFieldPos(event) {
		const relPos = this.getElementCursorRelPosition(this.hoveredField, event);
		return !(relPos.x > 1 || relPos.y > 1 || relPos.x < 0 || relPos.y < 0);
	},


	// Saving functions.

	saveFields() {
		this.inputField.value = JSON.stringify(Object.assign({}, this.formFields.map((el) => el.props)));
	},
}

document.addEventListener('DOMContentLoaded', () => {
	// Search for the form editor area.
	formDirector.initForm(document.querySelector('#snk-form-editor'));
});

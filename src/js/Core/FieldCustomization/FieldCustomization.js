
import axios from "axios";

const FieldCustomization = {
	prevReference: null,

	initCustomizer() {
		// Create new element for customizer.
		this.customizer = document.createElement('div');
		// Append a class to the customizer.
		this.customizer.classList.add('snk-field-customizer');
		// Append element at the end of the document.
		document.body.insertAdjacentElement('beforeend', this.customizer);
	},

	closeCustomizerOnClick() {
		// Add a check to close customizer upon clicking outside.
		document.addEventListener('click', (e) => {
			console.log('test');

			// Check whether a user clicked referenced item.
			if (this.prevReference !== null && this.prevReference.contains(e.target)) {
				// Create new event as this one was aborted.
				this.closeCustomizerOnClick();
				// Abort the rest of the event.
				return;
			}

			// Check whether clicked element is outside the customizer.
			if (this.customizer.contains(e.target)) {
				// Create new event as this one was aborted.
				this.closeCustomizerOnClick();
				// Abort the rest of the event.
				return;
			}

			this.customizer.classList.add('hidden');
		}, {once: true});
	},

	initFields() {
		// Find the input fields added to customizer area.
		const inputFields = this.customizer.querySelectorAll('input');

		// Prepare an object of the elements.
		this.state = {};
		inputFields.forEach((el) => {
			this.state[el.name] = el.value;
		});

		// Initialize update of the customizer state value on field change.
		inputFields.forEach((el) => el.addEventListener('change', () => {
			this.state[el.name] = el.value;
			// Send callback to notify about the change.
			if (this.handleUpdate instanceof Function) {
				this.handleUpdate(this.state);
			}
		}));
	},

	callCustomizer(fieldType, fieldState, reference = null, onUpdate = null) {
		// Check whether the customizer field was initialized.
		if (!this.customizer) {
			this.initCustomizer();
		}

		// Check whether the reference is the same as previous reference, to avoid unnecessary REST calls.
		if (!reference && reference.isSameNode(this.prevReference)) {
			this.customizer.classList.remove('hidden');

			return;
		}

		// Update callback function to send updated values.
		this.handleUpdate = onUpdate;

		// Do an endpoint call to request customization area.
		axios.post('/wp-json/snkfrm/v1/admin/get-editor/' + fieldType, { state: fieldState }, {}).then((response) => {
			// Reset reference of the last loaded item.
			this.prevReference = reference;

			// Remove previous content from customizer.
			this.customizer.innerHTML = null;
			// Show customizer.
			this.customizer.classList.remove('hidden');
			// Add new html crated in the endpoint.
			this.customizer.insertAdjacentHTML('afterbegin', response.data.html);

			// Initialize the fields.
			this.initFields();

			// Add event to close customizer when a user clicks outside.
			this.closeCustomizerOnClick();
		}).catch((error) => {
			// TODO: Do some proper error handling later.
		});
	},
};

export default FieldCustomization;

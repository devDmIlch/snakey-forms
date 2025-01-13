
const Resizer = {
	init(resizer, onUpdate = null) {
		// Search for the wrapper element of the field.
		const fieldContainer = resizer.querySelector('.field-visual-editor');
		if (!fieldContainer) {
			return;
		}
		// Search for the field element.
		const fieldInner = resizer.querySelector('.field-inner');
		if (!fieldInner) {
			return;
		}

		const initFieldResizeElements = () => {
			// Use this variable to keep only one mousemove event active to avoid slowdowns.
			let currentMouseMoveAction = null;

			/**
			 * Initializes resizing mechanic for the element.
			 *
			 * @param draggedEl Draggable resizer element.
			 * @param modEl     Modified element.
			 * @param props     Object with parameters
			 *
			 * @return Object with resizing methods.
			 **/
			const initResizer = (draggedEl, modEl = null, props = {}) => {
				// Check if the dragged element exists.
				if (!draggedEl) {
					return;
				}

				// Updates mod style with provided value.
				const updateModStyle = (value) => {
					// Modify target element style.
					if (props.modStyle) {
						modEl.style[props.modStyle] = value < 0 ? 0 : value + 'px';
					}

					// Modify the dragged element itself.
					if (props.modSelf && props.modSelfStyle) {
						draggedEl.style[props.modSelfStyle] = value < 0 ? 0 : value + 'px';
					}

					// Update last modified value.
					lastModValue = value;
				}

				// Get existing value of the modified element.
				const getCurrentModValue = () => {
					let value = 0;

					if (props.modProp) {
						value = parseInt(modEl[props.modProp].length ? modEl[props.modProp] : value);
					}
					if (props.modStyleRef) {
						value = parseInt(modEl.style[props.modStyleRef].length ? modEl.style[props.modStyleRef] : value);
					}
					if (props.getModInitialValue instanceof Function) {
						value = props.getModInitialValue();
					}

					return value;
				}

				// Returns last mod value.
				const getLastModValue = () => {
					return lastModValue;
				}

				// Sets handler for 'resize' event.
				const setResizeHandler = (callable) => {
					onResize = callable;
				}

				// Removes handler for 'resize' event.
				const removeResizeHandler = () => {
					onResize = null;
				}

				// Save last value.
				let lastModValue = getCurrentModValue();
				// Add flag to keep track whether the item is being currently dragged.
				let isDragActive = false;
				// Create 'resize' event variable.
				let onResize = null;

				draggedEl.addEventListener('pointerdown', (e) => {
					e.preventDefault();
					// Set dragging flag to true.
					isDragActive = true;

					// Save pointer ID to keep right type of cursor.
					const pointerId = e.pointerId;
					draggedEl.setPointerCapture(e.pointerId);

					// Save initial mouse position to reference during resizing.
					const initialMousePos = {x: e.pageX, y: e.pageY};

					// Save initial property value to reference during resizing.
					let initialValue = getCurrentModValue();

					// Save dragging event to the variable.
					currentMouseMoveAction = (e) => {
						e.preventDefault();
						// Get offset of the mouse relative to the element.
						const mouseOffset = props.isVertical ? e.pageY - initialMousePos.y : e.pageX - initialMousePos.x;
						// Calculate value modifier based on the offset and properties.
						const mouseOffsetMod = mouseOffset * (props.inversePrimeAxis ? -1 : 1) * (props.modRatio ?? 1);
						// Find a new value.
						let newValue = initialValue + mouseOffsetMod;

						// Do adjustment for diagonal resizing.
						if (props.isDiagonal) {
							// Get offset in alternative axis.
							const altOffset = !props.isVertical ? e.pageY - initialMousePos.y : e.pageX - initialMousePos.x;
							// Calculate value modifier based on the offset and properties.
							const altOffsetMod = altOffset * (props.inverseSecondaryAxis ? -1 : 1) * (props.modRatio ?? 1);
							// Use fast approximation formula to get the distance.
							newValue = initialValue + ((Math.abs(altOffsetMod) > Math.abs(mouseOffsetMod)) ? altOffsetMod * 1.4 + mouseOffsetMod : mouseOffsetMod * 1.4 + altOffsetMod);
						}

						// Round the value and update element style.
						updateModStyle(newValue.toFixed(2));

						// Handle the 'resize' event trigger.
						if (onResize) {
							onResize();
						}
					}

					// Add mouse movement event to track dragging.
					document.addEventListener('pointermove', currentMouseMoveAction);

					// Initialize on event to finish drag tracking.
					document.addEventListener('pointerup', () => {
						// Set dragging flag to false.
						isDragActive = false;
						// Remove mouse movement event to prevent clogging.
						document.removeEventListener('pointermove', currentMouseMoveAction);
					}, {once: true});
				});

				return {
					updateStyle: updateModStyle,
					getModValue: getLastModValue,
					// Event Handlers.
					setUpdateEventHandler: setResizeHandler,
					removeUpdateEventHandler: removeResizeHandler,
				};
			}

			/**
			 * Initializes interlocking mechanics for an array of resizers.
			 *
			 * @param isLocked      Whether the interlocking is initially initialized.
			 * @param lockControls  Array of locking/Unlocking elements.
			 * @param styleControls Arrays of resizing objects.
			 **/
			const initLockGroup = (isLocked = false, lockControls = [], ...styleControls) => {

				// Toggles locking for the group.
				const toggleLockControls = (lockRef, unlock = isLocked) => {
					// Toggle active lock flag.
					isLocked = !unlock;

					// Update class list for each locking elements.
					lockControls.forEach((lockEl) => {
						if (isLocked) {
							lockEl.classList.add('is-locked');
						} else {
							lockEl.classList.remove('is-locked');
						}
					});

					// Get reference controls to pull mod values from.
					const refControls = lockRef.getRefControls();

					styleControls.forEach((styleGroup, index) => {
						// Get modification value from primary control to apply to other controls.
						const modValue = refControls[index].getModValue();

						styleGroup.forEach((controlObj) => {
							if (isLocked) {
								// Update value for each resizer object.
								controlObj.updateStyle(modValue);
								// Set 'resize' event handlers for each resizer object.
								controlObj.setUpdateEventHandler(() => {
									// Get modification value from updated resizer.
									const modValue = controlObj.getModValue();
									// Update value for each resizer object.
									styleGroup.forEach((el) => {
										el.updateStyle(modValue);
									});
								});
							} else {
								// Remove 'resizer' event.
								controlObj.removeUpdateEventHandler();
							}
						});
					});
				}

				// Initialize locking controls.
				lockControls.forEach((lockEl, index) => {
					// Set up method to retrieve reference controls for each individual lock.
					lockEl.getRefControls = () => {
						return styleControls.map((arrayEl) => {
							return arrayEl[index];
						});
					};
					// Add event to actuate lock.
					lockEl.addEventListener('click', () => {
						// Update lock with value based on relative index.
						toggleLockControls(lockEl);
					});
				});

				return {
					toggleLock: toggleLockControls,
				}
			}

			/**
			 * Initializes positional display of the elements based on their distance to the mouse cursor.
			 *
			 * @param groupElements An array of affected element.
			 * @param props         An object with custom properties.
			 **/
			const initGroupHighlight = (groupElements, props = {}) => {
				// Distance at which an element should always be visible.
				const minThreshold = 100;
				// Distance at which an element should be visible based on relative distance priority.
				const maxThreshold = 300;

				// Forced delay between refreshing pointer data.
				const delayTime = 100;
				// Set up a flag to track the delayed action.
				let isDelayActive = false;

				// Name of the class that indicates 'display' status of the element.
				const displayClass = 'is-displayed';

				const findAndHighlightElements = (e) => {
					// Wait if the delay is active.
					if (isDelayActive) {
						return;
					}

					// Set the delay flag.
					isDelayActive = true;
					// Create a delayed event to update the flag after the delay has expired.
					setTimeout(() => {
						isDelayActive = false;
					}, delayTime);

					// Prioritized element that should be displayed.
					let priorityEl = null;
					// Distance to the current prioritised element.
					let priorityDist = maxThreshold;
					// Elements that must be displayed, as they pass minimum threshold.
					let requiredEl = [];

					groupElements.forEach((element) => {
						// Remove previous display status.
						element.classList.remove(displayClass);

						// Get position of the element on the page.
						const elPos = element.getBoundingClientRect();
						// Calculate absolute distance to the center of the element for each of the axis.
						const xOffset = Math.abs(elPos.x + elPos.width / 2 - e.pageX);
						const yOffset = Math.abs(elPos.y + elPos.height / 2 - e.pageY);
						// Calculate distance to the cursor based on approximation formula.
						const approxDist = (xOffset > yOffset) ? 1.4 * xOffset + yOffset : 1.4 * yOffset + xOffset;

						// Check whether the distance surpasses minimal distance.
						if (approxDist < minThreshold) {
							requiredEl.push(element);
						}
						// Check whether the required elements exist to skip checking for priority element.
						if (requiredEl.length) {
							return;
						}
						// Check whether the distance surpasses maximum distance.
						if (approxDist < maxThreshold) {
							// Check whether this element is closer to cursor than previously found priority element.
							if (approxDist < priorityDist) {
								priorityEl = element;
								priorityDist = approxDist;
							}
						}
					});

					// If required elements exist display them and bail afterward.
					if (requiredEl.length) {
						requiredEl.forEach((element) => {
							element.classList.add(displayClass);
						});

						return;
					}
					// If the priority element exists, display it.
					if (priorityEl) {
						priorityEl.classList.add(displayClass);
					}
				}

				resizer.addEventListener('mouseenter', () => {
					// Start tracking position of the cursor, once it enters the resizer area.
					resizer.addEventListener('mousemove', findAndHighlightElements);
				});

				resizer.addEventListener('mouseleave', () => {
					// Remove event tracking position of the cursor.
					resizer.removeEventListener('mousemove', findAndHighlightElements);
					// Hide all elements after leaving the resizer area.
					setTimeout(() => {
						groupElements.forEach((element) => {
							element.classList.remove(displayClass);
						});
					}, delayTime);
				});
			}

			/**
			 * Initialized tracking of the element to update relative element classes.
			 **/
			const initControlScaling = (scaledEl, relElement, relProp, props = {}) => {
				// Distances at which scaled element should be updated.
				const thresholds = props.thresholds ?? {
					hide:    props.hide ?? Math.min(scaledEl.offsetHeight, scaledEl.offsetWidth) * 2,
					compact: props.compact ?? Math.max(scaledEl.offsetHeight, scaledEl.offsetWidth) * 1.2,
				};

				// Forced delay between refreshing relative element data.
				const delayTime = 100;
				// Set up a flag to track the delayed action.
				let isDelayActive = false;

				const checkElementUpdate = () => {
					// Get the value of the relative element that should be checked.
					const relElValue = relElement[relProp];
					// Create a flag to track a first match in size.
					let thresholdFound = false;

					for (const [className, size] of Object.entries(thresholds)) {
						if (!thresholdFound && size > relElValue) {
							scaledEl.classList.add(className);
							// Update flag to disallow adding new classes.
							thresholdFound = true;
						} else {
							scaledEl.classList.remove(className);
						}
					}
				}

				const observer = new MutationObserver(() => {
					// Wait if the delay is active.
					if (isDelayActive) {
						return;
					}

					// Set the delay flag.
					isDelayActive = true;
					// Create a delayed event.
					setTimeout(() => {
						// Update the flag to allow queueing new event.
						isDelayActive = false;
						// Do the check to update element values.
						checkElementUpdate();
					}, delayTime);
				});

				observer.observe(relElement, {attributeFilter: ['style']});

				// Run initial check.
				checkElementUpdate();
			}

			// Vertical Resizers.
			initResizer(resizer.querySelector('.resize-vertical'), fieldInner, {
				isVertical: true,
				modProp: 'offsetHeight',
				modStyle: 'height',
				getModInitialValue: () => {
					return fieldInner.offsetHeight -
						(fieldInner.style.paddingTop.length > 0 ? parseInt(fieldInner.style.paddingTop) : 0) -
						(fieldInner.style.paddingBottom.length > 0 ? parseInt(fieldInner.style.paddingBottom) : 0);
				},
			});
			initResizer(resizer.querySelector('.resize-horizontal'), fieldInner, {
				isVertical: false,
				modProp: 'offsetWidth',
				modStyle: 'width',
				getModInitialValue: () => {
					return fieldInner.offsetWidth -
						(fieldInner.style.paddingRight.length > 0 ? parseInt(fieldInner.style.paddingRight) : 0) -
						(fieldInner.style.paddingLeft.length > 0 ? parseInt(fieldInner.style.paddingLeft) : 0);
				},
			});

			// Padding Resizers.
			initResizer(resizer.querySelector('.resize-padding-top'), fieldInner, {
				isVertical: true,
				modStyleRef: 'paddingTop',
				modStyle: 'padding-top',
				modSelf: true,
				modSelfStyle: 'height',
			});
			initResizer(resizer.querySelector('.resize-padding-right'), fieldInner, {
				isVertical: false,
				inversePrimeAxis: true,
				modStyleRef: 'paddingRight',
				modStyle: 'padding-right',
				modSelf: true,
				modSelfStyle: 'width',
			});
			initResizer(resizer.querySelector('.resize-padding-bottom'), fieldInner, {
				isVertical: true,
				modStyleRef: 'paddingBottom',
				modStyle: 'padding-bottom',
				modSelf: true,
				modSelfStyle: 'height',
			});
			initResizer(resizer.querySelector('.resize-padding-left'), fieldInner, {
				isVertical: false,
				modStyleRef: 'paddingLeft',
				modStyle: 'padding-left',
				modSelf: true,
				modSelfStyle: 'width',
			});

			// Margin Resizers.
			initResizer(resizer.querySelector('.resize-margin-top'), fieldContainer, {
				isVertical: true,
				modStyleRef: 'paddingTop',
				modStyle: 'padding-top',
				modSelf: true,
				modSelfStyle: 'height',
			});
			initResizer(resizer.querySelector('.resize-margin-right'), fieldContainer, {
				isVertical: false,
				modStyleRef: 'paddingRight',
				modStyle: 'padding-right',
				modSelf: true,
				modSelfStyle: 'width',
			});
			initResizer(resizer.querySelector('.resize-margin-bottom'), fieldContainer, {
				isVertical: true,
				modStyleRef: 'paddingBottom',
				modStyle: 'padding-bottom',
				modSelf: true,
				modSelfStyle: 'height',
			});
			initResizer(resizer.querySelector('.resize-margin-left'), fieldContainer, {
				isVertical: false,
				modStyleRef: 'paddingLeft',
				modStyle: 'padding-left',
				modSelf: true,
				modSelfStyle: 'width',
			});

			// Border Width Resizer.
			const rBorderTop = initResizer(resizer.querySelector('.resize-border-top'), fieldInner, {
				isVertical: true,
				modRatio: .1,
				modStyle: 'border-top-width',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-top-width'));
				},
			});
			const rBorderRight = initResizer(resizer.querySelector('.resize-border-right'), fieldInner, {
				isVertical: false,
				inverse: true,
				modRatio: .1,
				modStyle: 'border-right-width',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-right-width'));
				},
			});
			const rBorderBottom = initResizer(resizer.querySelector('.resize-border-bottom'), fieldInner, {
				isVertical: true,
				modRatio: .1,
				modStyle: 'border-bottom-width',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-bottom-width'));
				},
			});
			const rBorderLeft = initResizer(resizer.querySelector('.resize-border-left'), fieldInner, {
				isVertical: false,
				modRatio: .1,
				modStyle: 'border-left-width',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-left-width'));
				},
			});

			// Initialize border style locking.
			initLockGroup(false, resizer.querySelectorAll('.lock-border-style'),[
				rBorderTop, rBorderRight, rBorderBottom, rBorderLeft
			]);

			// Border Corner Resizers.
			const rCornerTopLeft = initResizer(resizer.querySelector('.resize-corner-top-left'), fieldInner, {
				isVertical: true,
				isDiagonal: true,
				modRatio: .25,
				modStyle: 'border-top-left-radius',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-top-left-radius'));
				},
			});
			const rCornerTopRight = initResizer(resizer.querySelector('.resize-corner-top-right'), fieldInner, {
				isVertical: false,
				isDiagonal: true,
				inversePrimeAxis: true,
				modRatio: .25,
				modStyle: 'border-top-right-radius',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-top-right-radius'));
				},
			});
			const rCornerBottomRight = initResizer(resizer.querySelector('.resize-corner-bottom-right'), fieldInner, {
				isVertical: true,
				isDiagonal: true,
				inversePrimeAxis: true,
				inverseSecondaryAxis: true,
				modRatio: .25,
				modStyle: 'border-bottom-right-radius',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-bottom-right-radius'));
				},
			});
			const rCornerBottomLeft = initResizer(resizer.querySelector('.resize-corner-bottom-left'), fieldInner, {
				isVertical: false,
				isDiagonal: true,
				inverseSecondaryAxis: true,
				modRatio: .25,
				modStyle: 'border-bottom-left-radius',
				getModInitialValue: () => {
					return parseInt(getComputedStyle(fieldInner).getPropertyValue('border-bottom-left-radius'));
				},
			});

			// Initialize corner style locking.
			initLockGroup(false, resizer.querySelectorAll('.lock-corner-style'),[
				rCornerTopRight, rCornerBottomRight, rCornerBottomLeft, rCornerTopLeft
			]);

			// Initialize border control positional element highlighting.
			initGroupHighlight(resizer.querySelectorAll('.border-controls,.border-corner-controls'));

			// Initialize border controls scaling based on border length.
			initControlScaling(resizer.querySelector('.border-controls.border-top'), fieldInner, 'offsetWidth');
			initControlScaling(resizer.querySelector('.border-controls.border-right'), fieldInner, 'offsetHeight');
			initControlScaling(resizer.querySelector('.border-controls.border-bottom'), fieldInner, 'offsetWidth');
			initControlScaling(resizer.querySelector('.border-controls.border-left'), fieldInner, 'offsetHeight');
		}

		initFieldResizeElements();
	}
}

export default Resizer;

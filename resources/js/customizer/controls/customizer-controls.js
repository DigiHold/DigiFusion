/**
 * DigiFusion Customizer Controls JS
 *
 * Handles rendering and functionality of React-based customizer controls.
 */

(function(wp, $) {
    'use strict';

    // Initialize when Customizer is ready
    wp.customize.bind('ready', function() {
        const { createElement, render, useState, useEffect } = wp.element;
        const { __ } = wp.i18n;
        const { 
            Button, 
            RangeControl, 
            PanelBody, 
            TextControl, 
            ToggleControl,
            TabPanel,
            ColorPicker,
            __experimentalToggleGroupControl: ToggleGroupControl, 
            __experimentalToggleGroupControlOption: ToggleGroupControlOption
        } = wp.components;
        const { MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor || wp.editor;

        // Initialize global state for responsive controls
        window.digi = window.digi || {};
        window.digi.responsiveState = {
            activeDevice: 'desktop',
            subscribers: [],
            
            // Subscribe to device state changes
            subscribe: function(callback) {
                this.subscribers.push(callback);
                // Initial call with current state
                callback(this.activeDevice);
                
                // Return unsubscribe function
                return () => {
                    this.subscribers = this.subscribers.filter(sub => sub !== callback);
                };
            },
            
            // Set active device and notify subscribers
            setDevice: function(device) {
                if (this.activeDevice !== device) {
                    this.activeDevice = device;
                    // Set data attribute on customizer preview iframe
                    const previewFrame = document.getElementById('customize-preview');
                    if (previewFrame && previewFrame.contentDocument) {
                        previewFrame.contentDocument.body.setAttribute('data-device', device);
                    }
                    // Notify subscribers
                    this.notifySubscribers();
                    // Sync with WordPress Customizer device buttons
                    this.syncCustomizerDevices(device);
                }
            },
            
            // Toggle through devices
            toggleDevice: function() {
                const nextDevice = this.getNextDevice();
                this.setDevice(nextDevice);
            },
            
            // Get next device in cycle
            getNextDevice: function() {
                switch (this.activeDevice) {
                    case 'desktop': return 'tablet';
                    case 'tablet': return 'mobile';
                    default: return 'desktop';
                }
            },
            
            // Notify all subscribers
            notifySubscribers: function() {
                this.subscribers.forEach(callback => callback(this.activeDevice));
            },
            
            // Sync with WordPress Customizer device preview buttons
            syncCustomizerDevices: function(device) {
                const footerDevices = document.querySelector('.wp-full-overlay-footer .devices');
                if (footerDevices) {
                    const buttons = footerDevices.querySelectorAll('button');
                    buttons.forEach(button => {
                        if (button.classList.contains('preview-' + device)) {
                            button.click();
                        }
                    });
                }
            }
        };

        // Initialize device icons
        window.digi.deviceIcons = {
            desktop: createElement('span', { className: 'dashicons dashicons-desktop' }),
            tablet: createElement('span', { className: 'dashicons dashicons-tablet' }),
            mobile: createElement('span', { className: 'dashicons dashicons-smartphone' })
        };

        // Sync with WordPress Customizer device buttons
        const footerDevices = document.querySelector('.wp-full-overlay-footer .devices');
        if (footerDevices) {
            const buttons = footerDevices.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    let device = 'desktop';
                    if (button.classList.contains('preview-tablet')) {
                        device = 'tablet';
                    } else if (button.classList.contains('preview-mobile')) {
                        device = 'mobile';
                    }
                    window.digi.responsiveState.setDevice(device);
                });
            });
        }

        /**
         * Rich Text Control Component
         */
        const RichTextControl = ({ label, value, onChange }) => {
            return createElement('div', { className: 'digifusion-rich-text-control' },
                createElement('span', { className: 'customize-control-title' }, label),
                createElement(RichText, {
                    tagName: 'div',
                    className: 'digifusion-rich-text-editor',
                    value: value,
                    onChange: onChange,
                    placeholder: __('Enter content...', 'digifusion')
                })
            );
        };

        /**
         * Image Upload Control Component
         */
        const ImageUploadControl = ({ label, value, onChange }) => {
            return createElement('div', { className: 'digifusion-media-control' },
                createElement('span', { className: 'customize-control-title' }, label),
                createElement(MediaUploadCheck, {},
                    createElement(MediaUpload, {
                        onSelect: (media) => {
                            onChange(media.url);
                        },
                        allowedTypes: ['image'],
                        value: value,
                        render: ({ open }) => (
                            createElement('div', { className: 'digifusion-media-upload-wrapper' },
                                value ? 
                                    createElement('div', { className: 'digifusion-media-preview' },
                                        createElement('img', { src: value, alt: '' }),
                                        createElement('div', { className: 'digifusion-media-controls' },
                                            createElement(Button, {
                                                isPrimary: true,
                                                onClick: open
                                            },
                                                createElement('span', { className: 'dashicon dashicons dashicons-edit' })
                                            ),
                                            createElement(Button, {
                                                isDestructive: true,
                                                onClick: () => onChange('')
                                            },
                                                createElement('span', { className: 'dashicon dashicons dashicons-trash' })
                                            )
                                        )
                                    ) :
                                    createElement(Button, {
                                        className: 'digifusion-media-upload-button',
                                        isPrimary: true,
                                        onClick: open
                                    }, __('Select Image', 'digifusion'))
                            )
                        )
                    })
                )
            );
        };

        /**
         * Dimensions Control Component
         */
        const DimensionsControl = ({ label, value, onChange, isResponsive, units, allowNegative, min, max, step }) => {
            const [isLinked, setIsLinked] = useState(true);
            const [localActiveDevice, setLocalActiveDevice] = useState(window.digi.responsiveState.activeDevice);
            
            // Initialize default values if needed
            const defaultValue = {
                top: '',
                right: '',
                bottom: '',
                left: '',
                unit: 'px'
            };
            
            const devices = ['desktop', 'tablet', 'mobile'];
            const initialValues = {};
            
            devices.forEach(device => {
                initialValues[device] = { ...defaultValue };
            });
            
            // Parse the control value
            const [values, setValues] = useState(() => {
                try {
                    if (value && typeof value === 'object') {
                        return value;
                    } else if (typeof value === 'string' && value.trim() !== '') {
                        return JSON.parse(value);
                    }
                } catch (e) {
                    console.error('Error parsing dimensions value:', e);
                }
                return initialValues;
            });
            
            // Subscribe to global device state changes if component is responsive
            useEffect(() => {
                if (isResponsive) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setLocalActiveDevice(device);
                    });
                    
                    // Cleanup subscription on unmount
                    return unsubscribe;
                }
            }, [isResponsive]);
            
            // Handle value change
            const handleValueChange = (key, newValue) => {
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                if (isLinked) {
                    // When linked, update all values
                    newValues[localActiveDevice] = {
                        ...newValues[localActiveDevice],
                        top: newValue,
                        right: newValue,
                        bottom: newValue,
                        left: newValue,
                    };
                } else {
                    // When unlinked, update only the specific value
                    newValues[localActiveDevice][key] = newValue;
                }
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
            };
            
            // Handle unit change
            const handleUnitChange = (unit) => {
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                newValues[localActiveDevice].unit = unit;
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
            };
            
            // Reset values
            const resetValues = () => {
                let newValues = { ...values };
                
                newValues[localActiveDevice] = {
                    ...defaultValue,
                    unit: values[localActiveDevice]?.unit || 'px'
                };
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
            };
            
            // Get current device values
            const currentValues = values[localActiveDevice] || defaultValue;
            
            // Check if values are at default (all empty)
            const isDefault = 
                currentValues.top === '' &&
                currentValues.right === '' &&
                currentValues.bottom === '' &&
                currentValues.left === '';
            
            // Get max value based on unit
            const getMaxValue = (unit) => {
                switch (unit) {
                    case "px":
                        return 500;
                    case "rem":
                        return 30;
                    case "em":
                        return 30;
                    case "%":
                        return 100;
                    default:
                        return 100;
                }
            };
            
            // Get step based on unit
            const getStepValue = (unit) => {
                switch (unit) {
                    case "px":
                        return 1;
                    case "rem":
                        return 0.1;
                    case "em":
                        return 0.1;
                    case "%":
                        return 1;
                    default:
                        return 1;
                }
            };
            
            return createElement('div', { className: `digifusion-dimension-control ${isResponsive ? 'is-responsive' : ''}` },
                createElement('div', { className: 'digifusion-control__header' },
                    createElement('div', { className: 'digifusion-responsive-label-wrap' },
                        createElement('span', { className: 'digifusion-control-label' }, label),
                        isResponsive && createElement(Button, {
                            className: 'digifusion-responsive-common-button',
                            onClick: () => window.digi.responsiveState.toggleDevice(),
                            'aria-label': __(`Switch to ${window.digi.responsiveState.getNextDevice()} view`, 'digifusion')
                        }, window.digi.deviceIcons[localActiveDevice])
                    ),
                    createElement('div', { className: 'digifusion-control__actions' },
                        createElement('div', {},
                            createElement(Button, {
                                isSmall: true,
                                className: 'digifusion-reset',
                                icon: 'image-rotate',
                                onClick: resetValues,
                                disabled: isDefault,
                                'aria-label': __('Reset', 'digifusion')
                            })
                        ),
                        createElement(ToggleGroupControl, {
                            value: currentValues.unit || 'px',
                            onChange: handleUnitChange,
                            isSmall: true,
                            isBlock: true,
                            hideLabelFromVision: true,
                            'aria-label': __('Select Units', 'digifusion'),
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }, 
                            (units || [
                                { value: 'px', label: 'px' },
                                { value: 'rem', label: 'rem' },
                                { value: 'em', label: 'em' },
                                { value: '%', label: '%' }
                            ]).map(unit => 
                                createElement(ToggleGroupControlOption, {
                                    key: unit.value,
                                    value: unit.value,
                                    label: unit.label
                                })
                            )
                        )
                    )
                ),
                createElement('div', { className: 'digifusion-spacing-inputs' },
                    createElement('input', {
                        className: 'digifusion-spacing-input',
                        type: 'number',
                        value: currentValues.top === '' ? '' : currentValues.top,
                        onChange: (e) => {
                            const value = e.target.value === '' ? '' : parseFloat(e.target.value);
                            handleValueChange('top', value);
                        },
                        min: allowNegative ? -getMaxValue(currentValues.unit) : 0,
                        max: getMaxValue(currentValues.unit),
                        step: getStepValue(currentValues.unit),
                        'aria-label': __('Top', 'digifusion')
                    }),
                    createElement('input', {
                        className: 'digifusion-spacing-input',
                        type: 'number',
                        value: currentValues.right === '' ? '' : currentValues.right,
                        onChange: (e) => {
                            const value = e.target.value === '' ? '' : parseFloat(e.target.value);
                            handleValueChange('right', value);
                        },
                        min: allowNegative ? -getMaxValue(currentValues.unit) : 0,
                        max: getMaxValue(currentValues.unit),
                        step: getStepValue(currentValues.unit),
                        'aria-label': __('Right', 'digifusion')
                    }),
                    createElement('input', {
                        className: 'digifusion-spacing-input',
                        type: 'number',
                        value: currentValues.bottom === '' ? '' : currentValues.bottom,
                        onChange: (e) => {
                            const value = e.target.value === '' ? '' : parseFloat(e.target.value);
                            handleValueChange('bottom', value);
                        },
                        min: allowNegative ? -getMaxValue(currentValues.unit) : 0,
                        max: getMaxValue(currentValues.unit),
                        step: getStepValue(currentValues.unit),
                        'aria-label': __('Bottom', 'digifusion')
                    }),
                    createElement('input', {
                        className: 'digifusion-spacing-input',
                        type: 'number',
                        value: currentValues.left === '' ? '' : currentValues.left,
                        onChange: (e) => {
                            const value = e.target.value === '' ? '' : parseFloat(e.target.value);
                            handleValueChange('left', value);
                        },
                        min: allowNegative ? -getMaxValue(currentValues.unit) : 0,
                        max: getMaxValue(currentValues.unit),
                        step: getStepValue(currentValues.unit),
                        'aria-label': __('Left', 'digifusion')
                    }),
                    createElement('span', {
                        className: `digifusion-spacing-link ${
                            !isLinked ? "digifusion-spacing-control-disconnected" : ""
                        } dashicons ${
                            isLinked ? "dashicons-admin-links" : "dashicons-editor-unlink"
                        }`,
                        onClick: () => setIsLinked(!isLinked),
                        title: isLinked ? __('Unlink values', 'digifusion') : __('Link values', 'digifusion'),
                        role: 'button',
                        tabIndex: '0',
                        onKeyPress: (event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                setIsLinked(!isLinked);
                            }
                        }
                    })
                ),
                createElement('div', { className: 'digifusion-spacing-labels' },
                    createElement('span', { className: 'digifusion-spacing-label' }, __('Top', 'digifusion')),
                    createElement('span', { className: 'digifusion-spacing-label' }, __('Right', 'digifusion')),
                    createElement('span', { className: 'digifusion-spacing-label' }, __('Bottom', 'digifusion')),
                    createElement('span', { className: 'digifusion-spacing-label' }, __('Left', 'digifusion')),
                    createElement('span', { className: 'digifusion-spacing-label digifusion-spacing-link-label' })
                )
            );
        };

        /**
         * Range Control Component
         */
        const ResponsiveRangeControl = ({ label, value, onChange, isResponsive, units, defaultUnit, min, max, step, defaultValues }) => {
            const [localActiveDevice, setLocalActiveDevice] = useState(window.digi.responsiveState.activeDevice);
            const [inputValue, setInputValue] = useState('');
            
            // Initialize default values
            const defaultValue = {
                value: '',
                unit: defaultUnit || 'px'
            };
            
            const devices = ['desktop', 'tablet', 'mobile'];
            const initialValues = {};
            
            devices.forEach(device => {
                initialValues[device] = { ...defaultValue };
            });
            
            // Parse the control value
            const [values, setValues] = useState(() => {
                try {
                    if (value && typeof value === 'object') {
                        return value;
                    } else if (typeof value === 'string' && value.trim() !== '') {
                        return JSON.parse(value);
                    }
                } catch (e) {
                    console.error('Error parsing range value:', e);
                }
                return initialValues;
            });
            
            // Subscribe to global device state changes if component is responsive
            useEffect(() => {
                if (isResponsive) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setLocalActiveDevice(device);
                    });
                    
                    // Cleanup subscription on unmount
                    return unsubscribe;
                }
            }, [isResponsive]);
            
            // Update input value when device or values change
            useEffect(() => {
                if (values[localActiveDevice]) {
                    setInputValue(values[localActiveDevice].value === '' ? '' : String(values[localActiveDevice].value));
                }
            }, [localActiveDevice, values]);
            
            // Handle direct input changes
            const handleInputChange = (e) => {
                const newValue = e.target.value;
                
                // Update local input state
                setInputValue(newValue);
                
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                // If empty, update with empty string
                if (newValue === '') {
                    newValues[localActiveDevice].value = '';
                    setValues(newValues);
                    onChange(JSON.stringify(newValues));
                    return;
                }
                
                // For non-empty values, convert to number
                const numValue = parseFloat(newValue);
                if (!isNaN(numValue)) {
                    newValues[localActiveDevice].value = numValue;
                    setValues(newValues);
                    onChange(JSON.stringify(newValues));
                }
            };
            
            // Handle slider changes
            const handleSliderChange = (e) => {
                const newValue = parseFloat(e.target.value);
                
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                newValues[localActiveDevice].value = newValue;
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
                setInputValue(String(newValue));
            };
            
            // Handle unit change
            const handleUnitChange = (unit) => {
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                newValues[localActiveDevice].unit = unit;
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
            };
            
            // Reset to default value
            const resetValue = () => {
                let defaultVal = '';
                
                if (defaultValues) {
                    defaultVal = defaultValues[localActiveDevice] !== undefined ? 
                        defaultValues[localActiveDevice] : 
                        (defaultValues.default !== undefined ? defaultValues.default : '');
                }
                
                let newValues = { ...values };
                
                if (!newValues[localActiveDevice]) {
                    newValues[localActiveDevice] = { ...defaultValue };
                }
                
                newValues[localActiveDevice].value = defaultVal;
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
                setInputValue(defaultVal === '' ? '' : String(defaultVal));
            };
            
            // Determine if reset button should be disabled
            const isResetDisabled = () => {
                if (!defaultValues && (!values[localActiveDevice] || values[localActiveDevice].value === '')) return true;
                
                if (defaultValues) {
                    const defaultVal = defaultValues[localActiveDevice] !== undefined ? 
                        defaultValues[localActiveDevice] : 
                        (defaultValues.default !== undefined ? defaultValues.default : '');
                    
                    return values[localActiveDevice] && values[localActiveDevice].value === defaultVal;
                }
                
                return false;
            };
            
            // Get current device values
            const currentValues = values[localActiveDevice] || defaultValue;
            
            // Calculate percentage for slider positioning
            const getPercentage = () => {
                if (!currentValues || currentValues.value === '') return 0;
                
                const value = parseFloat(currentValues.value);
                return Math.max(0, Math.min(100, ((value - min) / (max - min)) * 100));
            };
            
            const percentage = getPercentage();
            
            return createElement('div', { className: 'digifusion-size-type-field-tabs' },
                createElement('div', { className: 'digifusion-responsive-control-inner' },
                    createElement('div', { className: 'components-base-control' },
                        createElement('div', { className: 'digifusion-range-control digifusion-size-type-field-tabs' },
                            createElement('div', { className: 'digifusion-control__header' },
                                createElement('div', { className: 'digifusion-responsive-label-wrap' },
                                    createElement('span', { className: 'digifusion-control-label' }, label),
                                    isResponsive && createElement(Button, {
                                        className: 'digifusion-responsive-common-button',
                                        onClick: () => window.digi.responsiveState.toggleDevice(),
                                        'aria-label': __(`Switch to ${window.digi.responsiveState.getNextDevice()} view`, 'digifusion')
                                    }, window.digi.deviceIcons[localActiveDevice])
                                ),
                                createElement('div', { className: 'digifusion-range-control__actions digifusion-control__actions' },
                                    createElement('div', { tabIndex: '0' },
                                        createElement(Button, {
                                            type: 'button',
                                            disabled: isResetDisabled(),
                                            className: 'components-button digifusion-reset is-secondary is-small',
                                            onClick: resetValue
                                        },
                                            createElement('span', { className: 'dashicon dashicons dashicons-image-rotate' })
                                        )
                                    ),
                                    (units && units.length > 1) && createElement(ToggleGroupControl, {
                                        value: currentValues.unit || defaultUnit || 'px',
                                        onChange: handleUnitChange,
                                        isBlock: true,
                                        isSmall: true,
                                        hideLabelFromVision: true,
                                        'aria-label': __('Select Units', 'digifusion'),
                                        __next40pxDefaultSize: true,
                                        __nextHasNoMarginBottom: true
                                    }, 
                                        (units || [
                                            { value: 'px', label: 'px' },
                                            { value: '%', label: '%' },
                                            { value: 'em', label: 'em' },
                                            { value: 'rem', label: 'rem' },
                                            { value: 'vh', label: 'vh' }
                                        ]).map(unit => 
                                            createElement(ToggleGroupControlOption, {
                                                key: unit.value,
                                                value: unit.value,
                                                label: unit.label
                                            })
                                        )
                                    )
                                )
                            ),
                            createElement('div', { className: 'digifusion-range-control__mobile-controls' },
                                createElement('div', { className: 'digifusion-custom-range-control' },
                                    createElement('div', { className: 'range-slider-wrapper' },
                                        createElement('input', {
                                            className: 'range-slider',
                                            max: max,
                                            min: min,
                                            step: step,
                                            type: 'range',
                                            value: currentValues.value === '' ? 0 : currentValues.value,
                                            onChange: handleSliderChange
                                        }),
                                        createElement('div', { className: 'range-track' },
                                            createElement('div', {
                                                className: 'range-track-fill',
                                                style: { width: `${percentage}%` }
                                            })
                                        ),
                                        createElement('div', {
                                            className: 'range-thumb',
                                            style: { left: `${percentage}%` }
                                        })
                                    ),
                                    createElement('div', { className: 'input-wrapper' },
                                        createElement('input', {
                                            className: 'number-input',
                                            type: 'number',
                                            value: inputValue,
                                            onChange: handleInputChange,
                                            min: min,
                                            max: max,
                                            step: step
                                        })
                                    )
                                )
                            )
                        )
                    )
                )
            );
        };

        /**
         * Box Shadow Control Component
         */
        const BoxShadowControl = ({ label, value, onChange }) => {
            // Set default values if not provided
            const defaultShadow = {
                enable: false,
                color: 'rgba(0, 0, 0, 0.2)',
                horizontal: 0,
                vertical: 0,
                blur: 0,
                spread: 0,
                position: 'outset'
            };
            
            // Parse the control value
            const [values, setValues] = useState(() => {
                try {
                    if (value && typeof value === 'object') {
                        return value;
                    } else if (typeof value === 'string' && value.trim() !== '') {
                        return JSON.parse(value);
                    }
                } catch (e) {
                    console.error('Error parsing box shadow value:', e);
                }
                return {
                    normal: { ...defaultShadow },
                    hover: { ...defaultShadow }
                };
            });
            
            // Helper function to handle shadow property changes
            const updateShadowProperty = (tab, property, value) => {
                const newValues = { ...values };
                
                if (!newValues[tab]) {
                    newValues[tab] = { ...defaultShadow };
                }
                
                newValues[tab][property] = value;
                
                setValues(newValues);
                onChange(JSON.stringify(newValues));
            };
            
            // Generate CSS value from shadow object
            const getShadowCSS = (shadow) => {
                if (!shadow.enable) return 'none';
                
                const inset = shadow.position === 'inset' ? 'inset ' : '';
                return `${inset}${shadow.horizontal}px ${shadow.vertical}px ${shadow.blur}px ${shadow.spread}px ${shadow.color}`;
            };
            
            // Tabs for the panel
            const tabs = [
                {
                    name: 'normal',
                    title: __('Normal', 'digifusion'),
                    className: 'digifusion-tab-1 normal'
                },
                {
                    name: 'hover',
                    title: __('Hover', 'digifusion'),
                    className: 'digifusion-tab-2 hover'
                }
            ];
            
            // Render shadow controls based on the active tab
            const renderShadowControls = (tab) => {
                const currentValue = values[tab] || defaultShadow;
                
                return createElement('div', { className: 'digifusion-box-shadow-controls' },
                    // Enable/Disable Toggle Button
                    createElement('div', { className: 'digifusion-toggle-wrapper', style: { marginBottom: '16px' } },
                        createElement(ToggleControl, {
                            label: __('Enable Box Shadow', 'digifusion'),
                            checked: currentValue.enable,
                            onChange: () => updateShadowProperty(tab, 'enable', !currentValue.enable),
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        })
                    ),
                    
                    currentValue.enable && [
                        // Color Picker
                        createElement('div', { className: 'digifusion-color-picker-wrapper', key: 'color' },
                            createElement('span', { className: 'customize-control-title' }, __('Color', 'digifusion')),
                            createElement(ColorPicker, {
                                color: currentValue.color,
                                onChange: (value) => updateShadowProperty(tab, 'color', value),
                                enableAlpha: true
                            })
                        ),
                        
                        // Horizontal Offset
                        createElement(RangeControl, {
                            key: 'horizontal',
                            label: __('Horizontal', 'digifusion'),
                            value: currentValue.horizontal,
                            onChange: (value) => updateShadowProperty(tab, 'horizontal', value),
                            min: -100,
                            max: 100,
                            step: 1,
                            allowReset: true,
                            resetFallbackValue: 0,
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        
                        // Vertical Offset
                        createElement(RangeControl, {
                            key: 'vertical',
                            label: __('Vertical', 'digifusion'),
                            value: currentValue.vertical,
                            onChange: (value) => updateShadowProperty(tab, 'vertical', value),
                            min: -100,
                            max: 100,
                            step: 1,
                            allowReset: true,
                            resetFallbackValue: 0,
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        
                        // Blur Radius
                        createElement(RangeControl, {
                            key: 'blur',
                            label: __('Blur', 'digifusion'),
                            value: currentValue.blur,
                            onChange: (value) => updateShadowProperty(tab, 'blur', value),
                            min: 0,
                            max: 100,
                            step: 1,
                            allowReset: true,
                            resetFallbackValue: 0,
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        
                        // Spread
                        createElement(RangeControl, {
                            key: 'spread',
                            label: __('Spread', 'digifusion'),
                            value: currentValue.spread,
                            onChange: (value) => updateShadowProperty(tab, 'spread', value),
                            min: -100,
                            max: 100,
                            step: 1,
                            allowReset: true,
                            resetFallbackValue: 0,
                            __next40pxDefaultSize: true,
                            __nextHasNoMarginBottom: true
                        }),
                        
                        // Position (Outset/Inset)
                        createElement('div', { className: 'digifusion-multi-buttons-control', key: 'position' },
                            createElement('div', { className: 'digifusion-multi-buttons-control__label' },
                                __('Position', 'digifusion')
                            ),
                            createElement(ToggleGroupControl, {
                                value: currentValue.position,
                                onChange: (value) => updateShadowProperty(tab, 'position', value),
                                isBlock: true,
                                __next40pxDefaultSize: true,
                                __nextHasNoMarginBottom: true
                            },
                                createElement(ToggleGroupControlOption, {
                                    value: 'outset',
                                    label: __('Outset', 'digifusion')
                                }),
                                createElement(ToggleGroupControlOption, {
                                    value: 'inset',
                                    label: __('Inset', 'digifusion')
                                })
                            )
                        )
                    ]
                );
            };
            
            return createElement('div', { className: 'digifusion-box-shadow-control' },
                createElement('span', { className: 'customize-control-title' }, label),
                createElement(TabPanel, {
                    className: 'digifusion-control-tabs',
                    activeClass: 'active-tab',
                    tabs: tabs
                }, (tab) => renderShadowControls(tab.name))
            );
        };

        /**
         * Color Picker Control Component
         */
        const ColorPickerControl = ({ label, value, onChange, alpha }) => {
            return createElement('div', { className: 'digifusion-color-picker-control' },
                createElement('span', { className: 'customize-control-title' }, label),
                createElement(ColorPicker, {
                    color: value,
                    onChange: onChange,
                    enableAlpha: alpha
                })
            );
        };

        // Render Rich Text Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-rich-text').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(RichTextControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    }
                }),
                document.getElementById(controlId)
            );
        });

        // Render Image Upload Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-image-upload').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(ImageUploadControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    }
                }),
                document.getElementById(controlId)
            );
        });

        // Render Dimensions Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-dimensions').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(DimensionsControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    },
                    isResponsive: controlData.is_responsive,
                    units: controlData.units,
                    allowNegative: controlData.allowNegative,
                    min: controlData.min,
                    max: controlData.max,
                    step: controlData.step
                }),
                document.getElementById(controlId)
            );
        });

        // Render Range Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-range').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(ResponsiveRangeControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    },
                    isResponsive: controlData.is_responsive,
                    units: controlData.units,
                    defaultUnit: controlData.default_unit,
                    min: controlData.min,
                    max: controlData.max,
                    step: controlData.step,
                    defaultValues: controlData.default_values
                }),
                document.getElementById(controlId)
            );
        });

        // Render Color Picker Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-color-picker').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(ColorPickerControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    },
                    alpha: controlData.alpha
                }),
                document.getElementById(controlId)
            );
        });

        // Render Box Shadow Controls
        document.querySelectorAll('.digifusion-control-wrapper.digifusion-box-shadow').forEach(container => {
            const controlId = container.querySelector('div[data-customize-setting-link]').id;
            const controlData = wp.customize.control(controlId).params;
            
            render(
                createElement(BoxShadowControl, {
                    label: controlData.label,
                    value: controlData.value,
                    onChange: (value) => {
                        wp.customize.control(controlId).setting.set(value);
                    }
                }),
                document.getElementById(controlId)
            );
        });
    });
})(wp, jQuery);
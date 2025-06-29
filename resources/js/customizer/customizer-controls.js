/**
 * DigiFusion Customizer Controls
 * 
 * Modern Gutenberg-based controls for the WordPress Customizer
 * 
 * @package DigiFusion
 */

// Import WordPress dependencies
const { createRoot } = wp.element;
const { __ } = wp.i18n;
const {
    Button,
    __experimentalToggleGroupControl: ToggleGroupControl,
    __experimentalToggleGroupControlOption: ToggleGroupControlOption,
    Panel,
    RangeControl,
    SelectControl,
    ToggleControl,
    ColorPicker,
    Popover,
    Icon
} = wp.components;
const { useState, useEffect } = wp.element;
const { editor } = wp;

/**
 * Global state manager for responsive device state
 * This allows synchronization across all responsive controls
 */
window.digi = window.digi || {};

// Global responsive state manager
window.digi.responsiveState = (() => {
    // Current active device
    let activeDevice = 'desktop';
    
    // Subscribers
    const subscribers = [];
    
    // Device icons
    window.digi.deviceIcons = {
		desktop: (
			<svg width="8" height="7" viewBox="0 0 8 7" xmlns="http://www.w3.org/2000/svg">
				<path d="M7.33333 0H0.666667C0.298611 0 0 0.293945 0 0.65625V5.03125C0 5.39355 0.298611 5.6875 0.666667 5.6875H3.33333L3.11111 6.34375H2.11111C1.92639 6.34375 1.77778 6.49004 1.77778 6.67188C1.77778 6.85371 1.92639 7 2.11111 7H5.88889C6.07361 7 6.22222 6.85371 6.22222 6.67188C6.22222 6.49004 6.07361 6.34375 5.88889 6.34375H4.88889L4.66667 5.6875H7.33333C7.70139 5.6875 8 5.39355 8 5.03125V0.65625C8 0.293945 7.70139 0 7.33333 0ZM7.11111 4.8125H0.888889V0.875H7.11111V4.8125Z"></path>
			</svg>
		),
		tablet: (
			<svg width="6" height="8" viewBox="0 0 6 8" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 0H1C0.447715 0 0 0.447715 0 1V7C0 7.55228 0.447715 8 1 8H5C5.55228 8 6 7.55228 6 7V1C6 0.447715 5.55228 0 5 0ZM5 7H1V1H5V7Z"></path>
			</svg>
		),
		mobile: (
			<svg width="4" height="8" viewBox="0 0 4 8" xmlns="http://www.w3.org/2000/svg">
				<path d="M3.33333 0H0.666667C0.297995 0 0 0.298 0 0.666667V7.33333C0 7.702 0.297995 8 0.666667 8H3.33333C3.70201 8 4 7.702 4 7.33333V0.666667C4 0.298 3.70201 0 3.33333 0ZM2 7.33333C1.63201 7.33333 1.33333 7.03467 1.33333 6.66667C1.33333 6.29867 1.63201 6 2 6C2.36799 6 2.66667 6.29867 2.66667 6.66667C2.66667 7.03467 2.36799 7.33333 2 7.33333ZM3.33333 5.33333H0.666667V1.33333H3.33333V5.33333Z"></path>
			</svg>
		)
	};
    
    // Initial device setup
    document.body.setAttribute('data-digiblocks-device', activeDevice);
    
    /**
     * Subscribe to device changes
     * @param {Function} callback Callback to run when device changes
     * @returns {Function} Unsubscribe function
     */
    const subscribe = (callback) => {
        subscribers.push(callback);
        
        // Immediately call with current device
        callback(activeDevice);
        
        // Return unsubscribe function
        return () => {
            const index = subscribers.indexOf(callback);
            if (index !== -1) {
                subscribers.splice(index, 1);
            }
        };
    };
    
    /**
     * Set the active device
     * @param {string} device The device to set active
     */
    const setDevice = (device) => {
        if (['desktop', 'tablet', 'mobile'].includes(device) && device !== activeDevice) {
            activeDevice = device;
            
            // Update body attribute
            document.body.setAttribute('data-digiblocks-device', device);
            
            // Trigger WordPress customizer preview device
            triggerCustomizerDevicePreview(device);
            
            // Notify subscribers
            subscribers.forEach(callback => callback(device));
        }
    };
    
    /**
     * Toggle to the next device in sequence
     */
    const toggleDevice = () => {
        const nextDevice = getNextDevice();
        setDevice(nextDevice);
    };
    
    /**
     * Get the next device in the rotation
     * @returns {string} Next device
     */
    const getNextDevice = () => {
        switch (activeDevice) {
            case 'desktop': return 'tablet';
            case 'tablet': return 'mobile';
            case 'mobile': return 'desktop';
            default: return 'desktop';
        }
    };
    
    /**
     * Trigger WordPress customizer preview device
     * @param {string} device The device to preview
     */
    const triggerCustomizerDevicePreview = (device) => {
        // Find WordPress device buttons
        const wpDeviceButtons = {
            desktop: document.querySelector('.preview-desktop'),
            tablet: document.querySelector('.preview-tablet'),
            mobile: document.querySelector('.preview-mobile')
        };
        
        // Click the corresponding WordPress device button
        if (wpDeviceButtons[device] && typeof wpDeviceButtons[device].click === 'function') {
            wpDeviceButtons[device].click();
        }
    };
    
    // Initialize: Listen to WordPress device buttons
    const initDeviceListeners = () => {
        const wpDeviceButtons = {
            desktop: document.querySelector('.preview-desktop'),
            tablet: document.querySelector('.preview-tablet'),
            mobile: document.querySelector('.preview-mobile')
        };
        
        // Add listeners to WordPress device buttons
        Object.keys(wpDeviceButtons).forEach(device => {
            const button = wpDeviceButtons[device];
            if (button) {
                button.addEventListener('click', () => {
                    setDevice(device);
                });
            }
        });
    };
    
    // Initialize device listeners after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeviceListeners);
    } else {
        initDeviceListeners();
    }
    
    // Public API
    return {
        subscribe,
        setDevice,
        toggleDevice,
        getNextDevice,
        get activeDevice() {
            return activeDevice;
        }
    };
})();

/**
 * Global Colors Synchronization Manager
 */
window.digi.globalColorSync = (() => {
    let isUpdating = false;
    
    /**
     * Update dependent colors when global colors change
     * Only updates colors that match the old global color exactly
     */
    const updateDependentColors = (globalColorKey, oldColor, newColor) => {
        if (isUpdating || !window.digifusionCustomizer) return;
        
        isUpdating = true;
        
        const colorSettings = window.digifusionCustomizer.colorSettings || [];
        const updatedSettings = {};
        
        // Skip global colors setting to avoid infinite loop
        const filteredSettings = colorSettings.filter(setting => setting !== 'digifusion_global_colors');
        
        filteredSettings.forEach(settingId => {
            const control = wp.customize.control(settingId);
            if (!control) return;
            
            try {
                const currentValue = control.setting.get();
                const colors = JSON.parse(currentValue || '{}');
                let hasChanges = false;
                
                // Check each color in this setting
                Object.keys(colors).forEach(colorKey => {
                    // Only update if the color exactly matches the old global color
                    if (colors[colorKey] && colors[colorKey].toLowerCase() === oldColor.toLowerCase()) {
                        colors[colorKey] = newColor;
                        hasChanges = true;
                    }
                });
                
                // Update the setting if changes were made
                if (hasChanges) {
                    const newValue = JSON.stringify(colors);
                    control.setting.set(newValue);
                    updatedSettings[settingId] = newValue;
                    
                    // Trigger a change event to update the control UI
                    const event = new Event('change');
                    const input = document.getElementById(`_customize-input-${settingId}`);
                    if (input) {
                        input.value = newValue;
                        input.dispatchEvent(event);
                    }
                }
            } catch (e) {
                console.warn('Error updating dependent color for setting:', settingId, e);
            }
        });
        
        isUpdating = false;
        
        return updatedSettings;
    };

    /**
     * Check if a color is a custom color (doesn't match any current global color)
     */
    const isCustomColor = (colorValue) => {
        if (!window.digifusionCustomizer || !window.digifusionCustomizer.globalColors) return true;
        
        const globalColors = Object.values(window.digifusionCustomizer.globalColors);
        return !globalColors.some(globalColor => 
            globalColor.toLowerCase() === colorValue.toLowerCase()
        );
    };

    /**
     * Reset all dependent colors that use a specific global color
     * Preserves custom colors that don't match any global color
     */
    const resetDependentColors = (globalColorKey, oldColor, newDefaultColor) => {
        if (isUpdating || !window.digifusionCustomizer) return;
        
        const colorSettings = window.digifusionCustomizer.colorSettings || [];
        const filteredSettings = colorSettings.filter(setting => setting !== 'digifusion_global_colors');
        
        filteredSettings.forEach(settingId => {
            const control = wp.customize.control(settingId);
            if (!control) return;
            
            try {
                const currentValue = control.setting.get();
                const colors = JSON.parse(currentValue || '{}');
                let hasChanges = false;
                
                Object.keys(colors).forEach(colorKey => {
                    const currentColor = colors[colorKey];
                    
                    // Only reset if the color matches the old global color exactly
                    // This preserves custom colors
                    if (currentColor && currentColor.toLowerCase() === oldColor.toLowerCase()) {
                        colors[colorKey] = newDefaultColor;
                        hasChanges = true;
                    }
                });
                
                if (hasChanges) {
                    const newValue = JSON.stringify(colors);
                    control.setting.set(newValue);
                    
                    // Update the control UI
                    const input = document.getElementById(`_customize-input-${settingId}`);
                    if (input) {
                        input.value = newValue;
                        const event = new Event('change');
                        input.dispatchEvent(event);
                    }
                }
            } catch (e) {
                console.warn('Error resetting dependent colors for setting:', settingId, e);
            }
        });
    };
    
    /**
     * Initialize global color sync
     */
    const init = () => {
        // Listen for global colors changes
        if (wp.customize.control('digifusion_global_colors')) {
            wp.customize.control('digifusion_global_colors').setting.bind((newValue) => {
                if (isUpdating) return;
                
                try {
                    const newColors = JSON.parse(newValue || '{}');
                    const globalColors = window.digifusionCustomizer.globalColors || {};
                    
                    // Find which color changed
                    Object.keys(newColors).forEach(colorKey => {
                        const oldColor = globalColors[colorKey];
                        const newColor = newColors[colorKey];
                        
                        if (oldColor && newColor && oldColor !== newColor) {
                            // Update dependent colors
                            updateDependentColors(colorKey, oldColor, newColor);
                            
                            // Update our reference
                            globalColors[colorKey] = newColor;
                        }
                    });
                } catch (e) {
                    console.warn('Error processing global colors change:', e);
                }
            });
        }
    };
    
    return {
        init,
        updateDependentColors,
        resetDependentColors,
        isCustomColor
    };
})();

/**
 * Initialize all controls when the Customizer loads
 */
wp.customize.bind('ready', function() {
    // Initialize Global Color Sync
    window.digi.globalColorSync.init();
    
    // Initialize Range Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-range') {
            new DigiFusionRangeControl(control);
        }
    });

    // Initialize Color Picker Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-color-picker') {
            new DigiFusionColorPickerControl(control);
        }
    });

    // Initialize Toggle Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-toggle') {
            new DigiFusionToggleControl(control);
        }
    });

    // Initialize Select Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-select') {
            new DigiFusionSelectControl(control);
        }
    });

	// Initialize Typography Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-typography') {
            new DigiFusionTypographyControl(control);
        }
    });
});

/**
 * Color Picker Control
 */
class DigiFusionColorPickerControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-color-picker-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.alpha = this.container.dataset.alpha === 'true';
        this.colors = JSON.parse(this.container.dataset.colors || '[]');
        
        this.render();
    }
    
    render() {
        const ColorPickerComponent = () => {
            const isMultiColor = this.colors && this.colors.length > 0;
            const isGlobalColors = this.control.id === 'digifusion_global_colors';
            
            if (isMultiColor) {
                const [values, setValues] = useState(() => {
                    try {
                        return JSON.parse(this.input.value) || {};
                    } catch (e) {
                        const defaults = {};
                        this.colors.forEach(color => {
                            defaults[color.key] = color.default || '#000000';
                        });
                        return defaults;
                    }
                });
                
                const [openPicker, setOpenPicker] = useState(null);
                
                // Listen for external changes (from global color sync)
                useEffect(() => {
                    const handleInputChange = () => {
                        try {
                            const newValues = JSON.parse(this.input.value || '{}');
                            setValues(newValues);
                        } catch (e) {
                            // Ignore invalid JSON
                        }
                    };
                    
                    this.input.addEventListener('change', handleInputChange);
                    return () => this.input.removeEventListener('change', handleInputChange);
                }, []);
                
                // Update value and trigger change
                const updateValue = (colorKey, newColor) => {
                    let colorValue;
                    
                    if (newColor.rgb && this.alpha) {
                        const { r, g, b, a } = newColor.rgb;
                        colorValue = `rgba(${r}, ${g}, ${b}, ${a})`;
                    } else if (newColor.hex) {
                        colorValue = newColor.hex;
                    } else if (typeof newColor === 'string') {
                        colorValue = newColor;
                    } else {
                        return;
                    }
                    
                    const newValues = { ...values, [colorKey]: colorValue };
                    setValues(newValues);
                    
                    this.input.value = JSON.stringify(newValues);
                    const event = new Event('change');
                    this.input.dispatchEvent(event);
                };

                /**
                 * Enhanced reset function for global colors
                 */
                const resetGlobalColorWithCascade = (colorKey, oldColor, defaultColor) => {
                    if (!window.digifusionCustomizer || !window.digifusionCustomizer.colorSettings) {
                        return;
                    }

                    const colorSettings = window.digifusionCustomizer.colorSettings.filter(
                        setting => setting !== 'digifusion_global_colors'
                    );

                    // Update all dependent colors that are using the old global color
                    colorSettings.forEach(settingId => {
                        const control = wp.customize.control(settingId);
                        if (!control) return;

                        try {
                            const currentValue = control.setting.get();
                            const colors = JSON.parse(currentValue || '{}');
                            let hasChanges = false;

                            // Check each color in this setting
                            Object.keys(colors).forEach(key => {
                                if (colors[key] && colors[key].toLowerCase() === oldColor.toLowerCase()) {
                                    colors[key] = defaultColor;
                                    hasChanges = true;
                                }
                            });

                            // Update the setting if changes were made
                            if (hasChanges) {
                                const newValue = JSON.stringify(colors);
                                control.setting.set(newValue);
                                
                                // Update the control UI
                                const input = document.getElementById(`_customize-input-${settingId}`);
                                if (input) {
                                    input.value = newValue;
                                    const event = new Event('change');
                                    input.dispatchEvent(event);
                                }
                            }
                        } catch (e) {
                            console.warn('Error updating dependent color for setting:', settingId, e);
                        }
                    });
                };
                
                return (
                    <div className="digifusion-color-picker-wrap">
                        <label className="digifusion-color-label customize-control-title">{this.control.params.label}</label>
                        
                        <div className="digifusion-color-list">
                            {this.colors.map((colorConfig, index) => {
                                const currentColor = values[colorConfig.key] || colorConfig.default || '#000000';
                                const isOpen = openPicker === colorConfig.key;
                                const isDefault = currentColor.toLowerCase() === (colorConfig.default || '#000000').toLowerCase();
                                
                                // Enhanced reset function
                                const resetColor = () => {
                                    const oldColor = currentColor;
                                    const defaultColor = colorConfig.default || '#000000';
                                    
                                    // Update the global color first
                                    const newValues = { ...values, [colorConfig.key]: defaultColor };
                                    setValues(newValues);
                                    
                                    this.input.value = JSON.stringify(newValues);
                                    const event = new Event('change');
                                    this.input.dispatchEvent(event);
                                    
                                    // If this is a global color control, cascade the reset
                                    if (isGlobalColors) {
                                        // Small delay to ensure the global color is updated first
                                        setTimeout(() => {
                                            resetGlobalColorWithCascade(colorConfig.key, oldColor, defaultColor);
                                        }, 50);
                                    }
                                };
                                
                                return (
                                    <div key={colorConfig.key} className="digifusion-color-item">
                                        <div className="digifusion-color-item-inner">
                                            <Button
                                                className="digifusion-color-button"
                                                onClick={() => setOpenPicker(isOpen ? null : colorConfig.key)}
                                                aria-expanded={isOpen}
                                                aria-label={`${colorConfig.label} color picker`}
                                            >
                                                <div className="digifusion-color-indicator-wrapper">
                                                    <span 
                                                        className="digifusion-color-indicator"
                                                        style={{ backgroundColor: currentColor }}
                                                    ></span>
                                                    <span className="digifusion-color-name">
                                                        {colorConfig.label}
                                                    </span>
                                                </div>
                                            </Button>
                                            
                                            <Button
                                                className="digifusion-color-reset"
                                                icon="image-rotate"
                                                onClick={resetColor}
                                                disabled={isDefault}
                                                isSmall
                                                aria-label={__(`Reset ${colorConfig.label} to default`, "digifusion")}
                                                title={__(`Reset to default (${colorConfig.default})${isGlobalColors ? ' - will update related colors' : ''}`, "digifusion")}
                                            />
                                        </div>
                                        
                                        {isOpen && (
                                            <Popover
                                                position="bottom"
                                                onClose={() => setOpenPicker(null)}
                                            >
                                                <div className="digifusion-color-picker-popover">
                                                    <ColorPicker
                                                        color={currentColor}
                                                        onChangeComplete={(color) => updateValue(colorConfig.key, color)}
                                                        disableAlpha={!this.alpha}
                                                    />
                                                </div>
                                            </Popover>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                );
            } else {
                // Single color picker logic (unchanged)
                const [color, setColor] = useState(this.input.value || '#000000');
                const [isOpen, setIsOpen] = useState(false);
                
                const defaultColor = this.control.params.default || '#000000';
                const isDefault = color.toLowerCase() === defaultColor.toLowerCase();
                
                const updateColor = (newColor) => {
                    let colorValue;
                    
                    if (newColor.rgb && this.alpha) {
                        const { r, g, b, a } = newColor.rgb;
                        colorValue = `rgba(${r}, ${g}, ${b}, ${a})`;
                    } else if (newColor.hex) {
                        colorValue = newColor.hex;
                    } else if (typeof newColor === 'string') {
                        colorValue = newColor;
                    } else {
                        return;
                    }
                    
                    setColor(colorValue);
                    this.input.value = colorValue;
                    
                    const event = new Event('change');
                    this.input.dispatchEvent(event);
                };
                
                const resetColor = () => {
                    setColor(defaultColor);
                    this.input.value = defaultColor;
                    
                    const event = new Event('change');
                    this.input.dispatchEvent(event);
                };
                
                return (
                    <div className="digifusion-color-picker-wrap">
                        <div className="digifusion-control-header-row">
                            <label className="digifusion-color-label customize-control-title">{this.control.params.label}</label>
                            
                            <div className="digifusion-single-color-controls">
                                <Button
                                    className="digifusion-color-preview"
                                    style={{ backgroundColor: color }}
                                    onClick={() => setIsOpen(!isOpen)}
                                    aria-label={__('Select color', 'digifusion')}
                                >
                                    <span className="screen-reader-text">{__('Select color', 'digifusion')}</span>
                                </Button>
                                
                                <Button
                                    className="digifusion-color-reset"
                                    icon="image-rotate"
                                    onClick={resetColor}
                                    disabled={isDefault}
                                    isSmall
                                    aria-label={__('Reset to default', "digifusion")}
                                    title={__(`Reset to default (${defaultColor})`, "digifusion")}
                                />
                            </div>
                        </div>
                        
                        {isOpen && (
                            <Popover
                                position="bottom"
                                onClose={() => setIsOpen(false)}
                            >
                                <div className="digifusion-color-picker-popover">
                                    <ColorPicker
                                        color={color}
                                        onChangeComplete={updateColor}
                                        disableAlpha={!this.alpha}
                                    />
                                </div>
                            </Popover>
                        )}
                    </div>
                );
            }
        };
        
        const root = createRoot(this.container);
        root.render(<ColorPickerComponent />);
    }
}

/**
 * Toggle Control
 */
class DigiFusionToggleControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-toggle-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.render();
    }
    
    render() {
        const ToggleComponent = () => {
            // Parse the value as boolean
            const parseValue = (val) => {
                if (val === 'true' || val === true) return true;
                if (val === 'false' || val === false) return false;
                return !!val;
            };
            
            const [isChecked, setIsChecked] = useState(parseValue(this.input.value));
            
            const handleToggleChange = (value) => {
                setIsChecked(value);
                
                // Update the input value
                this.input.value = value.toString();
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            return (
                <div className="digifusion-toggle-switch-wrap">
                    <ToggleControl
                        label={this.control.params.label}
                        checked={isChecked}
                        onChange={handleToggleChange}
                        __nextHasNoMarginBottom={true}
                    />
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<ToggleComponent />);
    }
}

/**
 * Select Control
 */
class DigiFusionSelectControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-select-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.choices = JSON.parse(this.container.dataset.choices || '[]');
        
        this.render();
    }
    
    render() {
        const SelectControlComponent = () => {
            const [value, setValue] = useState(this.input.value || '');
            
            const handleChange = (newValue) => {
                setValue(newValue);
                
                // Update the input value
                this.input.value = newValue;
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            // Format choices for SelectControl
            const options = this.choices.map(choice => ({
                value: choice.value,
                label: choice.label
            }));
            
            return (
                <div className="digifusion-select-control-inner">
                    <SelectControl
                        value={value}
                        options={options}
                        onChange={handleChange}
                        __next40pxDefaultSize={true}
                        __nextHasNoMarginBottom={true}
                    />
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<SelectControlComponent />);
    }
}

/**
 * Typography Control
 */
class DigiFusionTypographyControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-typography-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            console.error('Typography control container or input not found for:', control.id);
            return;
        }
        
        // Get defaults from the data attribute
        try {
            this.defaults = JSON.parse(this.container.dataset.defaults || '{}');
        } catch (e) {
            console.error('Failed to parse default values from data attribute:', e);
            this.defaults = {};
        }
        
        this.render();
    }
    
    render() {
        const TypographyComponent = () => {
            // State for panel toggle
            const [isOpen, setIsOpen] = useState(false);
            
            // Parse stored values
            const [values, setValues] = useState(() => {
                try {
                    const parsed = JSON.parse(this.input.value);
                    return { ...this.defaults, ...parsed };
                } catch (e) {
                    return this.defaults;
                }
            });
            
            // Use global responsive state
            const [activeDevice, setActiveDevice] = useState('desktop');
            
            // Subscribe to global device state changes
            useEffect(() => {
                if (window.digi?.responsiveState?.subscribe) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setActiveDevice(device);
                    });
                    return unsubscribe;
                }
            }, []);
            
            // Font family options (dynamic with Google Fonts)
            const [fontFamilyOptions, setFontFamilyOptions] = useState([
                { label: __('Default', 'digifusion'), value: '' },
                { label: __('System UI', 'digifusion'), value: 'system-ui' },
                { label: __('Arial', 'digifusion'), value: 'Arial, sans-serif' },
                { label: __('Helvetica', 'digifusion'), value: 'Helvetica, sans-serif' },
                { label: __('Times New Roman', 'digifusion'), value: 'Times New Roman, serif' },
                { label: __('Georgia', 'digifusion'), value: 'Georgia, serif' },
                { label: __('Courier New', 'digifusion'), value: 'Courier New, monospace' },
            ]);
            
            // Font weight options (dynamic based on selected font)
            const [fontWeightOptions, setFontWeightOptions] = useState([
                { label: __('Default', 'digifusion'), value: '' },
                { label: '100 - Thin', value: '100' },
                { label: '200 - Extra Light', value: '200' },
                { label: '300 - Light', value: '300' },
                { label: '400 - Normal', value: '400' },
                { label: '500 - Medium', value: '500' },
                { label: '600 - Semi Bold', value: '600' },
                { label: '700 - Bold', value: '700' },
                { label: '800 - Extra Bold', value: '800' },
                { label: '900 - Black', value: '900' },
            ]);
            
            // Load Google Fonts on component mount
            useEffect(() => {
                // Get Google Fonts data from the global object
                const googleFonts = window.digifusionGoogleFonts || {};
                
                if (Object.keys(googleFonts).length > 0) {
                    // System fonts
                    const systemFonts = [
                        { label: __('Default', 'digifusion'), value: '' },
                        { label: __('System UI', 'digifusion'), value: 'system-ui' },
                        { label: __('Arial', 'digifusion'), value: 'Arial, sans-serif' },
                        { label: __('Helvetica', 'digifusion'), value: 'Helvetica, sans-serif' },
                        { label: __('Times New Roman', 'digifusion'), value: 'Times New Roman, serif' },
                        { label: __('Georgia', 'digifusion'), value: 'Georgia, serif' },
                        { label: __('Courier New', 'digifusion'), value: 'Courier New, monospace' },
                    ];
                    
                    // Google Fonts
                    const googleFontsList = Object.keys(googleFonts).map(fontName => ({
                        label: fontName,
                        value: fontName
                    }));
                    
                    // Combine system fonts and Google Fonts
                    setFontFamilyOptions([...systemFonts, ...googleFontsList]);
                }
            }, []);
            
            // Update font weights when font family changes - Enhanced version
            useEffect(() => {
                const selectedFont = values.fontFamily;
                
                // If no font selected or system font, use default weights
                if (!selectedFont || selectedFont === '' || selectedFont.includes(',') || selectedFont === 'system-ui') {
                    setFontWeightOptions([
                        { label: __('Default', 'digifusion'), value: '' },
                        { label: '100 - Thin', value: '100' },
                        { label: '200 - Extra Light', value: '200' },
                        { label: '300 - Light', value: '300' },
                        { label: '400 - Normal', value: '400' },
                        { label: '500 - Medium', value: '500' },
                        { label: '600 - Semi Bold', value: '600' },
                        { label: '700 - Bold', value: '700' },
                        { label: '800 - Extra Bold', value: '800' },
                        { label: '900 - Black', value: '900' },
                    ]);
                    return;
                }
                
                // Get available weights for Google Font
                const googleFonts = window.digifusionGoogleFonts || {};
                const fontData = googleFonts[selectedFont];
                
                if (fontData && (fontData.v || fontData.weight)) {
                    // Parse weights from v (variants) or weight array
                    const weights = new Set(['']); // Always include default
                    const weightLabels = {
                        '100': '100 - Thin',
                        '200': '200 - Extra Light', 
                        '300': '300 - Light',
                        '400': '400 - Normal',
                        '500': '500 - Medium',
                        '600': '600 - Semi Bold',
                        '700': '700 - Bold',
                        '800': '800 - Extra Bold',
                        '900': '900 - Black'
                    };
                    
                    // Use weight array first if available, otherwise use v (variants)
                    const variantsToProcess = fontData.weight || fontData.v || [];
                    
                    variantsToProcess.forEach(variant => {
                        // Handle different data formats
                        let weight = variant;
                        
                        // Skip 'Default' from weight array
                        if (weight === 'Default') {
                            return;
                        }
                        
                        // If variant is a string, extract weight
                        if (typeof variant === 'string') {
                            weight = variant.replace(/italic/gi, '').replace(/i$/i, '').trim();
                        }
                        
                        // Handle specific cases
                        if (weight === 'regular' || weight === 'normal') {
                            weights.add('400');
                        } else if (weight === 'bold') {
                            weights.add('700');
                        } else if (/^\d+$/.test(weight)) {
                            weights.add(weight);
                        }
                    });
                    
                    const weightOptions = Array.from(weights).sort((a, b) => {
                        if (a === '') return -1;
                        if (b === '') return 1;
                        return parseInt(a) - parseInt(b);
                    }).map(weight => ({
                        label: weight === '' ? __('Default', 'digifusion') : (weightLabels[weight] || weight),
                        value: weight
                    }));
                    
                    setFontWeightOptions(weightOptions);
                    
                    // Reset font weight to default if current weight is not available
                    const currentWeight = values.fontWeight;
                    const availableWeights = Array.from(weights);
                    
                    if (currentWeight && !availableWeights.includes(currentWeight)) {
                        // Reset to default since current weight is not available
                        const resetValues = {
                            ...values,
                            fontWeight: ''
                        };
                        setValues(resetValues);
                        this.input.value = JSON.stringify(resetValues);
                        
                        const event = new Event('change');
                        this.input.dispatchEvent(event);
                    }
                } else {
                    // Fallback to common weights for fonts without variant data
                    setFontWeightOptions([
                        { label: __('Default', 'digifusion'), value: '' },
                        { label: '400 - Normal', value: '400' },
                        { label: '700 - Bold', value: '700' },
                    ]);
                    
                    // Reset font weight if not available in fallback options
                    const currentWeight = values.fontWeight;
                    if (currentWeight && !['', '400', '700'].includes(currentWeight)) {
                        const resetValues = {
                            ...values,
                            fontWeight: ''
                        };
                        setValues(resetValues);
                        this.input.value = JSON.stringify(resetValues);
                        
                        const event = new Event('change');
                        this.input.dispatchEvent(event);
                    }
                }
            }, [values.fontFamily]); // Important: Only trigger when fontFamily changes
            
            // Load currently selected font on mount
            useEffect(() => {
                if (values.fontFamily && !values.fontFamily.includes(',') && values.fontFamily !== 'system-ui') {
                    loadGoogleFont(values.fontFamily, values.fontWeight || '400');
                }
            }, []);
            
            // Update handler for all typography settings
            const updateTypographyValue = (property, newValue) => {
                let updatedValues = {
                    ...values,
                    [property]: newValue
                };
                
                // Special handling for font family changes
                if (property === 'fontFamily') {
                    // Reset font weight to default when font family changes
                    updatedValues = {
                        ...updatedValues,
                        fontWeight: '' // Reset to default
                    };
                    
                    // Load Google Font when font family changes
                    if (newValue && !newValue.includes(',') && newValue !== 'system-ui') {
                        loadGoogleFont(newValue, '400'); // Use 400 as default weight
                    }
                }
                
                setValues(updatedValues);
                this.input.value = JSON.stringify(updatedValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            // Enhanced Load Google Font helper function - Using API v1 for compatibility
            const loadGoogleFont = (fontFamily, fontWeight = '400') => {
                if (!fontFamily || fontFamily.includes(',') || fontFamily === 'system-ui') {
                    return;
                }
                
                // Check if font is already loaded
                const existingLink = document.querySelector(`link[data-font-family="${fontFamily}"]`);
                if (existingLink) {
                    return;
                }
                
                // Get font variants from Google Fonts data
                const googleFonts = window.digifusionGoogleFonts || {};
                const fontData = googleFonts[fontFamily];
                
                // Use API v1 format for better compatibility
                const urlFontFamily = fontFamily.replace(/ /g, '+');
                let fontUrl = `https://fonts.googleapis.com/css?family=${urlFontFamily}`;
                
                // Add weights if available
                if (fontData && fontData.variants && fontData.variants.length > 0) {
                    // Extract all numeric weights and include 400 as default
                    const weights = new Set(['400']);
                    
                    fontData.variants.forEach(variant => {
                        const weight = variant.replace('italic', '').trim();
                        if (weight && /^\d+$/.test(weight)) {
                            weights.add(weight);
                        } else if (weight === 'regular') {
                            weights.add('400');
                        }
                    });
                    
                    const weightList = Array.from(weights).sort((a, b) => parseInt(a) - parseInt(b)).join(',');
                    fontUrl += `:${weightList}`;
                } else {
                    // Fallback to common weights
                    fontUrl += ':400,700';
                }
                
                fontUrl += '&display=swap';
                
                // Create and append link element
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = fontUrl;
                link.setAttribute('data-font-family', fontFamily);
                document.head.appendChild(link);
            };
            
            // Font size units
            const fontSizeUnits = [
                { label: 'px', value: 'px' },
                { label: 'em', value: 'em' },
                { label: 'rem', value: 'rem' },
                { label: 'vw', value: 'vw' },
            ];
            
            // Line height units
            const lineHeightUnits = [
                { label: 'px', value: 'px' },
                { label: 'em', value: 'em' },
            ];
            
            // Letter spacing units
            const letterSpacingUnits = [
                { label: 'px', value: 'px' },
                { label: 'em', value: 'em' },
            ];
            
            // Font style options
            const fontStyleOptions = [
                { label: __('Normal', 'digifusion'), value: 'normal' },
                { label: __('Italic', 'digifusion'), value: 'italic' },
                { label: __('Oblique', 'digifusion'), value: 'oblique' },
            ];
            
            // Text transform options
            const textTransformOptions = [
                { label: __('Default', 'digifusion'), value: '' },
                { label: __('None', 'digifusion'), value: 'none' },
                { label: __('Capitalize', 'digifusion'), value: 'capitalize' },
                { label: __('Uppercase', 'digifusion'), value: 'uppercase' },
                { label: __('Lowercase', 'digifusion'), value: 'lowercase' },
            ];
            
            // Text decoration options
            const textDecorationOptions = [
                { label: __('Default', 'digifusion'), value: '' },
                { label: __('None', 'digifusion'), value: 'none' },
                { label: __('Underline', 'digifusion'), value: 'underline' },
                { label: __('Overline', 'digifusion'), value: 'overline' },
                { label: __('Line Through', 'digifusion'), value: 'line-through' },
            ];
            
            // Update responsive value (fontSize, lineHeight, letterSpacing)
            const updateResponsiveValue = (property, device, newValue) => {
                const updatedValues = {
                    ...values,
                    [property]: {
                        ...values[property],
                        [device]: newValue
                    }
                };
                
                setValues(updatedValues);
                this.input.value = JSON.stringify(updatedValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            return (
                <div className="digifusion-typography-control">
                    <div className="digifusion-control-header-wrap">
                        <div className="digifusion-control-left">
                            <label className="digifusion-control-title customize-control-title">
                                {this.control.params.label}
                            </label>
                        </div>
                        <div className="digifusion-control-right">
                            <Button
                                isSmall
                                icon="edit"
                                onClick={() => setIsOpen(!isOpen)}
                                aria-label={__("Edit Typography", "digifusion")}
                                className={`digifusion-edit ${isOpen ? 'is-pressed' : ''}`}
                            />
                        </div>
                    </div>
                    
                    {isOpen && (
                        <div className="digifusion-typography-options">
                            {/* Font Family */}
                            <div className="digifusion-typography-row">
                                <SelectControl
                                    label={__('Font Family', 'digifusion')}
                                    value={values.fontFamily || ''}
                                    options={fontFamilyOptions}
                                    onChange={(newValue) => updateTypographyValue('fontFamily', newValue)}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                />
                            </div>
                            
                            {/* Font Size */}
                            <div className="digifusion-typography-row">
                                <div className="digifusion-responsive-control">
                                    <div className="digifusion-control-header-wrap">
                                        <div className="digifusion-control-left">
                                            <label className="digifusion-control-label">
                                                {__('Font Size', 'digifusion')}
                                            </label>
                                            <Button 
                                                className="digifusion-responsive-device-toggle"
                                                onClick={() => window.digi?.responsiveState?.toggleDevice?.()}
                                                aria-label={__(`Switch device view`, "digifusion")}
                                            >
                                                {window.digi?.deviceIcons?.[activeDevice] || activeDevice}
                                            </Button>
                                        </div>
                                        <div className="digifusion-control-right">
                                            <Button
                                                isSmall
                                                icon="image-rotate"
                                                onClick={() => updateResponsiveValue('fontSize', activeDevice, this.defaults.fontSize?.[activeDevice])}
                                                disabled={values.fontSize?.[activeDevice] === this.defaults.fontSize?.[activeDevice]}
                                                aria-label={__("Reset", "digifusion")}
                                                className="digifusion-reset"
                                            />
                                            <ToggleGroupControl
                                                value={values.fontSizeUnit || 'px'}
                                                onChange={(value) => updateTypographyValue('fontSizeUnit', value)}
                                                isBlock
                                                isSmall
                                                hideLabelFromVision
                                                aria-label={__("Select Units", "digifusion")}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            >
                                                {fontSizeUnits.map(unit => (
                                                    <ToggleGroupControlOption
                                                        key={unit.value}
                                                        value={unit.value}
                                                        label={unit.label}
                                                    />
                                                ))}
                                            </ToggleGroupControl>
                                        </div>
                                    </div>
                                    <RangeControl
                                        value={values.fontSize?.[activeDevice]}
                                        onChange={(newValue) => updateResponsiveValue('fontSize', activeDevice, newValue)}
                                        min={0}
                                        max={200}
                                        step={values.fontSizeUnit === 'px' ? 1 : 0.1}
                                        __next40pxDefaultSize={true}
                                        __nextHasNoMarginBottom={true}
                                    />
                                </div>
                            </div>
                            
                            {/* Font Weight */}
                            <div className="digifusion-typography-row">
                                <SelectControl
                                    label={__('Font Weight', 'digifusion')}
                                    value={values.fontWeight || ''}
                                    options={fontWeightOptions}
                                    onChange={(newValue) => updateTypographyValue('fontWeight', newValue)}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                />
                            </div>
                            
                            {/* Font Style */}
                            <div className="digifusion-typography-row">
                                <SelectControl
                                    label={__('Font Style', 'digifusion')}
                                    value={values.fontStyle || 'normal'}
                                    options={fontStyleOptions}
                                    onChange={(newValue) => updateTypographyValue('fontStyle', newValue)}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                />
                            </div>
                            
                            {/* Text Transform */}
                            <div className="digifusion-typography-row">
                                <SelectControl
                                    label={__('Text Transform', 'digifusion')}
                                    value={values.textTransform || ''}
                                    options={textTransformOptions}
                                    onChange={(newValue) => updateTypographyValue('textTransform', newValue)}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                />
                            </div>
                            
                            {/* Text Decoration */}
                            <div className="digifusion-typography-row">
                                <SelectControl
                                    label={__('Text Decoration', 'digifusion')}
                                    value={values.textDecoration || ''}
                                    options={textDecorationOptions}
                                    onChange={(newValue) => updateTypographyValue('textDecoration', newValue)}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                />
                            </div>
                            
                            {/* Line Height */}
                            <div className="digifusion-typography-row">
                                <div className="digifusion-responsive-control">
                                    <div className="digifusion-control-header-wrap">
                                        <div className="digifusion-control-left">
                                            <label className="digifusion-control-label">
                                                {__('Line Height', 'digifusion')}
                                            </label>
                                            <Button 
                                                className="digifusion-responsive-device-toggle"
                                                onClick={() => window.digi?.responsiveState?.toggleDevice?.()}
                                                aria-label={__(`Switch device view`, "digifusion")}
                                            >
                                                {window.digi?.deviceIcons?.[activeDevice] || activeDevice}
                                            </Button>
                                        </div>
                                        <div className="digifusion-control-right">
                                            <Button
                                                isSmall
                                                icon="image-rotate"
                                                onClick={() => updateResponsiveValue('lineHeight', activeDevice, this.defaults.lineHeight?.[activeDevice])}
                                                disabled={values.lineHeight?.[activeDevice] === this.defaults.lineHeight?.[activeDevice]}
                                                aria-label={__("Reset", "digifusion")}
                                                className="digifusion-reset"
                                            />
                                            <ToggleGroupControl
                                                value={values.lineHeightUnit || 'em'}
                                                onChange={(value) => updateTypographyValue('lineHeightUnit', value)}
                                                isBlock
                                                isSmall
                                                hideLabelFromVision
                                                aria-label={__("Select Units", "digifusion")}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            >
                                                {lineHeightUnits.map(unit => (
                                                    <ToggleGroupControlOption
                                                        key={unit.value}
                                                        value={unit.value}
                                                        label={unit.label}
                                                    />
                                                ))}
                                            </ToggleGroupControl>
                                        </div>
                                    </div>
                                    <RangeControl
                                        value={values.lineHeight?.[activeDevice]}
                                        onChange={(newValue) => updateResponsiveValue('lineHeight', activeDevice, newValue)}
                                        min={0}
                                        max={values.lineHeightUnit === 'px' ? 200 : 3}
                                        step={values.lineHeightUnit === 'px' ? 1 : 0.1}
                                        __next40pxDefaultSize={true}
                                        __nextHasNoMarginBottom={true}
                                    />
                                </div>
                            </div>
                            
                            {/* Letter Spacing */}
                            <div className="digifusion-typography-row">
                                <div className="digifusion-responsive-control">
                                    <div className="digifusion-control-header-wrap">
                                        <div className="digifusion-control-left">
                                            <label className="digifusion-control-label">
                                                {__('Letter Spacing', 'digifusion')}
                                            </label>
                                            <Button 
                                                className="digifusion-responsive-device-toggle"
                                                onClick={() => window.digi?.responsiveState?.toggleDevice?.()}
                                                aria-label={__(`Switch device view`, "digifusion")}
                                            >
                                                {window.digi?.deviceIcons?.[activeDevice] || activeDevice}
                                            </Button>
                                        </div>
                                        <div className="digifusion-control-right">
                                            <Button
                                                isSmall
                                                icon="image-rotate"
                                                onClick={() => updateResponsiveValue('letterSpacing', activeDevice, this.defaults.letterSpacing?.[activeDevice])}
                                                disabled={values.letterSpacing?.[activeDevice] === this.defaults.letterSpacing?.[activeDevice]}
                                                aria-label={__("Reset", "digifusion")}
                                                className="digifusion-reset"
                                            />
                                            <ToggleGroupControl
                                                value={values.letterSpacingUnit || 'px'}
                                                onChange={(value) => updateTypographyValue('letterSpacingUnit', value)}
                                                isBlock
                                                isSmall
                                                hideLabelFromVision
                                                aria-label={__("Select Units", "digifusion")}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            >
                                                {letterSpacingUnits.map(unit => (
                                                    <ToggleGroupControlOption
                                                        key={unit.value}
                                                        value={unit.value}
                                                        label={unit.label}
                                                    />
                                                ))}
                                            </ToggleGroupControl>
                                        </div>
                                    </div>
                                    <RangeControl
                                        value={values.letterSpacing?.[activeDevice]}
                                        onChange={(newValue) => updateResponsiveValue('letterSpacing', activeDevice, newValue)}
                                        min={-50}
                                        max={200}
                                        step={values.letterSpacingUnit === 'px' ? 1 : 0.1}
                                        __next40pxDefaultSize={true}
                                        __nextHasNoMarginBottom={true}
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<TypographyComponent />);
    }
}
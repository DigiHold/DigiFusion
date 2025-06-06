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
    PanelBody,
    PanelRow,
    RangeControl,
    SelectControl,
    TextControl,
    ToggleControl,
    ColorPicker,
    Popover,
    TabPanel,
    Icon
} = wp.components;
const { MediaUpload } = wp.blockEditor;
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
 * Initialize all controls when the Customizer loads
 */
wp.customize.bind('ready', function() {
    // Initialize Image Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-image') {
            new DigiFusionImageControl(control);
        }
    });

    // Initialize Dimensions Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-dimensions') {
            new DigiFusionDimensionsControl(control);
        }
    });

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

    // Initialize Box Shadow Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-box-shadow') {
            new DigiFusionBoxShadowControl(control);
        }
    });

    // Initialize Rich Text Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-rich-text') {
            new DigiFusionRichTextControl(control);
        }
    });

    // Initialize Toggle Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-toggle') {
            new DigiFusionToggleControl(control);
        }
    });

    // Initialize Text Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-text') {
            new DigiFusionTextControl(control);
        }
    });

    // Initialize Select Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-select') {
            new DigiFusionSelectControl(control);
        }
    });

    // Initialize Button Group Control
    wp.customize.control.each(function(control) {
        if (control.params.type === 'digifusion-button-group') {
            new DigiFusionButtonGroupControl(control);
        }
    });
});

/**
 * Image Control with Background Settings
 */
class DigiFusionImageControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-image-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.render();
    }
    
    render() {
        const ImageComponent = () => {
            // Keep original state for image URL
            const [imageUrl, setImageUrl] = useState(this.input.value);
            
            // Add state for background settings
            const [bgPosition, setBgPosition] = useState('center center');
            const [bgRepeat, setBgRepeat] = useState('no-repeat');
            const [bgSize, setBgSize] = useState('cover');
            
            // Position options
            const positionOptions = [
                { value: 'top left', label: __('Top Left', 'digifusion') },
                { value: 'top center', label: __('Top Center', 'digifusion') },
                { value: 'top right', label: __('Top Right', 'digifusion') },
                { value: 'center left', label: __('Center Left', 'digifusion') },
                { value: 'center center', label: __('Center Center', 'digifusion') },
                { value: 'center right', label: __('Center Right', 'digifusion') },
                { value: 'bottom left', label: __('Bottom Left', 'digifusion') },
                { value: 'bottom center', label: __('Bottom Center', 'digifusion') },
                { value: 'bottom right', label: __('Bottom Right', 'digifusion') },
            ];
            
            // Repeat options
            const repeatOptions = [
                { value: 'no-repeat', label: __('No Repeat', 'digifusion') },
                { value: 'repeat', label: __('Repeat', 'digifusion') },
                { value: 'repeat-x', label: __('Repeat Horizontally', 'digifusion') },
                { value: 'repeat-y', label: __('Repeat Vertically', 'digifusion') },
            ];
            
            // Size options
            const sizeOptions = [
                { value: 'cover', label: __('Cover', 'digifusion') },
                { value: 'contain', label: __('Contain', 'digifusion') },
                { value: 'auto', label: __('Auto', 'digifusion') },
                { value: '100% 100%', label: __('100% 100%', 'digifusion') },
            ];
            
            // Keep original handlers
            const onSelectImage = (media) => {
                const url = media.url;
                setImageUrl(url);
                this.input.value = url;
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            const removeImage = () => {
                setImageUrl('');
                this.input.value = '';
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            return (
                <div className="digifusion-image-control-inner">
                    {imageUrl && (
                        <div className="digifusion-image-preview">
                            <img src={imageUrl} alt="" />
                            <Button 
                                isDestructive
                                onClick={removeImage}
                                className="digifusion-remove-image"
                            >
                                <span className="dashicons dashicons-trash"></span>
                            </Button>
                        </div>
                    )}
                    
                    <MediaUpload
                        onSelect={onSelectImage}
                        allowedTypes={['image']}
                        render={({ open }) => (
                            <Button 
                                isPrimary
                                onClick={open}
                                className="digifusion-upload-button"
                            >
                                {imageUrl ? __('Change Image', 'digifusion') : __('Select Image', 'digifusion')}
                            </Button>
                        )}
                    />
                    
                    {/* Background Settings - Only show when image is selected */}
                    {imageUrl && (
                        <div className="digifusion-background-settings" style={{ marginTop: '15px' }}>
                            <SelectControl
                                label={__('Background Position', 'digifusion')}
                                value={bgPosition}
                                options={positionOptions}
                                onChange={(value) => setBgPosition(value)}
                            />
                            
                            <SelectControl
                                label={__('Background Repeat', 'digifusion')}
                                value={bgRepeat}
                                options={repeatOptions}
                                onChange={(value) => setBgRepeat(value)}
                            />
                            
                            <SelectControl
                                label={__('Background Size', 'digifusion')}
                                value={bgSize}
                                options={sizeOptions}
                                onChange={(value) => setBgSize(value)}
                            />
                        </div>
                    )}
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<ImageComponent />);
    }
}

/**
 * Dimensions Control - Improved version matching Gutenberg control
 */
class DigiFusionDimensionsControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-dimensions-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.isResponsive = this.container.dataset.isResponsive === 'true';
        this.units = JSON.parse(this.container.dataset.units || '[]');
        
        this.render();
    }
    
    render() {
        const DimensionsComponent = () => {
            // Parse stored values
            const [values, setValues] = useState(() => {
                try {
                    return JSON.parse(this.input.value);
                } catch (e) {
                    return {
                        desktop: { top: '', right: '', bottom: '', left: '', unit: 'px' },
                        tablet: { top: '', right: '', bottom: '', left: '', unit: 'px' },
                        mobile: { top: '', right: '', bottom: '', left: '', unit: 'px' }
                    };
                }
            });
            
            // Track linked state
            const [isLinked, setIsLinked] = useState(true);
            
            // Use global responsive state for device switching if responsive
            const [activeDevice, setActiveDevice] = useState('desktop');
            
            // Subscribe to global device state changes
            useEffect(() => {
                if (this.isResponsive) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setActiveDevice(device);
                    });
                    
                    // Cleanup subscription on unmount
                    return unsubscribe;
                }
            }, []);
            
            // Calculate if values are at default (all empty)
            const isDefault = () => {
                const current = values[activeDevice] || {};
                return (
                    current.top === '' &&
                    current.right === '' &&
                    current.bottom === '' &&
                    current.left === ''
                );
            };
            
            // Update value and trigger change
            const updateInputValue = (newValues) => {
                setValues(newValues);
                this.input.value = JSON.stringify(newValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            // Handle dimension value change
            const handleValueChange = (side, value) => {
                const newValues = { ...values };
                const current = { ...newValues[activeDevice] };
                
                if (isLinked) {
                    // When linked, update all sides
                    current.top = value;
                    current.right = value;
                    current.bottom = value;
                    current.left = value;
                } else {
                    // When unlinked, update only the specific side
                    current[side] = value;
                }
                
                newValues[activeDevice] = current;
                updateInputValue(newValues);
            };
            
            // Handle unit change
            const handleUnitChange = (unit) => {
                const newValues = { ...values };
                const current = { ...newValues[activeDevice] };
                
                current.unit = unit;
                newValues[activeDevice] = current;
                updateInputValue(newValues);
            };
            
            // Reset values
            const resetValues = () => {
                const newValues = { ...values };
                newValues[activeDevice] = {
                    top: '',
                    right: '',
                    bottom: '',
                    left: '',
                    unit: newValues[activeDevice]?.unit || 'px'
                };
                updateInputValue(newValues);
            };
            
            // Get max value based on unit
            const getMaxValue = (unit) => {
                switch (unit) {
                    case "px": return 500;
                    case "rem": return 30;
                    case "em": return 30;
                    case "%": return 100;
                    default: return 100;
                }
            };
            
            // Get step value based on unit
            const getStepValue = (unit) => {
                switch (unit) {
                    case "px": return 1;
                    case "rem": return 0.1;
                    case "em": return 0.1;
                    case "%": return 1;
                    default: return 1;
                }
            };
            
            // Current values for active device
            const currentDevice = activeDevice;
            const currentValues = values[currentDevice] || { top: '', right: '', bottom: '', left: '', unit: 'px' };
            
            return (
                <div className="digifusion-dimension-control">
					<div className="digifusion-control-header-wrap">
                        <div className="digifusion-control-left">
                            <label className="digifusion-control-title customize-control-title">{this.control.params.label}</label>
                            {this.isResponsive && (
                                <Button 
                                    className="digifusion-responsive-device-toggle"
                                    onClick={() => window.digi.responsiveState.toggleDevice()}
                                    aria-label={__(`Switch to ${window.digi.responsiveState.getNextDevice()} view`, "digifusion")}
                                >
                                    {window.digi.deviceIcons[activeDevice]}
                                </Button>
                            )}
                        </div>
                        <div className="digifusion-control-right">
                            <Button
                                isSmall
                                icon="image-rotate"
                                onClick={resetValues}
                                disabled={isDefault()}
                                aria-label={__("Reset", "digifusion")}
                                className="digifusion-reset"
                            />
                            {this.units && this.units.length > 1 && (
                                <ToggleGroupControl
                                    value={currentValues.unit}
                                    onChange={handleUnitChange}
                                    isBlock
                                    isSmall
									hideLabelFromVision
                                    aria-label={__("Select Units", "digifusion")}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                >
                                    {this.units.map((unit) => (
                                        <ToggleGroupControlOption
                                            key={unit.value}
                                            value={unit.value}
                                            label={unit.label}
                                        />
                                    ))}
                                </ToggleGroupControl>
                            )}
                        </div>
                    </div>
                    
                    <div className="digifusion-spacing-inputs">
                        <input
                            className="digifusion-spacing-input"
                            type="number"
                            value={currentValues.top}
                            onChange={(e) => handleValueChange("top", e.target.value)}
                            min={0}
                            max={getMaxValue(currentValues.unit)}
                            step={getStepValue(currentValues.unit)}
                            aria-label={__("Top", "digifusion")}
                        />
                        <input
                            className="digifusion-spacing-input"
                            type="number"
                            value={currentValues.right}
                            onChange={(e) => handleValueChange("right", e.target.value)}
                            min={0}
                            max={getMaxValue(currentValues.unit)}
                            step={getStepValue(currentValues.unit)}
                            aria-label={__("Right", "digifusion")}
                        />
                        <input
                            className="digifusion-spacing-input"
                            type="number"
                            value={currentValues.bottom}
                            onChange={(e) => handleValueChange("bottom", e.target.value)}
                            min={0}
                            max={getMaxValue(currentValues.unit)}
                            step={getStepValue(currentValues.unit)}
                            aria-label={__("Bottom", "digifusion")}
                        />
                        <input
                            className="digifusion-spacing-input"
                            type="number"
                            value={currentValues.left}
                            onChange={(e) => handleValueChange("left", e.target.value)}
                            min={0}
                            max={getMaxValue(currentValues.unit)}
                            step={getStepValue(currentValues.unit)}
                            aria-label={__("Left", "digifusion")}
                        />
                        <span
                            className={`digifusion-spacing-link ${
                                !isLinked ? "digifusion-spacing-control-disconnected" : ""
                            } dashicons ${
                                isLinked ? "dashicons-admin-links" : "dashicons-editor-unlink"
                            }`}
                            onClick={() => setIsLinked(!isLinked)}
                            title={isLinked ? __("Unlink values", "digifusion") : __("Link values", "digifusion")}
                            role="button"
                            tabIndex="0"
                            onKeyPress={(event) => {
                                if (event.key === 'Enter' || event.key === ' ') {
                                    setIsLinked(!isLinked);
                                }
                            }}
                        ></span>
                    </div>
                    <div className="digifusion-spacing-labels">
                        <span className="digifusion-spacing-label">{__("Top", "digifusion")}</span>
                        <span className="digifusion-spacing-label">{__("Right", "digifusion")}</span>
                        <span className="digifusion-spacing-label">{__("Bottom", "digifusion")}</span>
                        <span className="digifusion-spacing-label">{__("Left", "digifusion")}</span>
                        <span className="digifusion-spacing-label digifusion-spacing-link-label"></span>
                    </div>
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<DimensionsComponent />);
    }
}

/**
 * Range Control - Improved version matching Gutenberg control
 */
class DigiFusionRangeControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-range-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.isResponsive = this.container.dataset.isResponsive === 'true';
        this.min = parseFloat(this.container.dataset.min || 0);
        this.max = parseFloat(this.container.dataset.max || 100);
        this.step = parseFloat(this.container.dataset.step || 1);
        this.units = JSON.parse(this.container.dataset.units || '[]');
        
        this.render();
    }
    
    render() {
        const RangeControlComponent = () => {
            // Parse stored values
            const [values, setValues] = useState(() => {
                try {
                    return JSON.parse(this.input.value);
                } catch (e) {
                    return {
                        desktop: { value: '', unit: 'px' },
                        tablet: { value: '', unit: 'px' },
                        mobile: { value: '', unit: 'px' }
                    };
                }
            });
            
            // For tracking input value
            const [inputValue, setInputValue] = useState('');
            
            // Use global responsive state for device switching if responsive
            const [activeDevice, setActiveDevice] = useState('desktop');
            
            // Generate unique ID for the range input
            const [inputId] = useState(`range-control-${Math.floor(Math.random() * 10000)}`);
            
            // Subscribe to global device state changes
            useEffect(() => {
                if (this.isResponsive) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setActiveDevice(device);
                    });
                    
                    // Cleanup subscription on unmount
                    return unsubscribe;
                }
            }, []);
            
            // Update input value when active device changes
            useEffect(() => {
                const current = values[activeDevice] || { value: '', unit: 'px' };
                setInputValue(current.value === '' ? '' : String(current.value));
            }, [activeDevice, values]);
            
            // Update value and trigger change
            const updateInputValue = (newValues) => {
                setValues(newValues);
                this.input.value = JSON.stringify(newValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            // Handle direct number input change
            const handleInputChange = (e) => {
                const newValue = e.target.value;
                
                // Update local input state
                setInputValue(newValue);
                
                // If empty, update with empty string
                if (newValue === '') {
                    const updatedValues = {
                        ...values,
                        [activeDevice]: { ...values[activeDevice], value: '' }
                    };
                    updateInputValue(updatedValues);
                    return;
                }
                
                // For non-empty values, convert to number
                const numValue = parseFloat(newValue);
                if (!isNaN(numValue)) {
                    const updatedValues = {
                        ...values,
                        [activeDevice]: { ...values[activeDevice], value: numValue }
                    };
                    updateInputValue(updatedValues);
                }
            };
            
            // Handle slider change
            const handleSliderChange = (e) => {
                const newValue = parseFloat(e.target.value);
                
                // Update both input state and values
                setInputValue(String(newValue));
                const updatedValues = {
                    ...values,
                    [activeDevice]: { ...values[activeDevice], value: newValue }
                };
                updateInputValue(updatedValues);
            };
            
            // Handle unit change
            const handleUnitChange = (unit) => {
                const updatedValues = {
                    ...values,
                    [activeDevice]: { ...values[activeDevice], unit }
                };
                updateInputValue(updatedValues);
            };
            
            // Reset value
            const resetValue = () => {
                const updatedValues = {
                    ...values,
                    [activeDevice]: { ...values[activeDevice], value: '' }
                };
                updateInputValue(updatedValues);
                setInputValue('');
            };
            
            // Check if reset should be disabled
            const isResetDisabled = () => {
                return values[activeDevice]?.value === '';
            };
            
            // Calculate percentage for track fill and thumb positioning
            const getPercentage = () => {
                const current = values[activeDevice] || { value: '', unit: 'px' };
                if (current.value === '') return 0;
                
                const value = parseFloat(current.value);
                return Math.max(0, Math.min(100, ((value - this.min) / (this.max - this.min)) * 100));
            };
            
            const percentage = getPercentage();
            const currentDevice = activeDevice;
            const currentValues = values[currentDevice] || { value: '', unit: 'px' };
            
            return (
                <div className="digifusion-range-control">
                    <div className="digifusion-control-header-wrap">
                        <div className="digifusion-control-left">
                            <label className="digifusion-control-title customize-control-title">{this.control.params.label}</label>
                            {this.isResponsive && (
                                <Button 
                                    className="digifusion-responsive-device-toggle"
                                    onClick={() => window.digi.responsiveState.toggleDevice()}
                                    aria-label={__(`Switch to ${window.digi.responsiveState.getNextDevice()} view`, "digifusion")}
                                >
                                    {window.digi.deviceIcons[activeDevice]}
                                </Button>
                            )}
                        </div>
                        <div className="digifusion-control-right">
                            <Button
                                isSmall
                                icon="image-rotate"
                                onClick={resetValue}
                                disabled={isResetDisabled()}
                                aria-label={__("Reset", "digifusion")}
                                className="digifusion-reset"
                            />
                            {this.units && this.units.length > 1 && (
                                <ToggleGroupControl
                                    value={currentValues.unit}
                                    onChange={handleUnitChange}
                                    isBlock
                                    isSmall
									hideLabelFromVision
                                    aria-label={__("Select Units", "digifusion")}
                                    __next40pxDefaultSize={true}
                                    __nextHasNoMarginBottom={true}
                                >
                                    {this.units.map((unit) => (
                                        <ToggleGroupControlOption
                                            key={unit.value}
                                            value={unit.value}
                                            label={unit.label}
                                        />
                                    ))}
                                </ToggleGroupControl>
                            )}
                        </div>
                    </div>
                    
                    <div className="digifusion-range-control__mobile-controls">
                        <div className="digifusion-custom-range-control">
                            <div className="range-slider-wrapper">
                                <input 
                                    className="range-slider"
                                    id={inputId}
                                    max={this.max}
                                    min={this.min}
                                    step={this.step}
                                    type="range"
                                    value={currentValues.value === '' ? 0 : currentValues.value}
                                    onChange={handleSliderChange}
                                />
                                <div className="range-track">
                                    <div 
                                        className="range-track-fill"
                                        style={{ width: `${percentage}%` }}
                                    ></div>
                                </div>
                                <div 
                                    className="range-thumb"
                                    style={{ left: `${percentage}%` }}
                                ></div>
                            </div>
                            <div className="input-wrapper">
                                <input 
                                    className="number-input"
                                    type="number"
                                    id={`number-${inputId}`}
                                    value={inputValue}
                                    onChange={handleInputChange}
                                    min={this.min}
                                    max={this.max}
                                    step={this.step}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<RangeControlComponent />);
    }
}

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
        
        this.render();
    }
    
    render() {
        const ColorPickerComponent = () => {
            const [color, setColor] = useState(this.input.value || '#000000');
            const [isOpen, setIsOpen] = useState(false);
            
            const updateColor = (newColor) => {
                let colorValue;
                
                // Simplify the approach to match the box shadow control
                if (newColor.rgb && this.alpha) {
                    // If RGB with alpha is available, use it
                    const { r, g, b, a } = newColor.rgb;
                    colorValue = `rgba(${r}, ${g}, ${b}, ${a})`;
                } else if (newColor.hex) {
                    // Otherwise use hex
                    colorValue = newColor.hex;
                } else if (typeof newColor === 'string') {
                    // Direct string input
                    colorValue = newColor;
                } else {
                    // Fallback
                    return;
                }
                
                setColor(colorValue);
                this.input.value = colorValue;
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            return (
                <div className="digifusion-color-picker-wrap">
                    <label className="digifusion-color-label customize-control-title">{this.control.params.label}</label>
                    <div className="digifusion-color-picker-container">
                        <Button
                            className="digifusion-color-preview"
                            style={{ backgroundColor: color }}
                            onClick={() => setIsOpen(!isOpen)}
                        >
                            <span className="screen-reader-text">{__('Select color', 'digifusion')}</span>
                        </Button>
                        
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
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<ColorPickerComponent />);
    }
}

/**
 * Box Shadow Control
 */
class DigiFusionBoxShadowControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-box-shadow-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.render();
    }
    
    render() {
        const BoxShadowComponent = () => {
            const defaultValues = {
                normal: {
                    enable: false,
                    color: 'rgba(0, 0, 0, 0.2)',
                    horizontal: 0,
                    vertical: 0,
                    blur: 0,
                    spread: 0,
                    position: 'outset'
                },
                hover: {
                    enable: false,
                    color: 'rgba(0, 0, 0, 0.2)',
                    horizontal: 0,
                    vertical: 0,
                    blur: 0,
                    spread: 0,
                    position: 'outset'
                }
            };
            
            const [values, setValues] = useState(() => {
                try {
                    return JSON.parse(this.input.value) || defaultValues;
                } catch (e) {
                    return defaultValues;
                }
            });
            
            const [activeTab, setActiveTab] = useState('normal');
            const [isColorOpen, setIsColorOpen] = useState(false);
            
            const updateValue = (state, key, value) => {
                const newValues = { ...values };
                
                if (!newValues[state]) {
                    newValues[state] = { ...defaultValues[state] };
                }
                
                newValues[state][key] = value;
                setValues(newValues);
                updateInputValue(newValues);
            };
            
            const updateInputValue = (newValues) => {
                this.input.value = JSON.stringify(newValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            const currentState = values[activeTab] || defaultValues[activeTab];
            
            return (
                <div className="digifusion-box-shadow-control-inner">
                    <TabPanel
                        className="digifusion-control-tabs"
                        activeClass="active-tab"
                        tabs={[
                            {
                                name: 'normal',
                                title: __('Normal', 'digifusion'),
                                className: 'digifusion-tab normal',
                            },
                            {
                                name: 'hover',
                                title: __('Hover', 'digifusion'),
                                className: 'digifusion-tab hover',
                            },
                        ]}
                        onSelect={setActiveTab}
                    >
                        {() => (
                            <React.Fragment>
                                <ToggleControl
                                    label={__('Enable Box Shadow', 'digifusion')}
                                    checked={currentState.enable}
                                    onChange={(value) => updateValue(activeTab, 'enable', value)}
                                    __nextHasNoMarginBottom={true}
                                />
                                
                                {currentState.enable && (
                                    <React.Fragment>
                                        <div className="digifusion-box-shadow-color">
                                            <span className="digifusion-box-shadow-label">{__('Color', 'digifusion')}</span>
                                            <div className="digifusion-color-picker-container">
                                                <Button
                                                    className="digifusion-color-preview"
                                                    style={{ backgroundColor: currentState.color }}
                                                    onClick={() => setIsColorOpen(!isColorOpen)}
                                                >
                                                    <span className="screen-reader-text">{__('Select color', 'digifusion')}</span>
                                                </Button>
                                                
                                                {isColorOpen && (
                                                    <Popover
                                                        position="bottom"
                                                        onClose={() => setIsColorOpen(false)}
                                                    >
                                                        <div className="digifusion-color-picker-popover">
                                                            <ColorPicker
                                                                color={currentState.color}
                                                                onChangeComplete={(color) => {
                                                                    if (color.rgb) {
                                                                        const { r, g, b, a } = color.rgb;
                                                                        updateValue(activeTab, 'color', `rgba(${r}, ${g}, ${b}, ${a})`);
                                                                    } else {
                                                                        updateValue(activeTab, 'color', color.hex);
                                                                    }
                                                                }}
                                                                disableAlpha={false}
                                                            />
                                                        </div>
                                                    </Popover>
                                                )}
                                            </div>
                                        </div>
                                        
                                        <div className="digifusion-box-shadow-controls">
                                            <RangeControl
                                                label={__('Horizontal', 'digifusion')}
                                                value={currentState.horizontal}
                                                onChange={(value) => updateValue(activeTab, 'horizontal', value)}
                                                min={-100}
                                                max={100}
                                                step={1}
                                                allowReset={true}
                                                resetFallbackValue={0}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            />
                                            
                                            <RangeControl
                                                label={__('Vertical', 'digifusion')}
                                                value={currentState.vertical}
                                                onChange={(value) => updateValue(activeTab, 'vertical', value)}
                                                min={-100}
                                                max={100}
                                                step={1}
                                                allowReset={true}
                                                resetFallbackValue={0}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            />
                                            
                                            <RangeControl
                                                label={__('Blur', 'digifusion')}
                                                value={currentState.blur}
                                                onChange={(value) => updateValue(activeTab, 'blur', value)}
                                                min={0}
                                                max={100}
                                                step={1}
                                                allowReset={true}
                                                resetFallbackValue={0}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            />
                                            
                                            <RangeControl
                                                label={__('Spread', 'digifusion')}
                                                value={currentState.spread}
                                                onChange={(value) => updateValue(activeTab, 'spread', value)}
                                                min={-100}
                                                max={100}
                                                step={1}
                                                allowReset={true}
                                                resetFallbackValue={0}
                                                __next40pxDefaultSize={true}
                                                __nextHasNoMarginBottom={true}
                                            />
                                            
                                            <div className="digifusion-multi-buttons-control">
                                                <div className="digifusion-multi-buttons-control__label">
                                                    {__('Position', 'digifusion')}
                                                </div>
                                                <ToggleGroupControl
                                                    value={currentState.position}
                                                    onChange={(value) => updateValue(activeTab, 'position', value)}
                                                    isBlock
                                                    __next40pxDefaultSize={true}
                                                    __nextHasNoMarginBottom={true}
                                                >
                                                    <ToggleGroupControlOption 
                                                        value="outset" 
                                                        label={__('Outset', 'digifusion')} 
                                                    />
                                                    <ToggleGroupControlOption 
                                                        value="inset" 
                                                        label={__('Inset', 'digifusion')} 
                                                    />
                                                </ToggleGroupControl>
                                            </div>
                                        </div>
                                    </React.Fragment>
                                )}
                            </React.Fragment>
                        )}
                    </TabPanel>
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<BoxShadowComponent />);
    }
}

/**
 * Rich Text Control
 */
class DigiFusionRichTextControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-rich-text-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.editorId = `digifusion-rich-text-${control.id}`;
        this.editor = document.getElementById(this.editorId);
        
        if (!this.editor) {
            return;
        }
        
        this.initialize();
    }
    
    initialize() {
        // Initialize QuickTags normally
        if (window.quicktags) {
            const buttons = ['strong', 'em', 'link', 'ul', 'ol', 'li', 'code'];
            window.quicktags({
                id: this.editorId,
                buttons: buttons.join(',')
            });
            
            // After QuickTags initializes, observe the toolbar
            this.observeQuickTagsToolbar();
        }
        
        // Add event listener for the textarea direct typing
        this.editor.addEventListener('input', () => this.syncWithCustomizer());
    }
    
    observeQuickTagsToolbar() {
        // We need to wait for QuickTags to finish initializing
        setTimeout(() => {
            const toolbar = document.getElementById(`qt_${this.editorId}_toolbar`);
            if (!toolbar) return;
            
            // Add MutationObserver to detect when the editor content changes
            const observer = new MutationObserver((mutations) => {
                // If the textarea content has changed, sync with customizer
                this.syncWithCustomizer();
            });
            
            // Observe the editor for changes to its attributes and content
            observer.observe(this.editor, { 
                attributes: true, 
                childList: true, 
                characterData: true,
                subtree: true
            });
            
            // Add click listeners to all QuickTags buttons
            const buttons = toolbar.querySelectorAll('.ed_button');
            buttons.forEach(button => {
                // Add our own click handler that runs after the QuickTags handler
                button.addEventListener('click', () => {
                    // Use setTimeout to ensure this runs after QuickTags has finished
                    setTimeout(() => {
                        this.syncWithCustomizer();
                    }, 10);
                });
            });
            
            // Special handling for link button
            this.handleLinkButton(toolbar);
            
            // Add special handling for QTags.closeAllTags
            const originalCloseAllTags = window.QTags.closeAllTags;
            if (originalCloseAllTags) {
                window.QTags.closeAllTags = (editor_id) => {
                    originalCloseAllTags(editor_id);
                    if (editor_id === this.editorId) {
                        setTimeout(() => this.syncWithCustomizer(), 10);
                    }
                };
            }
            
            // Monitor keyboard shortcuts that might be used by QuickTags
            document.addEventListener('keydown', (e) => {
                // Common keyboard shortcuts like Ctrl+B, Ctrl+I, etc.
                if ((e.ctrlKey || e.metaKey) && 
                    ['b', 'i', 'u', 'k'].includes(e.key.toLowerCase()) && 
                    document.activeElement === this.editor) {
                    setTimeout(() => this.syncWithCustomizer(), 10);
                }
            });
        }, 500); // Give QuickTags time to initialize
    }
    
    handleLinkButton(toolbar) {
        // Find the link button
        const linkButton = toolbar.querySelector('#qt_' + this.editorId + '_link');
        if (!linkButton) return;
        
        // Save original QTags.insertLink function
        if (window.QTags && window.QTags.insertLink) {
            const originalInsertLink = window.QTags.insertLink;
            
            window.QTags.insertLink = (e, c, ed) => {
                originalInsertLink(e, c, ed);
                
                // If this is our editor, sync with customizer
                if (ed && ed.id === this.editorId) {
                    // Add a longer delay for link insertion because it involves a prompt
                    setTimeout(() => this.syncWithCustomizer(), 100);
                }
            };
        }
        
        // Create our own link handler as a backup
        linkButton.addEventListener('click', () => {
            // Wait for any dialog to appear, then for it to close
            setTimeout(() => {
                // Start checking for changes
                const checkInterval = setInterval(() => {
                    // If editor value changed, sync and stop checking
                    const hiddenValue = this.input.value;
                    const editorValue = this.editor.value;
                    
                    if (hiddenValue !== editorValue) {
                        this.syncWithCustomizer();
                        clearInterval(checkInterval);
                    }
                }, 100);
                
                // Stop checking after 5 seconds even if no change detected
                setTimeout(() => clearInterval(checkInterval), 5000);
            }, 100);
        });
    }
    
    syncWithCustomizer() {
        // Copy content from textarea to hidden input
        this.input.value = this.editor.value;
        
        // Trigger change event with bubbling
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);
        
        // Directly update WordPress customizer - this is the most reliable method
        if (wp && wp.customize) {
            const setting = wp.customize(this.control.id);
            if (setting) {
                setting.set(this.editor.value);
            }
        }
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
 * Text Control
 */
class DigiFusionTextControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-text-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.render();
    }
    
    render() {
        const TextControlComponent = () => {
            const [value, setValue] = useState(this.input.value || '');
            
            const handleChange = (newValue) => {
                setValue(newValue);
                
                // Update the input value
                this.input.value = newValue;
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            return (
                <div className="digifusion-text-control-inner">
                    <TextControl
                        value={value}
                        onChange={handleChange}
                        __next40pxDefaultSize={true}
                        __nextHasNoMarginBottom={true}
                    />
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<TextControlComponent />);
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
 * Responsive Button Group Control (similar to the example code provided)
 */
class DigiFusionButtonGroupControl {
    constructor(control) {
        this.control = control;
        this.container = document.querySelector(`.digifusion-button-group-container[data-control-id="${control.id}"]`);
        this.input = document.getElementById(`_customize-input-${control.id}`);
        
        if (!this.container || !this.input) {
            return;
        }
        
        this.isResponsive = this.container.dataset.isResponsive === 'true';
        this.choices = JSON.parse(this.container.dataset.choices || '[]');
        this.defaultValue = this.container.dataset.defaultValue || '';
        this.defaultValues = JSON.parse(this.container.dataset.defaultValues || 'null');
        
        this.render();
    }
    
    render() {
        const ButtonGroupComponent = () => {
            // Parse stored values
            const [values, setValues] = useState(() => {
                try {
                    return JSON.parse(this.input.value) || {
                        desktop: this.defaultValue,
                        tablet: this.defaultValue,
                        mobile: this.defaultValue
                    };
                } catch (e) {
                    return {
                        desktop: this.defaultValue,
                        tablet: this.defaultValue,
                        mobile: this.defaultValue
                    };
                }
            });
            
            // Use global responsive state for device switching if responsive
            const [activeDevice, setActiveDevice] = useState('desktop');
            
            // Subscribe to global device state changes
            useEffect(() => {
                if (this.isResponsive) {
                    const unsubscribe = window.digi.responsiveState.subscribe((device) => {
                        setActiveDevice(device);
                    });
                    
                    // Cleanup subscription on unmount
                    return unsubscribe;
                }
            }, []);
            
            // Ensure value is properly structured with device keys
            const ensureResponsiveValue = (val) => {
                if (!val || typeof val !== 'object') {
                    return {
                        desktop: this.defaultValue,
                        tablet: this.defaultValue,
                        mobile: this.defaultValue
                    };
                }
                
                const result = {};
                ['desktop', 'tablet', 'mobile'].forEach(device => {
                    if (val[device] !== undefined) {
                        result[device] = val[device];
                    } else {
                        result[device] = this.defaultValue;
                    }
                });
                
                return result;
            };
            
            const safeValues = ensureResponsiveValue(values);
            
            // Update value for current device
            const updateValue = (newValue) => {
                const updatedValues = {
                    ...safeValues,
                    [activeDevice]: newValue
                };
                
                setValues(updatedValues);
                
                // Update the input value
                this.input.value = JSON.stringify(updatedValues);
                
                // Trigger the change event
                const event = new Event('change');
                this.input.dispatchEvent(event);
            };
            
            // Reset to default value
            const resetValue = () => {
                let defaultVal;
                
                if (this.defaultValues) {
                    defaultVal = this.defaultValues[activeDevice] !== undefined ? 
                        this.defaultValues[activeDevice] : 
                        (this.defaultValues.default !== undefined ? this.defaultValues.default : this.defaultValue);
                } else {
                    defaultVal = this.defaultValue;
                }
                
                updateValue(defaultVal);
            };
            
            // Determine if reset button should be disabled
            const isResetDisabled = () => {
                if (!this.defaultValues) return false;
                
                const defaultVal = this.defaultValues[activeDevice] !== undefined ? 
                    this.defaultValues[activeDevice] : 
                    (this.defaultValues.default !== undefined ? this.defaultValues.default : this.defaultValue);
                
                return safeValues[activeDevice] === defaultVal;
            };
            
            return (
                <div className="digifusion-button-group-container">
                    <div className="digifusion-control-header-wrap">
                        <div className="digifusion-control-left">
                            <label className="digifusion-control-title customize-control-title">{this.control.params.label}</label>
                            {this.isResponsive && (
                                <Button 
                                    className="digifusion-responsive-device-toggle"
                                    onClick={() => window.digi.responsiveState.toggleDevice()}
                                    aria-label={__(`Switch to ${window.digi.responsiveState.getNextDevice()} view`, "digifusion")}
                                >
                                    {window.digi.deviceIcons[activeDevice]}
                                </Button>
                            )}
                        </div>
                        <div className="digifusion-control-right">
                            <Button
                                isSmall
                                icon="image-rotate"
                                onClick={resetValue}
                                disabled={isResetDisabled()}
                                aria-label={__("Reset", "digifusion")}
                                className="digifusion-reset"
                            />
                        </div>
                    </div>
                    
                    <div className="digifusion-button-group-options">
                        <ToggleGroupControl
                            value={safeValues[activeDevice]}
                            onChange={updateValue}
                            isBlock
							hideLabelFromVision
                            __next40pxDefaultSize={true}
                            __nextHasNoMarginBottom={true}
                        >
                            {this.choices.map(option => (
                                <ToggleGroupControlOption
                                    key={option.value}
                                    value={option.value}
                                    label={option.label}
                                />
                            ))}
                        </ToggleGroupControl>
                    </div>
                </div>
            );
        };
        
        // Use createRoot instead of render
        const root = createRoot(this.container);
        root.render(<ButtonGroupComponent />);
    }
}
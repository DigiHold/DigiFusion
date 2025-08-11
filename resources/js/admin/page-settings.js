// Wrap everything in an IIFE to avoid global scope pollution
(function() {
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editor;
    const { MediaUpload, MediaUploadCheck, PanelColorSettings } = wp.blockEditor;
    const { 
        PanelBody, 
        ToggleControl,
        SelectControl,
        TextControl,
        TextareaControl,
        Button
    } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { useState, useEffect } = wp.element;
    const { __ } = wp.i18n;

    // Logo Upload Component
    const LogoUpload = ({ logoId, onLogoChange }) => {
        const [logoUrl, setLogoUrl] = useState('');
        
        useEffect(() => {
            if (logoId) {
                wp.apiFetch({
                    path: `/wp/v2/media/${logoId}`
                }).then(media => {
                    setLogoUrl(media.media_details?.sizes?.thumbnail?.source_url || media.source_url);
                }).catch(() => {
                    setLogoUrl('');
                });
            } else {
                setLogoUrl('');
            }
        }, [logoId]);

        return (
            <div className="digifusion-media-control">
                <MediaUploadCheck>
                    <MediaUpload
                        onSelect={(media) => onLogoChange(media.id)}
                        allowedTypes={['image']}
                        value={logoId}
                        render={({ open }) => (
                            <div className="digifusion-media-upload-wrapper">
                                {logoUrl ? (
                                    <div className="digifusion-media-preview" style={{ marginBottom: '8px' }}>
                                        <img 
                                            src={logoUrl} 
                                            alt={__('Custom Logo', 'digifusion')} 
                                            style={{
                                                maxWidth: '100%',
                                                height: 'auto',
                                                display: 'block',
                                                borderRadius: '4px',
                                                border: '1px solid #ddd'
                                            }}
                                        />
                                        <div 
                                            className="digifusion-media-controls" 
                                            style={{
                                                display: 'flex',
                                                gap: '8px',
                                                marginTop: '8px'
                                            }}
                                        >
                                            <Button
                                                variant="secondary"
                                                onClick={open}
                                                size="small"
                                            >
                                                {__('Change', 'digifusion')}
                                            </Button>
                                            <Button 
                                                variant="secondary"
                                                isDestructive
                                                onClick={() => onLogoChange(0)}
                                                size="small"
                                            >
                                                {__('Remove', 'digifusion')}
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <Button
                                        variant="primary"
                                        onClick={open}
                                        style={{ width: '100%', justifyContent: 'center' }}
                                    >
                                        {__('Select Logo', 'digifusion')}
                                    </Button>
                                )}
                            </div>
                        )}
                    />
                </MediaUploadCheck>
            </div>
        );
    };

    // Main Page Settings Sidebar Component
    const PageSettingsSidebar = () => {
        const { editPost } = useDispatch("core/editor");
        const postMeta = useSelect((select) => {
            return select("core/editor").getEditedPostAttribute("meta");
        });

        // State for all settings
        const [disableHeader, setDisableHeader] = useState(false);
        const [disablePageHeader, setDisablePageHeader] = useState(false);
        const [disableFooter, setDisableFooter] = useState(false);
        const [disablePadding, setDisablePadding] = useState(false);
        const [headerType, setHeaderType] = useState('');
        const [customLogo, setCustomLogo] = useState(0);
        const [menuColors, setMenuColors] = useState({});
        const [customPageTitle, setCustomPageTitle] = useState('');
        const [pageDescription, setPageDescription] = useState('');

        // Load meta data on component mount and when postMeta changes
        useEffect(() => {
            if (postMeta) {
                setDisableHeader(postMeta.digifusion_disable_header || false);
                setDisablePageHeader(postMeta.digifusion_disable_page_header || false);
                setDisableFooter(postMeta.digifusion_disable_footer || false);
                setDisablePadding(postMeta.digifusion_disable_padding || false);
                setHeaderType(postMeta.digifusion_header_type || '');
                setCustomLogo(postMeta.digifusion_custom_logo || 0);
                setMenuColors(postMeta.digifusion_menu_colors || {});
                setCustomPageTitle(postMeta.digifusion_custom_page_title || '');
                setPageDescription(postMeta.digifusion_page_description || '');
            }
        }, [postMeta]);

        // Helper function to update meta
        const updateMeta = (key, value) => {
            editPost({ meta: { [key]: value } });
        };

        // Update menu colors
        const updateMenuColor = (colorType, color) => {
            const newColors = { ...menuColors, [colorType]: color };
            setMenuColors(newColors);
            updateMeta('digifusion_menu_colors', newColors);
        };

        return (
            <>
                <PluginSidebarMoreMenuItem target="digifusion-page-settings">
                    {__("DigiFusion Settings", "digifusion")}
                </PluginSidebarMoreMenuItem>
                <PluginSidebar
                    name="digifusion-page-settings"
                    title={__("DigiFusion Settings", "digifusion")}
                    className="digifusion-page-settings-sidebar"
                >
                    {/* Disable Elements Panel */}
                    <PanelBody title={__("Disable Elements", "digifusion")} initialOpen={true}>
                        <ToggleControl
                            label={__("Disable Header", "digifusion")}
                            checked={disableHeader}
                            onChange={(value) => {
                                setDisableHeader(value);
                                updateMeta('digifusion_disable_header', value);
                            }}
                            __nextHasNoMarginBottom={true}
                        />
                        <ToggleControl
                            label={__("Disable Page Header", "digifusion")}
                            checked={disablePageHeader}
                            onChange={(value) => {
                                setDisablePageHeader(value);
                                updateMeta('digifusion_disable_page_header', value);
                            }}
                            __nextHasNoMarginBottom={true}
                        />
                        <ToggleControl
                            label={__("Disable Footer", "digifusion")}
                            checked={disableFooter}
                            onChange={(value) => {
                                setDisableFooter(value);
                                updateMeta('digifusion_disable_footer', value);
                            }}
                            __nextHasNoMarginBottom={true}
                        />
                        <ToggleControl
                            label={__("Disable Container Spacing", "digifusion")}
                            checked={disablePadding}
                            onChange={(value) => {
                                setDisablePadding(value);
                                updateMeta('digifusion_disable_padding', value);
                            }}
                            __nextHasNoMarginBottom={true}
                        />
                    </PanelBody>

                    {/* Header Settings Panel */}
                    {!disableHeader && (
                        <PanelBody title={__("Header Settings", "digifusion")} initialOpen={false}>
                            <SelectControl
                                label={__("Header Type", "digifusion")}
                                value={headerType}
                                options={[
                                    { label: __('Default', 'digifusion'), value: '' },
                                    { label: __('Minimal', 'digifusion'), value: 'minimal' },
                                    { label: __('Transparent', 'digifusion'), value: 'transparent' }
                                ]}
                                onChange={(value) => {
                                    setHeaderType(value);
                                    updateMeta('digifusion_header_type', value);
                                }}
                                __nextHasNoMarginBottom={true}
                            />

                            <div style={{ marginBottom: '16px' }}>
                                <p className="components-base-control__label">{__("Custom Logo", "digifusion")}</p>
                                <LogoUpload
                                    logoId={customLogo}
                                    onLogoChange={(logoId) => {
                                        setCustomLogo(logoId);
                                        updateMeta('digifusion_custom_logo', logoId);
                                    }}
                                />
                            </div>

                            <PanelColorSettings
                                title={__("Menu Colors", "digifusion")}
                                initialOpen={false}
                                enableAlpha={true}
                                colorSettings={[
                                    {
                                        value: menuColors.normal || '',
                                        onChange: (color) => updateMenuColor('normal', color),
                                        label: __("Normal Color", "digifusion"),
                                    },
                                    {
                                        value: menuColors.hover || '',
                                        onChange: (color) => updateMenuColor('hover', color),
                                        label: __("Hover Color", "digifusion"),
                                    },
                                    {
                                        value: menuColors.current || '',
                                        onChange: (color) => updateMenuColor('current', color),
                                        label: __("Current Color", "digifusion"),
                                    },
                                ]}
                            />
                        </PanelBody>
                    )}

                    {/* Page Header Settings Panel */}
                    {!disablePageHeader && (
                        <PanelBody title={__("Page Header Settings", "digifusion")} initialOpen={false}>
                            <TextControl
                                label={__("Custom Title", "digifusion")}
                                value={customPageTitle}
                                onChange={(value) => {
                                    setCustomPageTitle(value);
                                    updateMeta('digifusion_custom_page_title', value);
                                }}
                                help={__("Leave empty to use default page title.", "digifusion")}
                                __nextHasNoMarginBottom={true}
                            />
                            <TextareaControl
                                label={__("Description", "digifusion")}
                                value={pageDescription}
                                onChange={(value) => {
                                    setPageDescription(value);
                                    updateMeta('digifusion_page_description', value);
                                }}
                                help={__("Optional description to display below the title.", "digifusion")}
                                rows={3}
                                __nextHasNoMarginBottom={true}
                            />
                        </PanelBody>
                    )}
                </PluginSidebar>
            </>
        );
    };

    registerPlugin('digifusion-page-settings', {
        render: PageSettingsSidebar,
        icon: (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 302 512" width="24" height="24" fill="currentColor"><path d="M192.0681,512c23.2622-38.5537,25.4058-80.1935,6.4307-124.9218-5.2767,23.9965-14.9292,38.5597-28.9583,43.693,13.0277-41.6963,2.1418-86.7441-32.6589-135.1434-.7511,49.9876-11.4698,86.401-32.1572,109.2413-28.4988,31.4337-28.1633,66.9092,1.0051,106.4254C-15.3539,439.0799-32.4367,342.226,54.4794,220.7355c5.391,29.3499,18.4542,47.3692,39.1919,54.058-22.6044-107.5006,1.1787-199.0984,71.3482-274.7935.4312,167.9841,46.3956,182.915,104.5105,257.8995,62.7465,89.985,25.8727,193.8299-77.4619,254.1005h0Z" fill-rule="evenodd"/></svg>
        ),
    });
})();
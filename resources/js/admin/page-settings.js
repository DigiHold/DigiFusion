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
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 512" width="24" height="24" fill="currentColor"><polygon points="250 202.2535 126.5277 202.2535 188.0896 0 0 309.7465 123.4723 309.7465 61.9104 512 250 202.2535"></polygon></svg>
        ),
    });
})();
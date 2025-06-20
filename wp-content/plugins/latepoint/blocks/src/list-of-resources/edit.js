/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import {
    Panel,
    PanelBody,
    PanelRow,
    TextControl,
    SelectControl,
    ToggleControl,
    TabPanel,
    __experimentalUnitControl as UnitControl,
} from '@wordpress/components';

import TypographyControl from "../../controls/TypographyControl";
import ColorSelectorControl from "../../controls/ColorSelectorControl";
import BorderControl from "../../controls/BorderControl";
import PaddingBoxControl from "../../controls/PaddingBoxControl";
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import {useState, useEffect} from "@wordpress/element";
import React from "react";
import {SettingsIcon, StylesIcon, CardIcon} from "../../controls/LatepointIcons";
import BoxShadowControl from "../../controls/BoxShadowControl";
import FontSizeControl from "../../controls/FontSizeControl";


const ListOfResourcesWrapper = styled.div`

`;

const LatepointBookButton = styled.div`
    margin-top: 10px;
`;

const ListOfResources = styled.div`
    display: grid;
    gap: 30px;
    grid-template-columns: 1fr 1fr 1fr 1fr;

    &.resources-columns-1 {
        grid-template-columns: 1fr;
        grid-gap: 20px;
    }

    &.resources-columns-2 {
        grid-template-columns: 1fr 1fr;
        grid-gap: 50px;
    }

    &.resources-columns-3 {
        grid-template-columns: 1fr 1fr 1fr;
        grid-gap: 40px;
    }

    &.resources-columns-4 {
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-gap: 30px;
    }

    &.resources-columns-5 {
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-gap: 20px;
    }
`;

const Resource = styled.div`
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 2px 18px -6px rgba(0, 0, 0, 0.2), 0 1px 2px 0 rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    height: 100%;
    overflow: hidden;
`;

const ResourceTitle = styled.h3`
    margin: 10px 0;
    font-size: 22px;
`;
const ResourceImage = styled.div`
    padding-bottom: 75%;
    position: relative;
    margin: -20px -20px 25px;
`;
const ResourceDescription = styled.div`
    color: #7c85a3;
    font-size: 15px;
    font-weight: 400;
    margin-bottom: 10px;
    line-height: 1.3;
`;
const ResourcePrice = styled.div`
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 10px;
`;

const PanelRowBlock = styled(PanelRow)`
    display: block;
    margin-bottom: 20px;
`;

const NoMatches = styled.div`
    display: block;
    padding: 20px;
    background-color: #eee;
    color: #888;
    text-align: center;
`;
const Separator = styled.div`
    height: 1px;
    background-color: #e0e0e0;
`;

const btnHoverColor = (e, attributes, action) => {
    if (attributes.is_inherit) {
        return;
    }

    let current_el = e.currentTarget.style;
    if (action === 'mouseEnter') {
        if (attributes.bg_color_hover) current_el.backgroundColor = attributes.bg_color_hover;
        if (attributes.border_color_hover) current_el.borderColor = attributes.border_color_hover;
        if (attributes.text_color_hover) current_el.color = attributes.text_color_hover;
    } else {
        current_el.backgroundColor = attributes.button_bg_color;
        current_el.borderColor = attributes.border_color;
        current_el.color = attributes.button_text_color;
    }
};
const cardHoverColor = (e, attributes, action) => {
    if (attributes.is_inherit) {
        return;
    }
    let current_el = e.currentTarget.style;
    let price_el = e.currentTarget.querySelector('.lb-resource-price');
    let title_el = e.currentTarget.querySelector('.lb-resource-title');
    let descr_el = e.currentTarget.querySelector('.lb-resource-descr');
    if (action === 'mouseEnter') {
        if (title_el && attributes.card_text_color_hover) title_el.style.color = attributes.card_text_color_hover;
        if (price_el && attributes.card_price_color_hover) price_el.style.color = attributes.card_price_color_hover;
        if (descr_el && attributes.card_descr_color_hover) descr_el.style.color = attributes.card_descr_color_hover;
        if (attributes.card_bg_color_hover) current_el.backgroundColor = attributes.card_bg_color_hover;
        if (attributes.card_border_color_hover) current_el.borderColor = attributes.card_border_color_hover;
    } else {
        if (title_el && attributes.card_text_color) title_el.style.color = attributes.card_text_color;
        if (price_el && attributes.card_price_color) price_el.style.color = attributes.card_price_color || '';
        if (descr_el && attributes.card_descr_color) descr_el.style.color = attributes.card_descr_color;
        current_el.backgroundColor = attributes.card_bg_color || '';
        current_el.borderColor = attributes.card_border_color || '';
    }
};


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
    const blockProps = useBlockProps();
    useEffect(() => {
        if (!attributes.id) {
            setAttributes({ id: Math.random().toString(36).substr(2, 9) + '-' + Date.now() });
        }
    }, []);

    const [activeTab, setActiveTab] = useState('settings');

    const generateStyles = () => {
        let styles = {}
        if (attributes.is_inherit) return styles;

        if (attributes.font_weight) styles.fontWeight = attributes.font_weight
        if (attributes.button_font_size) styles.fontSize = attributes.button_font_size
        if (attributes.text_transform) styles.textTransform = attributes.text_transform
        if (attributes.font_family) styles.fontFamily = attributes.font_family
        if (attributes.line_height) styles.lineHeight = attributes.line_height
        if (attributes.letter_spacing) styles.letterSpacing = attributes.letter_spacing
        if (attributes.button_border_radius) styles.borderRadius = attributes.button_border_radius
        if (attributes.button_bg_color) styles.backgroundColor = attributes.button_bg_color;
        if (attributes.button_text_color) styles.color = attributes.button_text_color;
        if (attributes.border_color) styles.borderColor = attributes.border_color;
        if (attributes.border_style && attributes.border_style !== 'default') styles.borderStyle = attributes.border_style;
        if (attributes.border_width && attributes.border_style !== 'default') styles.borderWidth = attributes.border_width;
        if (attributes.padding) styles.padding = attributes.padding;
        return styles
    }

    const resourceCardStyles = () => {
        let styles = {}

        if (attributes.is_inherit) return styles;

        if (attributes.card_padding) styles.padding = attributes.card_padding;
        if (attributes.card_box_shadow) styles.boxShadow = attributes.card_box_shadow;
        if (attributes.card_border_style && attributes.card_border_style !== 'default') styles.borderStyle = attributes.card_border_style;
        if (attributes.card_border_radius) styles.borderRadius = attributes.card_border_radius;
        if (attributes.card_border_width && attributes.card_border_style !== 'default') styles.borderWidth = attributes.card_border_width;
        if (attributes.card_border_color) styles.borderColor = attributes.card_border_color;
        if (attributes.card_bg_color) styles.backgroundColor = attributes.card_bg_color;
        return styles
    }

    const cardPriceStyles = () => {
        let styles = {}
        if (attributes.is_inherit) return styles;

        if (attributes.price_font_size) styles.fontSize = attributes.price_font_size
        if ( attributes.card_price_color) styles.color = attributes.card_price_color

        return styles;
    }

    const cardTitleStyles = () => {
        let styles = {}
        if (attributes.is_inherit) return styles;

        if (attributes.title_font_size) styles.fontSize = attributes.title_font_size
        if ( attributes.card_text_color) styles.color = attributes.card_text_color

        return styles;
    }

    const cardDescrStyles = () => {
        let styles = {}
        if (attributes.is_inherit) return styles;

        if (attributes.descr_font_size) styles.fontSize = attributes.descr_font_size
        if ( attributes.card_descr_color) styles.color = attributes.card_descr_color

        return styles;
    }


    const isIncludedInIds = (id, ids) => {
        if (!ids) return true;
        let clean_item_ids = ids.split(",").map(item => item.trim());
        return clean_item_ids ? clean_item_ids.includes(id) : true;
    }

    const renderResources = () => {
        let resources = []
        switch (attributes.items) {
            case 'services':
                resources = latepoint_helper.services.filter((service) => isIncludedInIds(service.id, attributes.item_ids) && isIncludedInIds(service.category_id, attributes.group_ids))
                break;
            case 'agents':
                resources = latepoint_helper.agents.filter((agent) => isIncludedInIds(agent.id, attributes.item_ids))
                break;
            case 'locations':
                resources = latepoint_helper.locations.filter((location) => isIncludedInIds(location.id, attributes.item_ids) && isIncludedInIds(location.category_id, attributes.group_ids))
                break;
            case 'bundles':
                resources = latepoint_helper.bundles.filter((bundle) => isIncludedInIds(bundle.id, attributes.item_ids))
                break;
        }
        if (resources.length) {
            if (attributes.limit) resources = resources.slice(0, attributes.limit)
            let resources_items = resources.map((resource) =>
                <Resource
                    key={resource.id}
                    style={resourceCardStyles()}
                    onMouseEnter={(e) => cardHoverColor(e, attributes, 'mouseEnter')}
                    onMouseLeave={(e) => cardHoverColor(e, attributes, 'mouseLeave')}
                >
                    {(!attributes.hide_image && resource.image_url) && (
                        <ResourceImage>
                            <img src={resource.image_url} style={{ position: 'absolute', inset: 0, objectFit: 'cover', width: '100%', height: '100%' }}/>
                        </ResourceImage>
                    )}
                    <ResourceTitle className="lb-resource-title" style={cardTitleStyles()}>{resource.name}</ResourceTitle>
                    { (!attributes.hide_price && resource.price_formatted) && <ResourcePrice className="lb-resource-price" style={cardPriceStyles()}>{resource.price_formatted}</ResourcePrice>}
                    { (!attributes.hide_description && resource.description) && <ResourceDescription className="lb-resource-descr" style={cardDescrStyles()}>{resource.description}</ResourceDescription>}
                    <div className="wp-block-button">
                        <LatepointBookButton
                            style={generateStyles()}
                            onMouseEnter={(e) => btnHoverColor(e, attributes, 'mouseEnter')}
                            onMouseLeave={(e) => btnHoverColor(e, attributes, 'mouseLeave')}
                            className="latepoint-book-button wp-block-button__link">
                            {attributes.button_caption}
                        </LatepointBookButton>
                    </div>
                </Resource>)
            return <ListOfResources className={`resources-columns-${attributes.columns}`}>{resources_items}</ListOfResources>
        } else {
            return <NoMatches>{__('No Items Matching', 'latepoint')}</NoMatches>
        }
    }

    const getGeneralTabs = () => {
        let tabs = [
            {name: 'settings', title: <div className="lb-tab-head"><SettingsIcon/>Settings</div>}
        ];
        if(!attributes.is_inherit){
            tabs.push({name: 'styles', title: <div className="lb-tab-head"><StylesIcon/>Button</div>});
            tabs.push({name: 'card_settings', title: <div className="lb-tab-head"><CardIcon/>Card</div>});
        }
        return tabs;
    }

    return (
        <div {...blockProps}>
            <InspectorControls>

                <TabPanel
                    className="lb-general-tabs"
                    activeClass="active-tab"
                    tabs={getGeneralTabs()}
                    onSelect={(tabName) => setActiveTab(tabName)}
                >
                    {(tab) => {
                        if (tab.name === 'settings') {
                            return (
                                <>
                                    <Panel>
                                        <PanelBody title="Button" initialOpen={true}>
                                            <TextControl
                                                label="Button Caption"
                                                value={attributes.button_caption || ''}
                                                onChange={(value) => setAttributes({button_caption: value})}
                                            />
                                            <ToggleControl
                                                label="Inherit From Theme"
                                                checked={attributes.is_inherit}
                                                onChange={(value) => setAttributes({is_inherit: value})}
                                            />
                                        </PanelBody>
                                    </Panel>
                                    <Panel>
                                        <PanelBody title="Settings" initialOpen={false}>
                                            <SelectControl
                                                label={__('Resource Type', 'latepoint')}
                                                onChange={(value) => setAttributes({items: value})}
                                                value={attributes.items}
                                                options={[
                                                    {value: 'services', label: __('Services', 'latepoint')},
                                                    {value: 'agents', label: __('Agents', 'latepoint')},
                                                    {value: 'locations', label: __('Locations', 'latepoint')},
                                                    {value: 'bundles', label: __('Bundles', 'latepoint')}]}
                                            />
                                            <SelectControl
                                                label={__('Number of columns', 'latepoint')}
                                                onChange={(value) => setAttributes({columns: value})}
                                                value={attributes.columns ?? '4'}
                                                options={[
                                                    {label: __('One', 'latepoint'), value: '1'},
                                                    {label: __('Two', 'latepoint'), value: '2'},
                                                    {label: __('Three', 'latepoint'), value: '3'},
                                                    {label: __('Four', 'latepoint'), value: '4'},
                                                    {label: __('Five', 'latepoint'), value: '5'}]}
                                            />

                                            {['services', 'bundles'].includes(attributes.items) && (
                                                <>
                                                    {attributes.items === 'services' && (
                                                        <ToggleControl
                                                            label="Hide Image"
                                                            checked={attributes.hide_image}
                                                            onChange={(value) => setAttributes({hide_image: value})}
                                                        />
                                                    )}
                                                    <ToggleControl
                                                        label="Hide Price"
                                                        checked={attributes.hide_price}
                                                        onChange={(value) => setAttributes({hide_price: value})}
                                                    />
                                                    <ToggleControl
                                                        label="Hide Description"
                                                        checked={attributes.hide_description}
                                                        onChange={(value) => setAttributes({hide_description: value})}
                                                    />
                                                </>
                                            )}
                                        </PanelBody>
                                    </Panel>
                                    <Panel>
                                        <PanelBody title="Booking Form Settings" initialOpen={false}>
                                            <ToggleControl
                                                label="Hide Summary Panel"
                                                checked={attributes.hide_summary}
                                                onChange={(value) => setAttributes({hide_summary: value})}
                                            />
                                            <ToggleControl
                                                label="Hide Side Panel"
                                                checked={attributes.hide_side_panel}
                                                onChange={(value) => setAttributes({hide_side_panel: value})}
                                            />
                                        </PanelBody>
                                    </Panel>
                                    <Panel>
                                        <PanelBody title="Step Settings" initialOpen={false}>
                                            { attributes.items != 'agents' &&
                                                <SelectControl
                                                    value={attributes.selected_agent}
                                                    label={__('Preselected Agent', 'latepoint')}
                                                    onChange={(value) => setAttributes({selected_agent: value})}
                                                    options={latepoint_helper.selected_agents_options}
                                                />
                                            }
                                            { !['services', 'bundles'].includes(attributes.items) &&
                                                <SelectControl
                                                    value={attributes.selected_service}
                                                    label={__('Preselected Service', 'latepoint')}
                                                    onChange={(value) => setAttributes({selected_service: value})}
                                                    options={latepoint_helper.selected_services_options}
                                                />
                                            }
                                            { !['services', 'bundles'].includes(attributes.items) &&
                                                <SelectControl
                                                    value={attributes.selected_service_category}
                                                    label={__('Preselected Service Category', 'latepoint')}
                                                    onChange={(value) => setAttributes({selected_service_category: value})}
                                                    options={latepoint_helper.selected_service_categories_options}
                                                />
                                            }
                                            { !['services', 'bundles'].includes(attributes.items) &&
                                                <SelectControl
                                                    value={attributes.selected_bundle}
                                                    label={__('Preselected Bundle', 'latepoint')}
                                                    onChange={(value) => setAttributes({selected_bundle: value})}
                                                    options={latepoint_helper.selected_bundles_options}
                                                />
                                            }
                                            {attributes.items != 'locations' &&
                                                <SelectControl
                                                    value={attributes.selected_location}
                                                    label={__('Preselected Location', 'latepoint')}
                                                    onChange={(value) => setAttributes({selected_location: value})}
                                                    options={latepoint_helper.selected_locations_options}
                                                />
                                            }

                                            <TextControl
                                                label={__('Preselected Booking Start Date', 'latepoint')}
                                                value={attributes.selected_start_date || ''}
                                                placeholder="YYYY-MM-DD"
                                                onChange={(value) => setAttributes({selected_start_date: value})}
                                            />

                                            <TextControl
                                                label={__('Preselected Booking Start Time', 'latepoint')}
                                                value={attributes.selected_start_time || ''}
                                                placeholder="Minutes"
                                                onChange={(value) => setAttributes({selected_start_time: value})}
                                            />

                                            <TextControl
                                                label={__('Preselected Duration', 'latepoint')}
                                                value={attributes.selected_duration || ''}
                                                placeholder="Minutes"
                                                onChange={(value) => setAttributes({selected_duration: value})}
                                            />

                                            <TextControl
                                                label={__('Preselected Total Attendees', 'latepoint')}
                                                value={attributes.selected_total_attendees || ''}
                                                placeholder="Number"
                                                onChange={(value) => setAttributes({selected_total_attendees: value})}
                                            />
                                        </PanelBody>
                                    </Panel>
                                    <Panel>
                                        <PanelBody title="Items Settings" initialOpen={false}>
                                            <TextControl
                                                label="Max Number of Items Shown"
                                                value={attributes.limit || ''}
                                                onChange={(value) => setAttributes({limit: value})}
                                            />
                                            <TextControl
                                                label="Show Selected Items"
                                                placeholder="Comma separated item IDs"
                                                value={attributes.item_ids || ''}
                                                onChange={(value) => setAttributes({item_ids: value})}
                                            />

                                            {attributes.items != 'bundles' &&
                                                <TextControl
                                                    label="Show Selected Categories"
                                                    placeholder="Comma separated category IDs"
                                                    value={attributes.group_ids || ''}
                                                    onChange={(value) => setAttributes({group_ids: value})}
                                                />
                                            }
                                        </PanelBody>
                                    </Panel>
                                    <Panel>
                                        <PanelBody title="Other Settings" initialOpen={false}>
                                            <TextControl
                                                label="Source ID"
                                                value={attributes.source_id || ''}
                                                onChange={(value) => setAttributes({source_id: value})}
                                            />
                                            <TextControl
                                                label="Calendar Start Date"
                                                value={attributes.calendar_start_date || ''}
                                                placeholder="YYYY-MM-DD"
                                                onChange={(value) => setAttributes({calendar_start_date: value})}
                                            />
                                            {!['services', 'bundles'].includes(attributes.items) &&
                                                <TextControl
                                                    label="Show Services"
                                                    placeholder="Comma separated service IDs"
                                                    value={attributes.show_services || ''}
                                                    onChange={(value) => setAttributes({show_services: value})}
                                                />
                                            }
                                            {!['services', 'bundles'].includes(attributes.items) &&
                                                <TextControl
                                                    label="Show Service Categories"
                                                    placeholder="Comma separated category IDs"
                                                    value={attributes.show_service_categories || ''}
                                                    onChange={(value) => setAttributes({show_service_categories: value})}
                                                />
                                            }
                                            {attributes.items != 'agents' &&
                                                <TextControl
                                                    label="Show Agents"
                                                    placeholder="Comma separated agent IDs"
                                                    value={attributes.show_agents || ''}
                                                    onChange={(value) => setAttributes({show_agents: value})}
                                                />
                                            }
                                            {attributes.items != 'locations' &&
                                                <TextControl
                                                    label="Show Locations"
                                                    placeholder="Comma separated location IDs"
                                                    value={attributes.show_locations || ''}
                                                    onChange={(value) => setAttributes({show_locations: value})}
                                                />
                                            }
                                        </PanelBody>
                                    </Panel>
                                </>
                            );
                        }
                        if (tab.name === 'styles') {
                            return (
                                <>
                                    {!attributes.is_inherit && (
                                        <Panel>
                                            <PanelBody>
                                                <PaddingBoxControl
                                                    label={__('Padding', 'latepoint')}
                                                    paddingAttribute="padding"
                                                    attributes={attributes}
                                                    setAttributes={setAttributes}
                                                />
                                            </PanelBody>
                                            <PanelBody title="Color" initialOpen={false}>
                                                <TabPanel
                                                    className="lb-tabs"
                                                    activeClass="active-tab"
                                                    tabs={[
                                                        {name: 'tab-normal', title: 'Normal',},
                                                        {name: 'tab-hover', title: 'Hover',},
                                                    ]}
                                                >
                                                    {(tab) => {
                                                        if (tab.name === 'tab-normal') {
                                                            return (
                                                                <>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="button_bg_color"
                                                                            label={__('Background Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="button_text_color"
                                                                            label={__('Text Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                </>
                                                            );
                                                        }
                                                        if (tab.name === 'tab-hover') {
                                                            return (
                                                                <>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="bg_color_hover"
                                                                            label={__('Background Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="text_color_hover"
                                                                            label={__('Text Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                </>
                                                            );
                                                        }
                                                    }}
                                                </TabPanel>
                                            </PanelBody>
                                            <PanelBody title="Text" initialOpen={false}>
                                                <TypographyControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr="button_font_size"></TypographyControl>
                                            </PanelBody>
                                            <PanelBody title="Border" initialOpen={false}>
                                                <BorderControl attributes={attributes} setAttributes={setAttributes} borderRadiusAttr="button_border_radius"></BorderControl>
                                            </PanelBody>
                                        </Panel>
                                    )}
                                </>
                            );
                        }
                        if (tab.name === 'card_settings') {
                            return (
                                <>
                                    <Panel>
                                        <PanelBody title="Color" initialOpen={false}>
                                            <TabPanel
                                                className="lb-tabs"
                                                activeClass="active-tab"
                                                tabs={[
                                                    {name: 'tab-normal', title: 'Normal',},
                                                    {name: 'tab-hover', title: 'Hover',},
                                                ]}
                                            >
                                                {(tab) => {
                                                    if (tab.name === 'tab-normal') {
                                                        return (
                                                            <>
                                                                <PanelRow>
                                                                    <ColorSelectorControl
                                                                        attributes={attributes}
                                                                        setAttributes={setAttributes}
                                                                        colorAttribute="card_bg_color"
                                                                        label={__('Background Color', 'latepoint')}
                                                                    ></ColorSelectorControl>
                                                                </PanelRow>
                                                                <PanelRow>
                                                                    <ColorSelectorControl
                                                                        attributes={attributes}
                                                                        setAttributes={setAttributes}
                                                                        colorAttribute="card_text_color"
                                                                        label={__('Title Color', 'latepoint')}
                                                                    ></ColorSelectorControl>
                                                                </PanelRow>
                                                                {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_price) && (
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_price_color"
                                                                            label={__('Price Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                )}
                                                                {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_description) && (
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_descr_color"
                                                                            label={__('Description Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                )}
                                                            </>
                                                        );
                                                    }
                                                    if (tab.name === 'tab-hover') {
                                                        return (
                                                            <>
                                                                <PanelRow>
                                                                    <ColorSelectorControl
                                                                        attributes={attributes}
                                                                        setAttributes={setAttributes}
                                                                        colorAttribute="card_bg_color_hover"
                                                                        label={__('Background Color', 'latepoint')}
                                                                    ></ColorSelectorControl>
                                                                </PanelRow>
                                                                <PanelRow>
                                                                    <ColorSelectorControl
                                                                        attributes={attributes}
                                                                        setAttributes={setAttributes}
                                                                        colorAttribute="card_text_color_hover"
                                                                        label={__('Title Color', 'latepoint')}
                                                                    ></ColorSelectorControl>
                                                                </PanelRow>
                                                                {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_price) && (
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_price_color_hover"
                                                                            label={__('Price Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                )}
                                                                {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_description) && (
                                                                    <PanelRow>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_descr_color_hover"
                                                                            label={__('Description Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </PanelRow>
                                                                )}
                                                            </>
                                                        );
                                                    }
                                                }}
                                            </TabPanel>
                                        </PanelBody>

                                        <PanelBody title="Title" initialOpen={false}>
                                            <FontSizeControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr="title_font_size"></FontSizeControl>
                                        </PanelBody>

                                        {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_price) && (
                                            <PanelBody title="Price" initialOpen={false}>
                                                <FontSizeControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr="price_font_size"></FontSizeControl>
                                            </PanelBody>
                                        )}

                                        {(['services', 'bundles'].includes(attributes.items) && !attributes.hide_description) && (
                                            <PanelBody title="Description" initialOpen={false}>
                                                <FontSizeControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr="descr_font_size"></FontSizeControl>
                                            </PanelBody>
                                        )}


                                        <PanelBody title="Box Shadow" initialOpen={false}>
                                            <BoxShadowControl
                                                shadowAttribute="card_box_shadow"
                                                attributes={attributes}
                                                setAttributes={setAttributes}
                                            />
                                        </PanelBody>
                                        <PanelBody title="Border" initialOpen={false}>
                                            <UnitControl
                                                label={__('Border Radius')}
                                                className="latepoint-control-two-columns"
                                                onChange={(border_radius) => setAttributes({card_border_radius: border_radius})}
                                                units={[
                                                    {value: 'px', label: 'px', default: 0},
                                                    {value: '%', label: '%', default: 10},
                                                    {value: 'em', label: 'em', default: 0},
                                                ]}
                                                value={attributes.card_border_radius}
                                            />

                                            <SelectControl
                                                label={__('Style', 'latepoint')}
                                                value={attributes.card_border_style}
                                                className="latepoint-control-two-columns"
                                                options={[
                                                    {label: __('Default', 'latepoint'), value: 'default'},
                                                    {label: __('None', 'latepoint'), value: 'none'},
                                                    {label: __('Solid', 'latepoint'), value: 'solid'},
                                                    {label: __('Dotted', 'latepoint'), value: 'dotted'},
                                                    {label: __('Dashed', 'latepoint'), value: 'dashed'},
                                                ]}
                                                onChange={(border_style) => setAttributes({card_border_style: border_style})}
                                            />

                                            {attributes.card_border_style !== 'default' && attributes.card_border_style !== 'none' && (
                                                <>
                                                    <UnitControl
                                                        label={__('Border Width')}
                                                        className="latepoint-control-two-columns"
                                                        onChange={(value) => {
                                                            setAttributes({card_border_width: value})
                                                        }}
                                                        units={[
                                                            {value: 'px', label: 'px', default: 0},
                                                            {value: '%', label: '%', default: 10},
                                                            {value: 'em', label: 'em', default: 0},
                                                        ]}
                                                        value={attributes.card_border_width}
                                                    />
                                                    <TabPanel
                                                        className="lb-tabs"
                                                        activeClass="active-tab"
                                                        tabs={[
                                                            {name: 'tab-normal', title: 'Normal',},
                                                            {name: 'tab-hover', title: 'Hover',},
                                                        ]}
                                                    >
                                                        {(tab) => {
                                                            if (tab.name === 'tab-normal') {
                                                                return (
                                                                    <>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_border_color"
                                                                            label={__('Border Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </>
                                                                );
                                                            }
                                                            if (tab.name === 'tab-hover') {
                                                                return (
                                                                    <>
                                                                        <ColorSelectorControl
                                                                            attributes={attributes}
                                                                            setAttributes={setAttributes}
                                                                            colorAttribute="card_border_color_hover"
                                                                            label={__('Border Color', 'latepoint')}
                                                                        ></ColorSelectorControl>
                                                                    </>
                                                                );
                                                            }
                                                        }}
                                                    </TabPanel>
                                                </>
                                            )}
                                        </PanelBody>
                                        <PanelBody title="Spacing" initialOpen={false}>
                                            <PaddingBoxControl
                                                label={__('Card Padding', 'latepoint')}
                                                paddingAttribute="card_padding" attributes={attributes}
                                                setAttributes={setAttributes}></PaddingBoxControl>
                                        </PanelBody>
                                    </Panel>
                                </>
                            );
                        }
                    }}
                </TabPanel>
                <Panel>
                    <Separator></Separator>
                </Panel>
            </InspectorControls>

            <ListOfResourcesWrapper>
                {renderResources()}
            </ListOfResourcesWrapper>
        </div>
    );
}

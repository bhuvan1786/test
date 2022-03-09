<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Api\Data;

/**
 * CMS blog interface.
 * @api
 * @since 100.0.2
 */
interface BlogInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BLOG_ID                  = 'blog_id';
    const IDENTIFIER               = 'identifier';
    const TITLE                    = 'title';
    const BLOG_LAYOUT              = 'blog_layout';
    const META_TITLE               = 'meta_title';
    const META_KEYWORDS            = 'meta_keywords';
    const META_DESCRIPTION         = 'meta_description';
    const CONTENT_HEADING          = 'content_heading';
    const CONTENT                  = 'content';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const SORT_ORDER               = 'sort_order';
    const LAYOUT_UPDATE_XML        = 'layout_update_xml';
    const CUSTOM_THEME             = 'custom_theme';
    const CUSTOM_ROOT_TEMPLATE     = 'custom_root_template';
    const CUSTOM_LAYOUT_UPDATE_XML = 'custom_layout_update_xml';
    const CUSTOM_THEME_FROM        = 'custom_theme_from';
    const CUSTOM_THEME_TO          = 'custom_theme_to';
    const IS_ACTIVE                = 'is_active';
    /**#@-*/
 /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

}

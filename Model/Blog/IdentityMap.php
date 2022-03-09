<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Blog\Model\Blog;

use Custom\Blog\Model\Blog;

/**
 * Identity map of loaded blogs.
 */
class IdentityMap
{
    /**
     * @var Blog[]
     */
    private $blogs = [];

    /**
     * Add a blog to the list.
     *
     * @param Blog $blog
     * @throws \InvalidArgumentException When blog doesn't have an ID.
     * @return void
     */
    public function add(Blog $blog): void
    {
        if (!$blog->getId()) {
            throw new \InvalidArgumentException('Cannot add non-persisted blog to identity map');
        }
        $this->blogs[$blog->getId()] = $blog;
    }

    /**
     * Find a loaded Blog by ID.
     *
     * @param int $id
     * @return Blog|null
     */
    public function get(int $id): ?Blog
    {
        if (array_key_exists($id, $this->blogs)) {
            return $this->blogs[$id];
        }

        return null;
    }

    /**
     * Remove the blog from the list.
     *
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        unset($this->blogs[$id]);
    }

    /**
     * Clear the list.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->blogs = [];
    }
}

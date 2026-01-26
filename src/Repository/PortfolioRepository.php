<?php

declare(strict_types=1);

namespace NiceSimpleVp\Repository;

use NiceSimpleVp\CustomPostType\Taxonomy;

class PortfolioRepository implements Collection, Taxonomy
{
    private const string POST_TYPE = 'portfolio';

    private const string TAXONOMY = 'collections';

    /**
     * @return \WP_Post[]|int[]
     */
    public function getAll(): array
    {
        return get_posts([
           'post_type' => self::POST_TYPE,
           'post_status' => 'publish',
           'posts_per_page' => -1
       ]);
    }

    /**
     * @return \WP_Post[]|int[]
     */
    public function getFeatured(): array
    {
        return get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                 [
                     'key'   => '_featured_project',
                     'value' => 'true',
                 ]
            ]
        ]);
    }

    /**
     * @return \WP_Post[]|int[]
     */
    public function getByCollection(string $collection): array
    {
        $args = [
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'collections',
                    'field'    => 'slug',
                    'terms'    => $collection
                ]
            ]
        ];
        return get_posts($args);
    }

    public function getTaxonomyName(): string
    {
        return self::TAXONOMY;
    }
}
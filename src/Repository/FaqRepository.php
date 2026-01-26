<?php

declare(strict_types=1);

namespace NiceSimpleVp\Repository;

class FaqRepository implements Collection
{
    private const string POST_TYPE = 'faq';

//    private const string TAXONOMY = 'faq-collections';

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
}
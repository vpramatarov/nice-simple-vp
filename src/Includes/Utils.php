<?php

declare(strict_types=1);

namespace NiceSimpleVp\Includes;


class Utils
{

    public static function dumpToFile(mixed $data, ?string $file = null): void
    {
        if (empty($file) || !file_exists($file)) {
            $file = ABSPATH. 'dump_log.txt'; // fallback to root dir
        }

        $debug_data = var_export($data, true).PHP_EOL;
        file_put_contents($file, $debug_data, FILE_APPEND | LOCK_EX);
    }

    
    public static function dd(mixed $data): void
    {
        echo '<pre>';
        print_r($data);
        die;
    }

    /**
     * @param array<string, mixed> $context
     * @return \WP_Term[]
     */
    public static function getTermsForPostByTaxonomy(int $id, string $taxonomy, array $context = []): array
    {
        $terms = get_the_terms( $id, $taxonomy );

        if (!$terms || is_wp_error( $terms )) {
            return [];
        }

        return $terms;
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function getTermsAsLinks(array $args): string
    {
        $terms = get_terms($args);

        if (empty($terms)) {
            return '';
        }

        $termLinks = [];
        $html = '';

        foreach ( $terms as $term ) {
            $termLinks[] = '<a href="' . esc_attr( get_term_link( $term->slug, $args['taxonomy'] ) ) . '">' . __( $term->name ) . '</a>';
        }

        $all_terms = implode(', ', $termLinks);
        $html .= '<span class="collections">' . __( $all_terms ) . '</span>';

        return $html;
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function getTermsListAsButtons(array $args): string
    {
        $args['fields'] = 'names'; // override
        $terms = get_terms($args);

        if (empty($terms)) {
            return '';
        }

        $termBtns = [];
        $html = '';
        $termBtns[] = '<button class="btn active" data-filter="all">' . __('All', 'nice-simple-vp') .'</button>';

        foreach ( $terms as $term ) {
            $termBtns[] = '<button class="btn" data-filter="' . __( $term ) . '">' . __( $term ) . '</button>';
        }

        $all_terms = implode(' ', $termBtns);
        $html .= '<span class="collections">' . __( $all_terms ) . '</span>';

        return $html;
    }

    public static function getTermsAsLinksForTaxonomy(int $id, string $taxonomy): string
    {
        $terms = self::getTermsForPostByTaxonomy($id, $taxonomy);

        if (empty($terms)) {
            return '';
        }

        $termLinks = [];
        $html = '';

        foreach ( $terms as $term ) {
            $termLinks[] = '<a href="' . esc_attr( get_term_link( $term->slug, $taxonomy ) ) . '">' . __( $term->name ) . '</a>';
        }

        $all_terms = implode(', ', $termLinks);
        $html .= '<span class="category-tag">' . __( $all_terms ) . '</span>';

        return $html;
    }
}
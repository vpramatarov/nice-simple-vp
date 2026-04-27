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

    public static function renderProjectCardHtml(\WP_Post $project, string $taxonomyName): string
    {
        setup_postdata( $project );
        $id = $project->ID;
        $title = get_the_title($id);
        $featuredImg = get_the_post_thumbnail_url($id, 'medium_large');
        $link = esc_url(get_permalink($id));
        $excerpt = get_the_excerpt($id);
        $collectionLinks = Utils::getTermsAsLinksForTaxonomy($id, $taxonomyName);
        $collections = strip_tags($collectionLinks);

        $html = '<div class="project-card" data-category="'.$collections.'">';
        $html .= '<div class="card-bg" style="background-image: url('. esc_url($featuredImg) . ');"></div>';
        $html .= '<div class="card-overlay">';
        $html .= '<div class="card-info">';
        $html .= $collectionLinks;
        $html .= '<h3 class="card-title"><a href="'. $link .'">' . $title . '</a></h3>';
        if (!empty($excerpt)) {
            $html .= '<p class="card-desc">' . $excerpt . '</p>';
        }
        $html .= '</div>'; // ./card-info
        $html .= '</div>'; // ./card-overlay
        $html .= '</div>'; // ./project-card

        return $html;
    }
}
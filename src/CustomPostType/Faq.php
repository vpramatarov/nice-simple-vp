<?php

declare(strict_types=1);

namespace NiceSimpleVp\CustomPostType;

final class Faq implements CustomPostType
{
    private const string NAME = 'faq';

    public function register(): void
    {
        $this->registerPostType();
        $this->registerTaxonomy();
    }

    private function registerPostType(): void
    {
        $labels = [
            'name'                  => __( 'FAQ', 'nice-simple-vp' ),
            'singular_name'         => __( 'FAQ', 'nice-simple-vp' ),
            'menu_name'             => __( 'FAQ', 'nice-simple-vp' ),
            'name_admin_bar'        => __( 'FAQ', 'nice-simple-vp' ),
            'archives'              => __( 'FAQ Archives', 'nice-simple-vp' ),
            'attributes'            => __( 'FAQ Attributes', 'nice-simple-vp' ),
            'parent_item_colon'     => __( 'Parent FAQ:', 'nice-simple-vp' ),
            'all_items'             => __( "All FAQ's", 'nice-simple-vp' ),
            'add_new_item'          => __( 'Add New FAQ', 'nice-simple-vp' ),
            'add_new'               => __( 'Add New', 'nice-simple-vp' ),
            'new_item'              => __( 'New FAQ', 'nice-simple-vp' ),
            'edit_item'             => __( 'Edit FAQ', 'nice-simple-vp' ),
            'update_item'           => __( 'Update FAQ', 'nice-simple-vp' ),
            'view_item'             => __( 'View FAQ', 'nice-simple-vp' ),
            'view_items'            => __( 'View FAQ', 'nice-simple-vp' ),
            'search_items'          => __( 'Search FAQ', 'nice-simple-vp' ),
            'not_found'             => __( 'Not found', 'nice-simple-vp' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'nice-simple-vp' ),
            'featured_image'        => __( 'Featured Image', 'nice-simple-vp' ),
            'set_featured_image'    => __( 'Set featured image', 'nice-simple-vp' ),
            'remove_featured_image' => __( 'Remove featured image', 'nice-simple-vp' ),
            'use_featured_image'    => __( 'Use as featured image', 'nice-simple-vp' ),
            'insert_into_item'      => __( 'Insert into item', 'nice-simple-vp' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'nice-simple-vp' ),
            'items_list'            => __( 'Items list', 'nice-simple-vp' ),
            'items_list_navigation' => __( 'Items list navigation', 'nice-simple-vp' ),
            'filter_items_list'     => __( 'Filter items list', 'nice-simple-vp' ),
        ];

        $args = [
            'label'                 => __( "FAQ's", 'nice-simple-vp' ),
            'description'           => __( "FAQ's", 'nice-simple-vp' ),
            'labels'                => $labels,
            'supports'              => ['title', 'editor', 'page-attributes'],
            'taxonomies'            => ['faq-collections'],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true
        ];

        register_post_type( self::NAME, $args );
    }

    private function registerTaxonomy(): void
    {
        $tax_labels = [
            'name' => __( 'FAQ Collections', 'nice-simple-vp' ),
            'singular_name' => __( 'Collection', 'nice-simple-vp' ),
            'search_items' =>  __( 'Search Collections', 'nice-simple-vp' ),
            'all_items' => __( 'All Collections', 'nice-simple-vp' ),
            'parent_item' => __( 'Parent Collection', 'nice-simple-vp' ),
            'parent_item_colon' => __( 'Parent Collection:', 'nice-simple-vp' ),
            'edit_item' => __( 'Edit Collection', 'nice-simple-vp' ),
            'update_item' => __( 'Update Collection', 'nice-simple-vp' ),
            'add_new_item' => __( 'Add New Collection', 'nice-simple-vp' ),
            'new_item_name' => __( 'New Collection Name', 'nice-simple-vp' ),
            'menu_name' => __( 'Collections', 'nice-simple-vp' ),
        ];

        // Now register the taxonomy
        register_taxonomy('faq-collections', [self::NAME], [
            'hierarchical' => true,
            'labels' => $tax_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => false,
            'rewrite' => [ 'slug' => 'faq-collection' ],
        ]);
    }
}
<?php

declare(strict_types=1);

namespace NiceSimpleVp\CustomPostType;

final class Portfolio implements CustomPostType, Metaboxes
{
    private const string NAME = 'portfolio';

    private array $metaboxes = [];

    public function register(): void
    {
        if (empty($this->metaboxes)) {
            $this->setMetaboxesData();
        }

        $this->registerPostType();
        $this->registerTaxonomy();
        $this->actions();
    }

    public function addMetaBoxes(): void
    {
        foreach($this->metaboxes as $metabox) {
            add_meta_box(
                $metabox['metabox_id'],
                $metabox['title'],
                $metabox['callback_fn'],
                $metabox['screen'],
                $metabox['context'],
                $metabox['priority'],
                $metabox['callback_args']
            );
        }
    }

    /**
     * When the post is saved, saves our custom data.
     */
    public function saveMetaboxesData(int $postId ): void
    {
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( !isset( $_POST['post_type'] ) ) {
            return;
        }

        if ( self::NAME !== $_POST['post_type'] ) {
            return;
        }

        if ( !current_user_can( 'edit_post', $postId ) ) {
            return;
        }

        // check if there was a multisite switch before
        if ( is_multisite() && ms_is_switched() ) {
            return;
        }

        // Check if nonce's is set.
        $nonces = array_column($this->metaboxes, 'nonce');
        $postMetaKeys = array_column($this->metaboxes, 'metabox_id');

        // check if nonces are set
        if ( !empty( array_diff( $nonces, array_keys($_POST) ) ) ){
            return;
        }

        // Verify that the nonce's are valid.
        foreach ($nonces as $nonce) {
            if (!isset($_POST[$nonce])) {
                return;
            }

            if ( !wp_verify_nonce( $_POST[$nonce], $nonce ) ) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */
        // Make sure that it is set.
        if ( !empty( array_diff( $postMetaKeys, array_keys($_POST) ) ) ){
            return;
        }

        // Sanitize user input.
        foreach ($this->metaboxes as $meta) {
            $metaPostKey = $meta['metabox_id'];

            if (!array_key_exists($metaPostKey, $_POST)) {
                continue;
            }

            $value = $_POST[$metaPostKey];
            $field = $meta['input']['field'] ?? null;

            if ($field === 'textarea') {
                $metaValue = sanitize_textarea_field( $value );
            } else {
                $metaValue = sanitize_text_field( $value );
            }

            update_post_meta( $postId, $meta['meta_key'], $metaValue );
        }
    }

    /* Metaboxes callback functions */
    public function makeProjectFeatured(\WP_Post $post ): void
    {
        $settings = $this->metaboxes['featured'];
        $nonce = $settings['nonce'];
        $metaKey = $settings['meta_key'];
        $value = get_post_meta( $post->ID, $metaKey, true );
        $name = $settings['metabox_id'];
        $title = $settings['title'];
        $inputType = $settings['input']['type'] ?? 'text';
        $style = $settings['input']['options']['style'] ?? '';
        $help = $settings['input']['help'] ?? null;
        $checked = !empty(esc_attr($value)) ? 'checked' : '';
        wp_nonce_field( $nonce, $nonce );

        printf('<input type="hidden" name="%s" value="">', $settings['metabox_id']); // default state
        printf(
            '<label class="screen-reader-text" for="%1$s">%4$s</label>
                    <input type="%3$s" name="%2$s" id="%1$s" value="true" style="%6$s" %5$s>
                    <p>%7$s</p>',
            $metaKey,
            $name,
            $inputType,
            $title,
            $checked,
            $style,
            $help
        );
    }

    /**
     * @param string[] $columns
     * @return string[]
     */
    public function setCustomColumns(array $columns): array
    {
        $newColumns = [];
        $newColumns['cb'] = $columns['cb'];
        $newColumns['ns_avatar'] = __('Photo', 'nice-simple-vp'); // Featured Image
        $newColumns['title'] = __('Project Name', 'nice-simple-vp');
        $newColumns['ns_featured_project'] = __('Is Featured', 'nice-simple-vp');
        $newColumns['taxonomy-collections'] = $columns['taxonomy-collections'];
        $newColumns['author'] = $columns['author'];
        $newColumns['date'] = $columns['date'];

        return $newColumns;
    }

    public function renderCustomColumns(string $column, int $post_id): void
    {
        if ($column === 'ns_avatar') {
            if (has_post_thumbnail($post_id)) {
                $styles = 'width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;';
                echo get_the_post_thumbnail($post_id, [40, 40], ['style' => $styles]);
            } else {
                echo '<span class="dashicons dashicons-admin-users" style="font-size: 30px; color: #ccc; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"></span>';
            }
        }

        if ($column === 'ns_featured_project') {
            $isFeatured = get_post_meta($post_id, '_featured_project', true);
            if ($isFeatured) {
                echo '<span class="dashicons dashicons-star-filled"></span>';
            } else {
                echo '<span class="dashicons dashicons-star-empty"></span>';
            }
        }
    }

    private function registerPostType(): void
    {
        $labels = [
            'name'                  => __( 'Portfolios', 'nice-simple-vp' ),
            'singular_name'         => __( 'Portfolio', 'nice-simple-vp' ),
            'menu_name'             => __( 'Portfolios', 'nice-simple-vp' ),
            'name_admin_bar'        => __( 'Portfolio', 'nice-simple-vp' ),
            'archives'              => __( 'Portfolio Archives', 'nice-simple-vp' ),
            'attributes'            => __( 'Portfolio Attributes', 'nice-simple-vp' ),
            'parent_item_colon'     => __( 'Parent Portfolio:', 'nice-simple-vp' ),
            'all_items'             => __( 'All Portfolios', 'nice-simple-vp' ),
            'add_new_item'          => __( 'Add New Portfolio', 'nice-simple-vp' ),
            'add_new'               => __( 'Add New', 'nice-simple-vp' ),
            'new_item'              => __( 'New Portfolio', 'nice-simple-vp' ),
            'edit_item'             => __( 'Edit Portfolio', 'nice-simple-vp' ),
            'update_item'           => __( 'Update Portfolio', 'nice-simple-vp' ),
            'view_item'             => __( 'View Portfolio', 'nice-simple-vp' ),
            'view_items'            => __( 'View Portfolios', 'nice-simple-vp' ),
            'search_items'          => __( 'Search Portfolio', 'nice-simple-vp' ),
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
            'label'                 => __( 'portfolios', 'nice-simple-vp' ),
            'description'           => __( 'User portfolios', 'nice-simple-vp' ),
            'labels'                => $labels,
            'supports'              => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
            'taxonomies'            => [ 'collection' ],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true
        ];
        register_post_type( self::NAME, $args );
    }

    private function registerTaxonomy(): void
    {
        $tax_labels = [
            'name' => __( 'Collections', 'nice-simple-vp' ),
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
        register_taxonomy('collections', [self::NAME], [
            'hierarchical' => true,
            'labels' => $tax_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'portfolio-collection' ],
        ]);
    }

    private function setMetaboxesData(): void
    {
        $this->metaboxes = [
            'featured' => [
                'metabox_id' => 'meta_featured_project',
                'meta_key' => '_featured_project',
                'title' => __( 'Make a Project Featured', 'nice-simple-vp' ),
                'callback_fn' => [$this , 'makeProjectFeatured'],
                'screen' => self::NAME,
                'context' => 'side', // normal, advanced, side
                'priority' => 'high', // default
                'callback_args' => null,
                'nonce' => '_make_project_featured_nonce',
                'input' => [
                    'field' => 'input',
                    'type' => 'checkbox',
                    'options' => null,
                    'help' => __('[Optional]', 'nice-simple-vp'). ' ' . __( 'Make a Project Featured', 'nice-simple-vp' ) . '.'
                ]
            ]
        ];
    }

    private function actions(): void
    {
        // --- ADMIN COLUMNS ---
        add_filter('manage_portfolio_posts_columns', [$this, 'setCustomColumns']);
        add_action('manage_portfolio_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }
}

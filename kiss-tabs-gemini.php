<?php
/**
 * Plugin Name:       KISS Tabs
 * Plugin URI:        https://kissplugins.com/
 * Description:       Creates a 'kiss_tabs' CPT to render tabbed content via a shortcode. Compatible with HTML, JS, and other shortcodes.
 * Version:           1.0.5
 * Author:            KISS Plugins
 * Author URI:        https://kissplugins.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kiss-tabs
 *
 * Changelog:
 * 1.0.5  - Moved kiss-tabs.css and kiss-tabs.js to the plugin root folder (out of /assets).
 * - Added comment regarding Font Awesome enqueueing if already loaded by theme/another plugin.
 * 1.0.4  - Changed edit icon from Dashicon to Font Awesome (fas fa-pencil-alt). Enqueued Font Awesome library.
 * 1.0.3  - Added URL hash navigation: updates URL on tab click (#tab-N) and opens tab from URL hash on page load.
 * - Moved CSS to a static assets/kiss-tabs.css file instead of generating it in PHP.
 * 1.0.2  - Added a front-end "Edit" pencil icon for logged-in users with edit permissions (using Dashicons).
 * 1.0.1  - Added full PHPDoc comment blocks to the class and all methods for improved code documentation.
 * 1.0.0  - Initial release. CPT, meta boxes, shortcode, and basic frontend tab functionality. Added admin links for "All Tabs" and a placeholder "Settings" page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * ===================================================================
 * KISS_Tabs Class
 * ===================================================================
 */
class KISS_Tabs {

    const VERSION     = '1.0.5';
    const CPT_SLUG    = 'kiss_tabs';
    const SHORTCODE   = 'kiss-tabs';
    const PLUGIN_SLUG = 'kiss-tabs-plugin';
    private static $shortcode_rendered = false;

    public function __construct() {
        $this->setup_hooks();
    }

    private function setup_hooks() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ $this, 'register_shortcode' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'add_plugin_links' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    public function register_cpt() {
        $labels = [
            'name'                  => _x( 'KISS Tabs', 'Post Type General Name', 'kiss-tabs' ),
            'singular_name'         => _x( 'KISS Tab', 'Post Type Singular Name', 'kiss-tabs' ),
            'menu_name'             => __( 'KISS Tabs', 'kiss-tabs' ),
            'all_items'             => __( 'All Tabs', 'kiss-tabs' ),
            'add_new_item'          => __( 'Add New Tab Set', 'kiss-tabs' ),
            'add_new'               => __( 'Add New', 'kiss-tabs' ),
            'new_item'              => __( 'New Tab Set', 'kiss-tabs' ),
            'edit_item'             => __( 'Edit Tab Set', 'kiss-tabs' ),
            // ... other labels can be kept brief or removed if not overriding defaults significantly
        ];
        $args = [
            'label'               => __( 'KISS Tab', 'kiss-tabs' ),
            'description'         => __( 'Custom Post Type for creating tabbed content.', 'kiss-tabs' ),
            'labels'              => $labels,
            'supports'            => [ 'title' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-table-row-after',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'rewrite'             => false,
        ];
        register_post_type( self::CPT_SLUG, $args );
    }

    public function add_plugin_links( $links ) {
        $all_tabs_link = '<a href="' . admin_url( 'edit.php?post_type=' . self::CPT_SLUG ) . '">' . __( 'All Tabs', 'kiss-tabs' ) . '</a>';
        array_unshift( $links, $all_tabs_link );
        $settings_link = '<a href="' . admin_url( 'admin.php?page=' . self::PLUGIN_SLUG ) . '">' . __( 'Settings', 'kiss-tabs' ) . '</a>';
        $links[] = $settings_link;
        return $links;
    }

    public function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=' . self::CPT_SLUG,
            __( 'Settings', 'kiss-tabs' ),
            __( 'Settings', 'kiss-tabs' ),
            'manage_options',
            self::PLUGIN_SLUG,
            [ $this, 'render_settings_page' ]
        );
    }

    public function render_settings_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'KISS Tabs Settings', 'kiss-tabs' ) . '</h1><h2>' . esc_html__( 'Coming Soon', 'kiss-tabs' ) . '</h2><p>' . esc_html__( 'Advanced settings will be available here soon.', 'kiss-tabs' ) . '</p></div>';
    }

    public function add_meta_boxes() {
        add_meta_box('kiss_tabs_content_meta_box', __( 'Tab Content', 'kiss-tabs' ), [ $this, 'render_meta_box_content' ], self::CPT_SLUG, 'normal', 'high');
        add_meta_box('kiss_tabs_shortcode_meta_box', __( 'Shortcode', 'kiss-tabs' ), function($post) {
            echo '<p>' . __( 'Use this shortcode:', 'kiss-tabs' ) . '</p><code>[' . self::SHORTCODE . ' id="' . $post->ID . '"]</code>';
            if (!empty($post->post_name)) { echo '<p style="margin-top:10px;">' . __( 'Or use name:', 'kiss-tabs' ) . '</p><code>[' . self::SHORTCODE . ' name="' . esc_attr($post->post_name) . '"]</code>'; }
        }, self::CPT_SLUG, 'side', 'low');
    }

    public function render_meta_box_content( $post ) {
        wp_nonce_field( 'kiss_tabs_save_meta_box_data', 'kiss_tabs_meta_box_nonce' );
        $tabs_data = get_post_meta( $post->ID, '_kiss_tabs_data', true );
        echo '<style>.kiss-tab-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 20px; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; background: #fdfdfd; } .kiss-tab-field label { font-weight: bold; } .kiss-tab-field input, .kiss-tab-field textarea { width: 100%; } .kiss-tab-field textarea { min-height: 150px; font-family: monospace; }</style>';
        for ( $i = 1; $i <= 4; $i++ ) {
            $title   = isset( $tabs_data[ $i ]['title'] ) ? esc_attr( $tabs_data[ $i ]['title'] ) : '';
            $content = isset( $tabs_data[ $i ]['content'] ) ? esc_textarea( $tabs_data[ $i ]['content'] ) : '';
            echo '<div class="kiss-tab-field"><label for="kiss_tab_title_' . $i . '">' . sprintf( esc_html__( 'Tab %d Title', 'kiss-tabs' ), $i ) . '</label><input type="text" id="kiss_tab_title_' . $i . '" name="kiss_tabs[' . $i . '][title]" value="' . $title . '" placeholder="' . esc_attr__( 'Enter tab title (leave blank to hide tab)', 'kiss-tabs' ) . '" /><label for="kiss_tab_content_' . $i . '">' . sprintf( esc_html__( 'Tab %d Content (HTML, Shortcode, JS)', 'kiss-tabs' ), $i ) . '</label><textarea id="kiss_tab_content_' . $i . '" name="kiss_tabs[' . $i . '][content]">' . $content . '</textarea></div>';
        }
        echo '<p class="description">' . esc_html__( 'Fill in titles/content. Empty titles hide tabs.', 'kiss-tabs' ) . '</p>';
    }

    public function save_meta_box_data( $post_id ) {
        if (!isset($_POST['kiss_tabs_meta_box_nonce']) || !wp_verify_nonce($_POST['kiss_tabs_meta_box_nonce'], 'kiss_tabs_save_meta_box_data') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id) || self::CPT_SLUG !== get_post_type($post_id) || !isset($_POST['kiss_tabs']) || !is_array($_POST['kiss_tabs'])) return;
        $sanitized_data = [];
        foreach ( $_POST['kiss_tabs'] as $index => $data ) {
            $index = intval($index);
            if ($index > 0 && $index <= 4) { $sanitized_data[$index] = ['title' => sanitize_text_field($data['title']), 'content' => trim($data['content'])]; }
        }
        update_post_meta( $post_id, '_kiss_tabs_data', $sanitized_data );
    }

    public function register_shortcode() {
        add_shortcode( self::SHORTCODE, [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( ['id' => '', 'name' => ''], $atts, self::SHORTCODE );
        $post_id = 0;
        if (!empty($atts['id'])) { $post_id = intval($atts['id']); }
        elseif (!empty($atts['name'])) { $post = get_page_by_path(sanitize_title($atts['name']), OBJECT, self::CPT_SLUG); if ($post) { $post_id = $post->ID; } }
        if (!$post_id || get_post_type($post_id) !== self::CPT_SLUG) { return current_user_can('manage_options') ? '<p style="color:red;">KISS Tabs: Invalid ID/name.</p>' : ''; }
        $tabs_data = get_post_meta( $post_id, '_kiss_tabs_data', true );
        $active_tabs = is_array($tabs_data) ? array_filter($tabs_data, fn($tab) => !empty($tab['title'])) : [];
        if (empty($active_tabs)) { return current_user_can('manage_options') ? '<p style="color:red;">KISS Tabs: No data found.</p>' : ''; }

        $edit_link_html = '';
        if ( current_user_can( 'edit_post', $post_id ) ) {
            $edit_url = get_edit_post_link( $post_id );
            $edit_link_html = '<a href="' . esc_url( $edit_url ) . '" class="kiss-tabs-edit-link" title="' . esc_attr__( 'Edit Tab Set', 'kiss-tabs' ) . '" target="_blank"><i class="fas fa-pencil-alt"></i></a>';
        }

        ob_start();
        $tab_render_index = 0;
        ?>
        <div class="kiss-tabs-wrapper" data-post-id="<?php echo esc_attr($post_id); ?>">
            <?php echo $edit_link_html; ?>
            <ul class="kiss-tabs-nav">
                <?php $first = true; foreach ( $active_tabs as $original_db_index => $tab ) : $tab_render_index++; ?>
                    <li class="kiss-tab-nav-item <?php echo $first ? 'active' : ''; ?>" 
                        data-tab-target="kiss-tab-content-<?php echo esc_attr($post_id) . '-' . esc_attr($original_db_index); ?>"
                        data-tab-index="<?php echo esc_attr($tab_render_index); ?>">
                        <?php echo esc_html( $tab['title'] ); ?>
                    </li>
                <?php $first = false; endforeach; ?>
            </ul>
            <div class="kiss-tabs-content">
                <?php $first = true; $tab_render_index = 0; foreach ( $active_tabs as $original_db_index => $tab ) : $tab_render_index++; ?>
                    <div id="kiss-tab-content-<?php echo esc_attr($post_id) . '-' . esc_attr($original_db_index); ?>" 
                         class="kiss-tab-pane <?php echo $first ? 'active' : ''; ?>"
                         data-tab-index="<?php echo esc_attr($tab_render_index); ?>">
                        <?php echo do_shortcode( $tab['content'] ); ?>
                    </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
        <?php
        self::$shortcode_rendered = true;
        return ob_get_clean();
    }

    public function enqueue_frontend_assets() {
        global $post;
        if ( is_a($post, 'WP_Post') && has_shortcode($post->post_content, self::SHORTCODE) || self::$shortcode_rendered ) {
             wp_enqueue_style(
                'kiss-tabs-style',
                plugin_dir_url( __FILE__ ) . 'kiss-tabs.css', // Path updated
                [],
                self::VERSION
            );

            // Enqueue Font Awesome (from CDN)
            // If your theme or another plugin already loads Font Awesome reliably,
            // you might consider commenting out the next line to prevent duplicate loading.
            // WordPress typically handles duplicate enqueues gracefully if the handle and src are identical.
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
                [],
                '5.15.4'
            );

            wp_enqueue_script(
                'kiss-tabs-script',
                plugin_dir_url( __FILE__ ) . 'kiss-tabs.js', // Path updated
                [ 'jquery' ],
                self::VERSION,
                true
            );
        }
    }
}

if ( class_exists( 'KISS_Tabs' ) ) {
    new KISS_Tabs();
}

/**
 * Runs on plugin activation.
 * Creates the JS file in the plugin root. CSS is now static.
 */
function kiss_tabs_activate() {
    $plugin_dir = plugin_dir_path( __FILE__ );
    // No longer creating /assets/ directory

    $js_file = $plugin_dir . 'kiss-tabs.js'; // Path updated
    $js_content = "
(function($) {
    'use strict';
    function activateTab(\$wrapper, tabIndex) {
        var \$navItems = \$wrapper.find('.kiss-tabs-nav > .kiss-tab-nav-item');
        var \$tabPanes = \$wrapper.find('.kiss-tabs-content > .kiss-tab-pane');
        var \$targetNavItem = \$navItems.filter('[data-tab-index=\"' + tabIndex + '\"]');
        var \$targetPane = \$tabPanes.filter('[data-tab-index=\"' + tabIndex + '\"]');
        if (\$targetNavItem.length && \$targetPane.length) {
            \$navItems.removeClass('active');
            \$tabPanes.removeClass('active');
            \$targetNavItem.addClass('active');
            \$targetPane.addClass('active');
            window.dispatchEvent(new Event('resize'));
            $(document).trigger('kiss:tab:shown', { newTab: '#' + \$targetPane.attr('id') });
            return true;
        }
        return false;
    }
    $(document).ready(function() {
        $('.kiss-tabs-wrapper').each(function() {
            var \$wrapper = $(this);
            \$wrapper.find('.kiss-tabs-nav > .kiss-tab-nav-item').on('click', function(e) {
                e.preventDefault();
                var \$this = $(this);
                if (\$this.hasClass('active')) return;
                var tabIndex = \$this.data('tab-index');
                if (activateTab(\$wrapper, tabIndex)) {
                    if (history.pushState) { history.pushState(null, null, '#tab-' + tabIndex); }
                    else { window.location.hash = 'tab-' + tabIndex; }
                }
            });
        });
        var hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            var tabIndexFromHash = parseInt(hash.substring(5), 10);
            if (!isNaN(tabIndexFromHash) && tabIndexFromHash > 0) {
                $('.kiss-tabs-wrapper').each(function() { activateTab($(this), tabIndexFromHash); });
            }
        }
    });
    $(window).on('hashchange', function() {
        var hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            var tabIndexFromHash = parseInt(hash.substring(5), 10);
            if (!isNaN(tabIndexFromHash) && tabIndexFromHash > 0) {
                $('.kiss-tabs-wrapper').each(function() {
                    if ($(this).find('.kiss-tabs-nav > .kiss-tab-nav-item.active').data('tab-index') != tabIndexFromHash) {
                        activateTab($(this), tabIndexFromHash);
                    }
                });
            }
        } else if (!hash) {
             $('.kiss-tabs-wrapper').each(function() { if ($(this).find('.kiss-tabs-nav > .kiss-tab-nav-item.active').data('tab-index') != 1) { activateTab($(this), 1); } });
        }
    });
})(jQuery);
    ";
    file_put_contents( $js_file, $js_content );
}
register_activation_hook( __FILE__, 'kiss_tabs_activate' );
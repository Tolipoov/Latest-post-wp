<?php
/*
Shortcode: Latest Posts Plugin
Description: Displays the latest posts using a shortcode.
Author: Tolipov Gulmurod
*/


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Latest_Posts_Plugin')) {
    class Latest_Posts_Plugin {
        
        public function __construct() {
            add_shortcode('latest_posts', [$this, 'display_latest_posts']);
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'settings_init']);
        }

        public function display_latest_posts($atts) {
            $atts = shortcode_atts([
                'number' => get_option('latest_posts_number', 9),
            ], $atts, 'latest_posts');

            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => intval($atts['number']),
            ];

            $query = new WP_Query($query_args);

            if ($query->have_posts()) {
                $output = '<ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    $output .= '<li>';
                     if (has_post_thumbnail()) :
                        '<div class="blog-post-thumb">';
                            '<a href="' . the_permalink() . '">';
                                 the_post_thumbnail('full', ['class' => 'img-responsive']); 
                            '</a>';
                        '</div>';
                    endif;
                    '<a href="' . get_permalink() . '">' . get_the_title() . '</a>
                    </li>';
                }
                $output .= '</ul>';
            } else {
                $this->log_error('No posts found.');
                $output = '<p>No posts found.</p>';
            }

            wp_reset_postdata();
            return $output;
        }

        public function add_admin_menu() {
            add_options_page(
                'Latest Posts Settings',
                'Latest Posts',
                'manage_options',
                'latest-posts-plugin',
                [$this, 'settings_page']
            );
        }

        public function settings_init() {
            register_setting('latest_posts_plugin', 'latest_posts_number');

            add_settings_section(
                'latest_posts_plugin_section',
                __('Latest Posts Settings', 'latest-posts-plugin'),
                null,
                'latest-posts-plugin'
            );

            add_settings_field(
                'latest_posts_number',
                __('Number of posts to display', 'latest-posts-plugin'),
                [$this, 'settings_number_render'],
                'latest-posts-plugin',
                'latest_posts_plugin_section'
            );
        }

        public function settings_number_render() {
            $value = get_option('latest_posts_number', 10);
            echo '<input type="number" name="latest_posts_number" value="' . esc_attr($value) . '" />';
        }

        public function settings_page() {
            ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('latest_posts_plugin');
                do_settings_sections('latest-posts-plugin');
                submit_button();
                ?>
            </form>
            <?php
        }

        private function log_error($message) {
            if (WP_DEBUG) {
                error_log($message);
            }
        }
    }

    new Latest_Posts_Plugin();
}
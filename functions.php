<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Copyright:         2023 by Jim Schwanda.
Description:       The Theme-Solutions framework serves as a base for custom theme implementation. The WordPress-Solutions plugin is required for the Theme-Solutions framework to run properly. 
Donate link:       https://www.usi2solve.com/donate/theme-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/variable-solutions/blob/master/LICENSE.md
Requires PHP:      5.6.25
Tested up to:      5.3.2
Text Domain:       usi-theme-solutions
Theme Name:        Theme-Solutions
Theme URI:         https://www.usi2solve.com/wordpress/theme-solutions
Version:           1.5.8
Warranty:          This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
// https://developer.wordpress.org/themes/basics/theme-functions/
// http://justintadlock.com/archives/2010/11/08/sidebars-in-wordpress
// http://wordpress.stackexchange.com/questions/26557/programmatically-add-widgets-to-sidebars/51086#51086
// https://code.tutsplus.com/customizing-your-wordpress-admin--wp-24941a

class USI_Theme_Solutions {

   const VERSION    = '1.5.8 (2023-06-20)';
   const NAME       = 'Theme-Solutions';
   const PREFIX     = 'usi-theme';
   const TEXTDOMAIN = 'usi-theme-solutions';

   static $home_url = null;
   static $options = null;
   static $option_name = USI_Theme_Solutions::PREFIX . '-options';
   static $options_post = null;
   static $robots = null;
   static $site_url = null;
   static $trim_urls_source = null;
   static $trim_urls_target = null;
   
   function __construct() {

      $defaults['jquery']['load']   = 'none';
      $defaults['jquery']['source'] = 'google';
      $defaults['updates']['allow_minor_auto_core_updates'] =
      $defaults['updates']['auto_update_translation']       = true;

      self::$options  = get_option(self::$option_name, $defaults);

      self::$home_url = home_url('/');
      self::$site_url = site_url('/');

      if (isset(self::$options['trim_urls'])) {
         $trim_urls = self::$options['trim_urls'];
         foreach ($trim_urls as $key => $value) {
            if (!empty($value)) {
               $tokens = explode(' ', $value);
               if (3 == count($tokens)) {
                  self::$trim_urls_source[] = $tokens[1];
                  self::$trim_urls_target[] = $tokens[2];
               }
            }
         }
      }

      if (!empty(self::$options['admin']['admin_maintanence_message'])) {
         self::display_maintenance_message(self::$options['admin']['admin_maintanence_message']);
      }

      $this->add_actions();
      $this->add_filters();
      $this->add_shortcodes();
      $this->add_support();
      $this->remove_actions();
      $this->remove_filters();
/*
      add_filter(
         'site_status_tests', 
         function (array $tests) {
            usi::log('$tests=', $tests);
            unset(
               $tests['async']['background_updates'],
               $tests['async']['page_cache'],
               $tests['direct']['theme_version'],
               $tests['direct']['caching_plugin']
            );
            return $tests;
         }, 
         10, 
         1
      );
*/
   } // __construct();

   function action_activated_plugin() {
      usi::log('plugin_install_errors=', ob_get_contents());
   } // action_activated_plugin();

   function action_admin_head() {
      if (!current_user_can('update_core')) {
         remove_action('admin_notices', 'update_nag', 3);
      }
      if (!empty(USI_Theme_Solutions::$options['search']['google_analytics']) && 
          !empty(USI_Theme_Solutions::$options['search']['google_analytics_head'])) {
          echo USI_Theme_Solutions::$options['search']['google_analytics'];
      }
   } // action_admin_head();
   
   function action_after_setup_theme() {
      if (is_admin()) {
         require_once get_template_directory() . '/usi-theme-solutions-settings.php';
      } // ENDIF is_admin();
   } // action_after_setup_theme();

   function action_wp_enqueue_scripts() {
      if (isset(self::$options['styles'])) {
         $styles = self::$options['styles'];
         foreach ($styles as $key => $value) {
            if (!empty($value)) {
               $tokens = explode(' ', $value);
               wp_enqueue_style(
                  $tokens[0], // Unique style sheet name;
                  $tokens[1], // Full URL of the style sheet, or path of style sheet relative to root;
                  null, // Array of registered style sheet handles this style sheet depends on;
                  (!empty($tokens[2]) && ('null' != $tokens[2])) ? $tokens[2] : null, // Style sheet version number;
                  !empty($tokens[3]) ? $tokens[3] : null // Media flag;
               );
            }
         }
      }
      if (!is_admin()) {
         if (!empty(self::$options['wp_head']['classic-theme-styles'])) {
            wp_deregister_style('classic-theme-styles');
            wp_dequeue_style('classic-theme-styles');
         }
         if (!empty(self::$options['wp_head']['global-styles'])) {
            wp_deregister_style('global-styles');
            wp_dequeue_style('global-styles');
         }
         if (!empty(self::$options['wp_head']['gutenberg_css'])) {
            wp_deregister_script('wp-block-library');
            wp_dequeue_style('wp-block-library');
         }
         if (!empty(self::$options['jquery']['load']) && ('none' != self::$options['jquery']['load'])) {
            $footer = ('footer' == self::$options['jquery']['load']);
            $version = self::$options['jquery']['version'];
            if ('google' == self::$options['jquery']['source']) {
               wp_deregister_script('jquery');  
               wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery.min.js', false, $version, $footer);  
            } else {
               if ($footer) {
                  wp_deregister_script('jquery');  
                  wp_register_script('jquery', includes_url('/js/jquery/jquery.js'), false, $version, true);
               }
            }
            wp_enqueue_script('jquery');
         }
      }
      if (isset(self::$options['scripts'])) {
         $scripts = self::$options['scripts'];
         foreach ($scripts as $key => $value) {
            if (!empty($value)) {
               $tokens = explode(' ', $value);
               wp_enqueue_script(
                  $tokens[0], // Unique script name;
                  $tokens[1], // Full URL of the script, or path of script relative to root;
                  null, // Array of registered script handles this script depends on;
                  (!empty($tokens[2]) && ('null' != $tokens[2])) ? $tokens[2] : null, // Script version number;
                  !empty($tokens[3]) ? $tokens[3] : false // Include in footer flag;
               );
            }
         }
      }
   } // action_wp_enqueue_scripts();

   function action_widgets_init() {
      if (isset(self::$options['widget_areas'])) {
         $options = self::$options['widget_areas'];
         $source = ['__', '_', '{i}', '{n}', 'null'];
         $target = ['_', ' ', '', PHP_EOL, ''];
         foreach ($options as $key => $value) {
            $tokens = explode(' ', $value);
            if (!empty($tokens[7])) $target[2] = str_repeat(' ', (int)$tokens[7]);
            register_sidebar(
               [
                  'id'   => !empty($tokens[0]) ? str_replace($source, $target, $tokens[0]) : '',
                  'name' => !empty($tokens[1]) ? str_replace($source, $target, $tokens[1]) : '',
                  'description'   => !empty($tokens[2]) ? str_replace($source, $target, $tokens[2]) : '',
                  'before_widget' => !empty($tokens[3]) ? str_replace($source, $target, $tokens[3]) : '',
                  'after_widget'  => !empty($tokens[4]) ? str_replace($source, $target, $tokens[4]) : '',
                  'before_title'  => !empty($tokens[5]) ? str_replace($source, $target, $tokens[5]) : '',
                  'after_title'   => !empty($tokens[6]) ? str_replace($source, $target, $tokens[6]) : '',
               ]
            );
         }
      }
   } // action_widgets_init();

   function action_wp_before_admin_bar_render() {
      $tokens  = explode('<|>', self::$options['admin']['admin_global_message']);
      $height  = !empty($tokens[0]) ? $tokens[0] : '';
      $style   = !empty($tokens[1]) ? $tokens[1] : '';
      $message = !empty($tokens[2]) ? $tokens[2] : '';
      echo 
         '<div id="usi-admin-message" style="' . $style . ' height:' . $height . 
         '; display:block; left:0; position:fixed; top:0; width:100%; z-index:99999;">' . $message . '</div>' . PHP_EOL .
         '<div id="usi-admin-spacer" style="display:block; height:' . $height . '; width:100%;"></div>' . PHP_EOL .
         '<script>jQuery("#usi-admin-spacer").insertBefore("#wpwrap");</script>' . PHP_EOL;
   } // action_wp_before_admin_bar_render();

   function action_wp_after_admin_bar_render() {
      $tokens  = explode('<|>', self::$options['admin']['admin_global_message']);
      $height  = !empty($tokens[0]) ? $tokens[0] : '';
      echo "<script>jQuery('#wpadminbar').css('top', '$height');</script>" . PHP_EOL;
   } // action_wp_after_admin_bar_render()

   function action_wp_footer() {
      if (!empty(self::$options['search']['google_analytics']) && empty(self::$options['search']['google_analytics_head'])) echo '    ' . self::$options['search']['google_analytics'];
      if (!empty(self::$options['miscellaneous']['log_error_get_last'])) {
         $error = error_get_last();
         if (!empty($error)) usi::log('error_get_last=', $error);
      }
   } // action_wp_footer();

   function action_wp_head_meta_tags() {

      echo '    <meta charset="' . get_bloginfo('charset') . '">' . PHP_EOL;
      if (isset(self::$options['meta_tags'])) {
         $options = self::$options['meta_tags'];
         foreach ($options as $key => $value) {
            if (!empty($value)) echo '    <meta name="' . $key . '" content="' . $value . '">' . PHP_EOL;
         }
      }

      global $post;
      if ($post) {
         USI_Theme_Solutions::$options_post = get_post_meta($post->ID, '_usi-theme-page', true);
         if (!empty(USI_Theme_Solutions::$options_post['hide'])) {
            unset(self::$robots['max-image-preview']);
            self::$robots['noindex']  = true;
            self::$robots['nofollow'] = true;
         }
      }
      if (!empty(self::$robots)) {
         $html   = '    <meta name="robots" content="';
         $spacer = null;
         foreach (self::$robots as $key => $value) {
            if ('max-image-preview' == $key) {
               $html  .= $spacer . 'max-image-preview:' . $value;
               $spacer = ', ';
            } else if ($value) {
               $html  .= $spacer . $key;
               $spacer = ', ';
            }
         }
         echo $html . '" />' . PHP_EOL;
      }

   } // action_wp_head_meta_tags();

   function add_actions() {
      if (!empty(self::$options['miscellaneous']['log_plugin_install_errors'])) {
         add_action('activated_plugin', [$this, 'action_activated_plugin']);
      }
      if (!empty(self::$options['updates']['disable_admin_notice'])) {
         add_action('admin_head', [$this, 'action_admin_head'], 1);
      }
      add_action('after_setup_theme', [$this, 'action_after_setup_theme']);
      add_action('widgets_init', [$this, 'action_widgets_init'], 10);
      if (!empty(self::$options['admin']['admin_global_message'])) {
         add_action('wp_after_admin_bar_render', [$this, 'action_wp_after_admin_bar_render']);
         add_action('wp_before_admin_bar_render', [$this, 'action_wp_before_admin_bar_render']);
      }
      add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts'], 20);
      add_action('wp_head', [$this, 'action_wp_head_meta_tags'], 8);
      add_action('wp_footer', [$this, 'action_wp_footer'], 20);
   } // add_actions();

   function add_filters() {

      add_filter('wp_robots', [$this, 'filter_wp_robots'], 10, 3);
      $updates = !empty(self::$options['updates']) ? self::$options['updates'] : [];
      if (!empty($updates['automatic_updater_disabled'])) {
         add_filter('automatic_updater_disabled', '__return_true');
      } else {
         if (!empty($updates['auto_update_core'])) {
            add_filter('auto_update_core', '__return_true');
         } else {
            add_filter('allow_major_auto_core_updates', '__return_' . (!empty($updates['allow_major_auto_core_updates']) ? 'true' : 'false'));
            add_filter('allow_minor_auto_core_updates', '__return_' . (!empty($updates['allow_minor_auto_core_updates']) ? 'true' : 'false'));
            add_filter('allow_dev_auto_core_updates',   '__return_' . (!empty($updates['allow_dev_auto_core_updates'])   ? 'true' : 'false'));
         }
         add_filter('auto_update_plugin',      '__return_' . (!empty($updates['auto_update_plugin'])      ? 'true' : 'false'));
         add_filter('auto_update_theme',       '__return_' . (!empty($updates['auto_update_theme'])       ? 'true' : 'false'));
         add_filter('auto_update_translation', '__return_' . (!empty($updates['auto_update_translation']) ? 'true' : 'false'));
      }

      if (!is_admin()) add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 10, 3);

      add_filter('site_icon_meta_tags', [$this, 'filter_site_icon_meta_tags']);
      add_filter('style_loader_tag', [$this, 'filter_style_loader_tag'], 10, 4);

   } // add_filters();

   function add_shortcodes() {
      if (isset(self::$options['support']['post-thumbnails'])) {
         add_shortcode('feature_image', [$this, 'shortcode_feature_image']);
      }
   } // add_shortcodes();

   function add_support() {
      if (isset(self::$options['support'])) {
         $options = self::$options['support'];
         if (isset($options['menus'])) add_theme_support('menus');
         if (isset($options['post-thumbnails'])) add_theme_support('post-thumbnails');
      }
   } // add_support();

   private static function display_maintenance_message($message) {
      if (str_ends_with($_SERVER['REDIRECT_URL'] ?? '', 'wp-login.php')) return;
      if (str_ends_with($_SERVER['SCRIPT_NAME']  ?? '', 'wp-login.php')) return;
      $user = wp_get_current_user();
      if ($user && !empty($user->roles)) {
         foreach ($user->roles as $role) {
            if ('administrator' == $role) return;
         }
      }
      wp_die($message);
   } // display_maintenance_message();

   function filter_wp_robots($robots) {
      self::$robots = $robots;
      return [];
   } // filter_wp_robots();

   function filter_script_loader_tag($tag, $id, $src) {
      if (self::$trim_urls_source) {
         $src = str_replace(self::$trim_urls_source, self::$trim_urls_target, $src);
      }
      return '    <script src="' . $src . '"></script>' . PHP_EOL;
   } // filter_script_loader_tag();

   function filter_site_icon_meta_tags($meta_tags) {
      if (isset(self::$options['site_icon_meta_tags'])) return [];
      if (self::$trim_urls_source) {
         foreach ($meta_tags as $key => $value) {
            $meta_tags[$key] = '    ' . str_replace(self::$trim_urls_source, self::$trim_urls_target, $value);
         }
      }
      return $meta_tags;
   }

   function filter_style_loader_tag($src, $id, $url, $media) {
      if (self::$trim_urls_source) {
         $url = str_replace(self::$trim_urls_source, self::$trim_urls_target, $url);
      }
      return '    <link href="' . $url . '" ' . (empty($media) ? '' : 'media="' . $media . '" ') . 'rel="stylesheet" />' . PHP_EOL;
   } // filter_style_loader_tag();

   public static function get_menu($menu_name, $indent = 0) {
      $html = '';
      $i = str_repeat(' ', $indent);
      if ($items = wp_get_nav_menu_items($menu_name)) {
         foreach ($items as $key => $item) {
            $html .= $i . '<a href="' . $item->url . '"' . ($item->target ? ' target="_blank"' : '') . 
               ($item->attr_title  ? ' title="' . $item->attr_title  . '"' : '') . '>' . $item->title . '</a>' . PHP_EOL;
         }
      }
      return $html;
   } // get_menu();  

   public static function loop() {
      // $hooks = USI_WordPress_Solutions_Hooks::get('the_content');
      if (!empty(self::$options['editor']['remove-wpautop']))         remove_filter('the_content', 'wpautop');
      if (!empty(self::$options['editor']['remove-wptexturize']))     remove_filter('the_content', 'wptexturize');
      if (!empty(self::$options['editor']['remove-excerpt-wpautop'])) remove_filter('the_excerpt', 'wpautop');
      while (have_posts()) { 
         the_post(); 
         //$content = get_the_content(null, false);
         //$content = apply_filters('the_content', $content);
         //$content = str_replace( ']]>', ']]&gt;', $content );
         the_content(); 
      } 
   } // loop();  

   function remove_actions() {
      if (isset(self::$options['wp_head'])) {
         $options = self::$options['wp_head'];
         if (isset($options['adjacent_posts_rel_link'])) remove_action('wp_head', 'adjacent_posts_rel_link');
         if (isset($options['adjacent_posts_rel_link_wp_head'])) remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
         if (isset($options['emoji_svg_url'])) add_filter('emoji_svg_url', '__return_false');
         if (isset($options['feed_links'])) remove_action('wp_head', 'feed_links', 2);
         if (isset($options['feed_links_extra'])) remove_action('wp_head', 'feed_links_extra', 3);
         if (isset($options['index_rel_link'])) remove_action('wp_head', 'index_rel_link');
         if (isset($options['print_emoji_detection_script'])) remove_action('wp_head', 'print_emoji_detection_script', 7);
         if (isset($options['rel_canonical'])) remove_action('wp_head', 'rel_canonical');
         if (isset($options['rest_output_link_wp_head'])) remove_action('wp_head', 'rest_output_link_wp_head', 10);
         if (isset($options['rsd_link'])) remove_action('wp_head', 'rsd_link');
         if (isset($options['start_post_rel_link'])) remove_action('wp_head', 'start_post_rel_link');
         if (isset($options['wlwmanifest_link'])) remove_action('wp_head', 'wlwmanifest_link');
         if (isset($options['wp_generator'])) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', [$this, 'filter_generator_version']);
         }
         if (isset($options['wp_oembed_add_discovery_links'])) remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
         if (isset($options['wp_oembed_add_host_js'])) remove_action('wp_head', 'wp_oembed_add_host_js'); 
         if (isset($options['wp_print_head_scripts'])) remove_action('wp_head', 'wp_print_head_scripts');
         if (isset($options['wp_print_styles'])) remove_action('wp_head', 'wp_print_styles');
         if (isset($options['wp_resource_hints'])) remove_action('wp_head', 'wp_resource_hints', 2);
         if (isset($options['wp_shortlink_wp_head'])) remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
         if (isset($options['wp_site_icon'])) remove_action('wp_head', 'wp_site_icon', 99);
         if (isset($options['print_emoji_styles'])) remove_action('wp_print_styles', 'print_emoji_styles');
      }
   } // remove_actions();

   function remove_filters() {
   // remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
   } // remove_filters();

   static function shortcode_feature_image($attributes, $content = null) {
      if (!has_post_thumbnail()) return null;
      if (empty($attributes) || ('url' == $attributes[0])) return get_the_post_thumbnail_url();
      $id = get_post_thumbnail_id();
      switch ($attributes[0]) {
      case 'alt': return get_post_meta($id, '_wp_attachment_image_alt', true);
      }
      return null;
   } // shortcode_feature_image();

} // Class USI_Theme_Solutions;
   
new USI_Theme_Solutions();

function usi_add_theme_editor_capabilities() {
   $role = get_role('administrator');
   if ($role) $role->add_cap('edit_theme_font_sizes'); 
} // usi_add_theme_editor_capabilities();

add_action('admin_init', 'usi_add_theme_editor_capabilities');

function widget($atts) {

   global $wp_widget_factory;

   extract(shortcode_atts(['widget_name' => FALSE], $atts));

   $widget_name = wp_specialchars($widget_name);

   if (!is_a($wp_widget_factory->widgets[$widget_name], 'WP_Widget')):
       $wp_class = 'WP_Widget_'.ucwords(strtolower($class));
       if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')):
          return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$class.'</strong>').'</p>';
       else:
          $class = $wp_class;
       endif;
   endif;

   ob_start();
   the_widget(
      $widget_name, 
      $instance, 
      [
         'widget_id'=>'arbitrary-instance-'.$id,
         'before_widget' => '',
         'after_widget' => '',
         'before_title' => '',
         'after_title' => ''
      ]
   );
   $output = ob_get_contents();
   ob_end_clean();
   return $output;

}

// --------------------------------------------------------------------------------------------------------------------------- // ?>
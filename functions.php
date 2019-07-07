<?php // ------------------------------------------------------------------------------------------------------------------------ //
/**
 * Theme-Solutions
 *
 * @package WordPress
 * @subpackage Theme-Solutions
 * @since Theme-Solutions 1.2.1 (2019-07-07)
 *
 **/

// https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
// https://developer.wordpress.org/themes/basics/theme-functions/
// http://justintadlock.com/archives/2010/11/08/sidebars-in-wordpress
// http://wordpress.stackexchange.com/questions/26557/programmatically-add-widgets-to-sidebars/51086#51086
// http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941

class USI_Theme_Solutions {

   const VERSION    = '1.2.1 (2019-07-07)';
   const NAME       = 'Theme-Solutions';
   const PREFIX     = 'usi-theme';
   const TEXTDOMAIN = 'usi-theme-solutions';

   static $home_url = null;
   static $options = null;
   static $option_name = USI_Theme_Solutions::PREFIX . '-options';
   static $options_post = null;
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

      $this->add_actions();
      $this->add_filters();
      $this->add_shortcodes();
      $this->add_support();
      $this->remove_actions();

   } // __construct();

   function action_activated_plugin() {
      usi_log('plugin_install_errors=' . ob_get_contents());
   } // action_activated_plugin();
   
   function action_after_setup_theme() {
      if (is_admin()) {
         require(get_template_directory() . '/usi-theme-solutions-settings.php');
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
         if (!empty(self::$options['wp_head']['remove_gutenberg_css'])) {
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

   function action_meta_tags() {

      global $post;

      if ($post) USI_Theme_Solutions::$options_post = get_post_meta($post->ID, '_usi-theme-page', true);

      echo '    <meta charset="' . get_bloginfo('charset') . '">' . PHP_EOL;
      if (isset(self::$options['meta_tags'])) {
         $options = self::$options['meta_tags'];
         foreach ($options as $key => $value) {
            if (!empty($value)) echo '    <meta name="' . $key . '" content="' . $value . '">' . PHP_EOL;
         }
      }

      if (!empty(USI_Theme_Solutions::$options_post['hide'])) {
         echo '    <meta name="robots" content="noindex,nofollow" />' . PHP_EOL;
      }

   } // action_meta_tags();

   function action_widgets_init() {
      if (isset(self::$options['widget_areas'])) {
         $options = self::$options['widget_areas'];
         $source = array('__', '_', '{i}', '{n}', 'null');
         $target = array('_', ' ', '', PHP_EOL, '');
         foreach ($options as $key => $value) {
            $tokens = explode(' ', $value);
            if (!empty($tokens[7])) $target[2] = str_repeat(' ', (int)$tokens[7]);
            register_sidebar(
               array(
                  'id'   => !empty($tokens[0]) ? str_replace($source, $target, $tokens[0]) : '',
                  'name' => !empty($tokens[1]) ? str_replace($source, $target, $tokens[1]) : '',
                  'description'   => !empty($tokens[2]) ? str_replace($source, $target, $tokens[2]) : '',
                  'before_widget' => !empty($tokens[3]) ? str_replace($source, $target, $tokens[3]) : '',
                  'after_widget'  => !empty($tokens[4]) ? str_replace($source, $target, $tokens[4]) : '',
                  'before_title'  => !empty($tokens[5]) ? str_replace($source, $target, $tokens[5]) : '',
                  'after_title'   => !empty($tokens[6]) ? str_replace($source, $target, $tokens[6]) : '',
               )
            );
         }
      }
   } // action_widgets_init();

   function action_wp_footer() {
      if (!empty(self::$options['search']['google_analytics'])) echo '    ' . self::$options['search']['google_analytics'];
   } // action_wp_footer();

   function action_wp_head_inline_style() {
      // echo "    <style>\n      /* theme customizer .jim2{color:red;} */\n    </style>\n";
   } // action_wp_head_inline_style();

   function add_actions() {
      if (!empty(self::$options['miscellaneous']['log_plugin_install_errors'])) {
         add_action('activated_plugin', array($this, 'action_activated_plugin'));
      }
      add_action('after_setup_theme', array($this, 'action_after_setup_theme'));
      add_action('widgets_init', array($this, 'action_widgets_init'), 10);
      add_action('wp_enqueue_scripts', array($this, 'action_wp_enqueue_scripts'), 20);
      add_action('wp_head', 'wp_site_icon', 8);
      add_action('wp_head', array($this, 'action_meta_tags'), 8.5);
      add_action('wp_head', array($this, 'action_wp_head_inline_style'), 30);
      add_action('wp_footer', array($this, 'action_wp_footer'), 20);
   } // add_actions();

   function add_filters() {

      $updates = !empty(self::$options['updates']) ? self::$options['updates'] : array();
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

      add_filter('script_loader_tag', array($this, 'filter_script_loader_tag'), 10, 3);
      add_filter('site_icon_meta_tags', array($this, 'filter_site_icon_meta_tags'));
      add_filter('style_loader_tag', array($this, 'filter_style_loader_tag'), 10, 4);
   } // add_filters();

   function add_shortcodes() {
      add_shortcode('cloak', array($this, 'shortcode_email'));
   } // add_shortcodes();

   function add_support() {
      if (isset(self::$options['support'])) {
         $options = self::$options['support'];
         if (isset($options['menus'])) add_theme_support('menus');
         if (isset($options['post-thumbnails'])) add_theme_support('post-thumbnails');
      }
   } // add_support();

   function filter_script_loader_tag($tag, $id, $src) {
      if (self::$trim_urls_source) {
         $src = str_replace(self::$trim_urls_source, self::$trim_urls_target, $src);
      }
      return('    <script src="' . $src . '"></script>' . PHP_EOL);
   } // filter_script_loader_tag();

   function filter_site_icon_meta_tags($meta_tags) {
      if (self::$trim_urls_source) {
         foreach ($meta_tags as $key => $value) {
            $meta_tags[$key] = '    ' . str_replace(self::$trim_urls_source, self::$trim_urls_target, $value);
         }
      }
      return($meta_tags);
   }

   function filter_style_loader_tag($src, $id, $url, $media) {
      if (self::$trim_urls_source) {
         $url = str_replace(self::$trim_urls_source, self::$trim_urls_target, $url);
      }
      return('    <link href="' . $url . '" ' . (empty($media) ? '' : 'media="' . $media . '" ') . 'rel="stylesheet" />' . PHP_EOL);
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
      return($html);
   } // get_menu();  

   public static function loop() {
      while (have_posts()) { 
         the_post(); 
         the_content(); 
      } 
   } // loop();  

   function remove_actions() {
      remove_action('wp_head', 'wp_site_icon', 99);
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
            add_filter('the_generator', array($this, 'filter_generator_version'));
         }
         if (isset($options['wp_oembed_add_discovery_links'])) remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
         if (isset($options['wp_oembed_add_host_js'])) remove_action('wp_head', 'wp_oembed_add_host_js'); 
         if (isset($options['wp_print_head_scripts'])) remove_action('wp_head', 'wp_print_head_scripts');
         if (isset($options['wp_print_styles'])) remove_action('wp_head', 'wp_print_styles');
         if (isset($options['wp_shortlink_wp_head'])) remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
         
         if (isset($options['print_emoji_styles'])) remove_action('wp_print_styles', 'print_emoji_styles');
      }
   } // remove_actions();

   static function shortcode_email($attributes, $content = null) {
      $t = explode('-', $attributes['email']);
      $local = str_replace('__', '-', $t[1]);
      $domain = str_replace('__', '-', $t[2]);;
      $tld = $t[3];
      $subject = $t[4];
      $font = $t[5];
      $base = $t[6];
      $height = $t[7];
      $padding = $t[8];
      $size = 0.75 * (float)$t[9];
      $font_name = "{$_SERVER['DOCUMENT_ROOT']}/$font";
      $text = $local . '@' . $domain . '.' . $tld;
      $bound_box = ImageTTFBbox($size, 0, $font_name, $text);
      $width = $bound_box[4] - $bound_box[0] + 2 * $padding;
      $html = '<a ' . (!empty($attributes['class']) ? 'class="' . $attributes['class'] . '" ' : '') . 
         'href="scripts/cloak.php?1-' . "$t[1]-$t[2]-$t[3]-$t[4]-$t[5]-$t[6]-$t[7]-$t[8]-$t[9]" . '" ' .
         'onmouseout="this.style.backgroundPosition=\'0 0\';" onmouseover="this.style.backgroundPosition=\'-100% 0\';" ' .
         'style="background:url(scripts/cloak.php?' . $attributes['email'] . '); ' . 'display:inline-block; height:' . 
         $height . 'px; vertical-align:top; width:' . $width . 'px;" target="null-frame"></a>';
      return($html);
   } // shortcode_email();

} // Class USI_Theme_Solutions;
   
new USI_Theme_Solutions();

function usi_add_theme_editor_capabilities() {
   $role = get_role('administrator');
   if ($role) $role->add_cap('edit_theme_font_sizes'); 
} // usi_add_theme_editor_capabilities();

add_action('admin_init', 'usi_add_theme_editor_capabilities');

function widget($atts) {
    
    global $wp_widget_factory;
    
    extract(shortcode_atts(array(
        'widget_name' => FALSE
    ), $atts));
    
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
    the_widget($widget_name, $instance, array('widget_id'=>'arbitrary-instance-'.$id,
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => ''
    ));
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
    
}

// --------------------------------------------------------------------------------------------------------------------------- // ?>
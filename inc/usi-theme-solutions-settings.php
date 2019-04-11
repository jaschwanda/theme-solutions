<?php // ------------------------------------------------------------------------------------------------------------------------ //

// Add theme wide JS at head, foot;

class USI_Theme_Solutions_Settings {

   const VERSION = '1.0.13 (2017-08-29)';

   var $indent = '      ';
   var $options = null;
   var $option_name = null;
   var $page_slug = 'usi-ts-settings';
   var $section = 'theme-solutions';
   var $versions = null;

   function __construct($option_name, $options) {
      if (empty($options['jquery']['load'])) $options['jquery']['load'] = 'none';
      if (empty($options['jquery']['source'])) $options['jquery']['source'] = 'google';
      $this->options = $options;
      $this->option_name = $option_name;
      add_action('add_meta_boxes', array($this, 'action_add_meta_boxes'));
      add_action('admin_head', array($this, 'action_admin_head'));
      add_action('admin_init', array($this, 'action_admin_init'));
      add_action('admin_menu', array($this, 'action_admin_menu'));
      add_action('save_post', array($this, 'action_save_post'));
      add_action('widgets_init', array($this, 'action_widgets_init'));
      $this->add_filters();
      $this->remove_filters();
   } // __construct();

   function action_add_meta_boxes() {
      add_meta_box(
         'usi-ts-page-meta-box', // Meta box id;
         __('Theme Solutions Options', USI_Theme_Solutions::TEXTDOMAIN), // Title;
         array($this, 'render_meta_box'), // Render meta box callback;
         'page', // Screen type;
         'side', // Location on page;
         'low' // Priority;
      );
   } // action_add_meta_boxes();

   function action_admin_head() {
      $page = !empty($_GET['page']) ? $_GET['page'] : null;
      if ('usi-ts-settings' == $page) {
         echo '<style>' . PHP_EOL .
            '.form-table td{padding-bottom:2px; padding-top:2px;} /* 15px; */' . PHP_EOL .
            '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
            'h2{margin-bottom:0.1em; margin-top:2em;} /* 1em; */' . PHP_EOL .
            '</style>' . PHP_EOL;
      }
   } // action_admin_head();

   function action_admin_init() {

      $render_fields_callback = array($this, 'fields_render');

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'admin'), // Section id;
         __('Administrator Pages', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'admin_bar_menu') . ']', // Option name;
         $id, // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'text')
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'admin_footer_text') . ']', // Option name;
         $id, // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'text')
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'update_footer') . ']', // Option name;
         $id, // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'text')
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'disable-editor') . ']', // Option name;
         'Disable File Editor', // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'check', 
            'notes' => '<i>' . __('Disables the plugin and theme code editor', USI_Theme_Solutions::TEXTDOMAIN) . '.</i>')
      );
   
      register_setting($this->section, $this->option_name, array($this, 'fields_validate'));

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'editor'), // Section id;
         __('Editor Functions', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $wp_header = array(
         'remove the_content wptexturize',
         'remove the_content wpautop',
         'remove the_excerpt wpautop',
      );
      $wp_header_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($wp_header); $ith++) {
         $id = $wp_header[$ith];   
         $wp_header_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            str_replace(' ', '&nbsp;', $id), // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $wp_header_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'header'), // Section id;
         __('Header Functions', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $header_functions = array(
         'base_url' => 'Base URL',
      );

      foreach ($header_functions as $header_id => $header_title) {
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $header_id . ']', // Option name;
            __($header_title, USI_Theme_Solutions::TEXTDOMAIN), // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            array('id' => $header_id, 'section' => $section, 'type' => 'check')
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'jquery'), // Section id;
         __('jQuery Libraries', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $jquery_args = array(
         array('id' => 'load', 'title' => __('Load', USI_Theme_Solutions::TEXTDOMAIN), 'type' => 'radio',
            'options' => array(
               array('value' => 'none', 'notes' => __('Do Not Load', USI_Theme_Solutions::TEXTDOMAIN) . ' &nbsp; &nbsp; &nbsp;'),
               array('value' => 'header', 'notes' => __('In Header', USI_Theme_Solutions::TEXTDOMAIN) . ' &nbsp; &nbsp; &nbsp;'),
               array('value' => 'footer', 'notes' => __('In Footer', USI_Theme_Solutions::TEXTDOMAIN)),
            )
         ),
      );
      if (!empty($this->options['jquery']['load']) && ('none' != $this->options['jquery']['load'])) {
         $jquery_args[] = 
            array('id' => 'source', 'title' => __('Source', USI_Theme_Solutions::TEXTDOMAIN), 'type' => 'radio',
               'options' => array(
                  array('value' => 'google', 'notes' => __('Google', USI_Theme_Solutions::TEXTDOMAIN) . ' &nbsp; &nbsp; &nbsp;'),
                  array('value' => 'wordpress', 'notes' => __('Wordpress', USI_Theme_Solutions::TEXTDOMAIN)),
               )
            );
         global $wp_scripts;
         $jquery_args[] = array('id' => 'version', 'title' => __('Version', USI_Theme_Solutions::TEXTDOMAIN), 'type' => 'text',
            'notes' => '<i>' . __('Current WordPress jQuery version is', USI_Theme_Solutions::TEXTDOMAIN) . 
            ' ' . $wp_scripts->registered['jquery']->ver . '</i>');
      }

      for ($ith = 0; $ith < count($jquery_args); $ith++) {
         $jquery_args[$ith]['section'] = $section;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $jquery_args[$ith]['id'] . ']', // Option name;
            $jquery_args[$ith]['title'], // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $jquery_args[$ith]
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'meta_tags'), // Section id;
         __('Meta Tags', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $meta_tags = array(
         'copyright',
         'description',
         'format-detection',
         'viewport',
      );

      foreach ($meta_tags as $meta_id) {
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $meta_id . ']', // Option name;
            $meta_id, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            array('id' => $meta_id, 'section' => $section, 'type' => 'text')
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'miscellaneous'), // Section id;
         __('Miscellaneous', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $miscellaneous = array(
         'log_plugin_install_errors',
      );
      $miscellaneous_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($miscellaneous); $ith++) {
         $id = $miscellaneous[$ith];   
         $miscellaneous_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            $id, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $miscellaneous_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'wp_head'), // Section id;
         __('Remove wp_head() Items', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );
/*
add_action( 'wp_head',             '_wp_render_title_tag',            1     );
add_action( 'wp_head',             'wp_enqueue_scripts',              1     );
add_action( 'wp_head',             'locale_stylesheet'                      );
add_action( 'wp_head',             'noindex',                          1    );
if (isset( $_GET['replytocom']))   add_action( 'wp_head', 'wp_no_robots' );
add_action( 'wp_head', 'wp_post_preview_js', 1 );
add_action( 'wp_head', '_custom_logo_header_styles' );
*/

      $wp_header = array(
         'adjacent_posts_rel_link',
         'adjacent_posts_rel_link_wp_head',  // 10,0;
         'emoji_svg_url',
         'feed_links',                       // 2;
         'feed_links_extra',                 // 3;
         'index_rel_link',
         'print_emoji_detection_script',     // 7;
         'print_emoji_styles',
         'remove_recent_comments_style',
         'rel_canonical',
         'rest_output_link_wp_head',         // 10,0;
         'rsd_link',
         'start_post_rel_link',
         'wlwmanifest_link',
         'wp_generator',
         'wp_oembed_add_discovery_links', 
         'wp_oembed_add_host_js',
         'wp_print_head_scripts',            // 9;
         'wp_print_styles',                  // 8;
         'wp_shortlink_wp_head',             // 10,0;
      // 'wp_site_icon',                     // 99;
      );
      $wp_header_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($wp_header); $ith++) {
         $id = $wp_header[$ith];   
         $wp_header_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            $id, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $wp_header_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'widgets'), // Section id;
         __('Remove Unused Default Widgets', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $widgets = array(
         'Archives',
         'Calendar',
         'Categories',
         'Links',
         'Meta',
         'Pages',
         'Recent Comments',
         'Recent Posts',
         'RSS',
         'Search',
         'Tag Cloud',
         'Text',
         'Menu Widget',
      );
      $widgets_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($widgets); $ith++) {
         $id = $widgets[$ith];   
         $widgets_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            str_replace(' ', '&nbsp;', $id), // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $widgets_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'scripts'), // Section id;
         __('Scripts', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $scripts = isset($this->options['scripts']) ? $this->options['scripts'] : array();
      $scripts['new-script'] = 'new-script';

      foreach ($scripts as $script_value) {
         $tokens = explode(' ', $script_value);
         $script_id = $field_title = $tokens[0];
         $args = array('id' => $script_id, 'section' => $section, 'type' => 'text');
         if ('new-script' == $script_id) {
            $args['notes'] = '<i>unique-id &nbsp; script/path/name &nbsp; version &nbsp; footer</i>';
            $field_title = __('Add Script', USI_Theme_Solutions::TEXTDOMAIN);
         }
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $script_id . ']', // Option name;
            $field_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $args // Additional arguments;
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'search'), // Section id;
         __('Search Engine Tools', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'google_analytics') . ']', // Option name;
         'Google Analytics', // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'text')
      );
     
      add_settings_field(
         $this->option_name . '[' . $section . '][' . ($id = 'page_title_suffix') . ']', // Option name;
         'Page Title Suffix', // Field title;
         $render_fields_callback, // Render field callback;
         $this->page_slug, // Settings page menu slug;
         $section_id, // Section id;
         array('id' => $id, 'section' => $section, 'type' => 'text')
      );

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'social'), // Section id;
         __('Social Media Links', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $social_media = array(
         'facebook' => 'Facebook',
         'flickr' => 'Flickr',
         'googleplus' => 'Google+',
         'instagram' => 'Instagram',
         'linkedin' => 'LinkedIn',
         'pinterest' => 'Pinterest',
         'twitter' => 'Twitter',
         'youtube' => 'YouTube',
      );

      foreach ($social_media as $media_id => $media_title) {
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $media_id . ']', // Option name;
            $media_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            array('id' => $media_id, 'section' => $section, 'type' => 'text')
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'styles'), // Section id;
         __('Styles', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $styles = isset($this->options['styles']) ? $this->options['styles'] : array();
      $styles['new-style'] = 'new-style';

      foreach ($styles as $style_value) {
         $tokens = explode(' ', $style_value);
         $style_id = $field_title = $tokens[0];
         $args = array('id' => $style_id, 'section' => $section, 'type' => 'text');
         if ('new-style' == $style_id) {
            $args['notes'] = '<i>unique-id &nbsp; style/path/name &nbsp; version &nbsp; media</i>';
            $field_title = __('Add Style', USI_Theme_Solutions::TEXTDOMAIN);
         }
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $style_id . ']', // Option name;
            $field_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $args // Additional arguments;
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'templates'), // Section id;
         __('Templates', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $templates = isset($this->options['templates']) ? $this->options['templates'] : array();
      $templates['new-template'] = 'new-template';

      $template_directory = get_template_directory();
      $this->versions = '<table>';

      $this->versions .= '<tr><td>activate</td><td>' . USI_Theme_Solutions_Activate::VERSION . '</td></tr>';
      $this->versions .= '<tr><td>customizer</td><td>' . USI_Theme_Solutions_Customizer::VERSION . '</td></tr>';
      $this->versions .= '<tr><td>settings</td><td>' . USI_Theme_Solutions_Settings::VERSION . '</td></tr>';
      $this->versions .= '<tr><td>functions</td><td>' . USI_Theme_Solutions::VERSION . '</td></tr>';

      $plug = get_plugin_data(str_replace('\\', '/', $template_directory . '/head.php'));
      $this->versions .= '<tr><td>head</td><td>' . $plug['Version'] . '</td></tr>';

      $plug = get_plugin_data(str_replace('\\', '/', $template_directory . '/index.php'));
      $this->versions .= '<tr><td>index</td><td>' . $plug['Version'] . '</td></tr>';

      $template_directory = get_stylesheet_directory(); //get_template_directory();

      foreach ($templates as $template_value) {
         $tokens = explode(' ', $template_value);
         $template_id = $field_title = $tokens[0];
         $args = array('id' => $template_id, 'section' => $section, 'type' => 'text');
         if ('new-template' == $template_id) {
            $args['notes'] = '<i>unique-id &nbsp; first-part &nbsp; second-part &nbsp; . . . &nbsp; nth-part</i>';
            $field_title = __('Add Template', USI_Theme_Solutions::TEXTDOMAIN);
         }
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $template_id . ']', // Option name;
            $field_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $args // Additional arguments;
         );
         for ($ith = 1; $ith < count($tokens); $ith++) {
            $plug = get_plugin_data(str_replace('\\', '/', $template_directory . '/' . $tokens[$ith] . '.php'));
            $this->versions .= '<tr><td>' . $tokens[$ith] . '</td><td>' . $plug['Version'] . '</td></tr>';
         }
      }
      $this->versions .= '</table>';

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'support'), // Section id;
         __('Theme Support', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $support = array(
         'menus',
         'post-thumbnails',
      );
      $support_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($support); $ith++) {
         $id = $support[$ith];   
         $support_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            $id, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $support_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'trim_urls'), // Section id;
         __('Trim URLs', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $trim_urls = isset($this->options['trim_urls']) ? $this->options['trim_urls'] : array();
      $trim_urls['new-trim_url'] = 'new-trim_url';

      foreach ($trim_urls as $trim_url_value) {
         $tokens = explode(' ', $trim_url_value);
         $trim_url_id = $field_title = $tokens[0];
         $args = array('id' => $trim_url_id, 'section' => $section, 'type' => 'text');
         if ('new-trim_url' == $trim_url_id) {
            $args['notes'] = '<i>unique-id &nbsp; long/url &nbsp; short/url</i>';
            $field_title = __('Add URL trim', USI_Theme_Solutions::TEXTDOMAIN);
         }
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $trim_url_id . ']', // Option name;
            $field_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $args // Additional arguments;
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'updates'), // Section id;
         __('Updates', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $update = array(
         'automatic_updater_disabled',
/*
         'auto_update_core',
         'allow_dev_auto_core_updates',
         'allow_minor_auto_core_updates',
         'allow_major_auto_core_updates',
         'auto_update_plugin',
         'auto_update_theme',
*/
      );
      $update_args = array('id' => '', 'section' => $section, 'type' => 'check');

      for ($ith = 0; $ith < count($update); $ith++) {
         $id = $update[$ith];   
         $update_args['id'] = $id;
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $id . ']', // Option name;
            $id, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $update_args
         );
      }

      /* --- */

      add_settings_section(
         $section_id = 'section-' . ($section = 'widget_areas'), // Section id;
         __('Widgetized Areas', USI_Theme_Solutions::TEXTDOMAIN), // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $widget_areas = isset($this->options['widget_areas']) ? $this->options['widget_areas'] : array();
      $widget_areas['new-widget_areas'] = 'new-widget_areas';

      foreach ($widget_areas as $widget_areas_value) {
         $tokens = explode(' ', $widget_areas_value);
         $widget_areas_id = $field_title = $tokens[0];
         $args = array('id' => $widget_areas_id, 'section' => $section, 'type' => 'text');
         if ('new-widget_areas' == $widget_areas_id) {
            $args['notes'] = '<i>unique-id &nbsp; name &nbsp; description &nbsp; before_widget &nbsp; after_widget &nbsp; before_title &nbsp; after_title</i>';
            $field_title = __('Add Widgetized Area', USI_Theme_Solutions::TEXTDOMAIN);
         }
         add_settings_field(
            $this->option_name . '[' . $section . '][' . $widget_areas_id . ']', // Option name;
            $field_title, // Field title;
            $render_fields_callback, // Render field callback;
            $this->page_slug, // Settings page menu slug;
            $section_id, // Section id;
            $args // Additional arguments;
         );
      }
   
   } // action_admin_init();

   function action_admin_menu() {
      add_options_page(
         __('Theme-Solutions Settings', USI_Theme_Solutions::TEXTDOMAIN), // Page <title/> text;
         __('Theme-Solutions', USI_Theme_Solutions::TEXTDOMAIN), // Sidebar menu text; 
         'manage_options', // Capability required to enable page;
         $this->page_slug, // Menu page slug name;
         array($this, 'settings_page') // Render page callback;
      );
   } // action_admin_menu();

   function action_save_post($page_id) {
      if (!current_user_can('edit_page', $page_id)) {      
      } else if (wp_is_post_autosave($page_id)) {
      } else if (wp_is_post_revision($page_id)) {
      } else if (empty($_POST['usi-ts-page-nonce'])) {
      } else if (!wp_verify_nonce($_POST['usi-ts-page-nonce'], basename(__FILE__))) {
      } else {
         $new_options = array(
            'hide' => !empty($_POST['usi-ts-page-hide']),
            'template' => !empty($_POST['usi-ts-page-template']) ? $_POST['usi-ts-page-template'] : 'default',
         );
         update_post_meta($page_id, '_usi-ts-page', $new_options);
      }
   } // action_save_post();

   function action_widgets_init() {
      if (isset($this->options['wp_head']['remove_recent_comments_style'])) {
         global $wp_widget_factory;
         remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
      }
      if (isset($this->options['widgets']['Archives'])) unregister_widget('WP_Widget_Archives');
      if (isset($this->options['widgets']['Calendar'])) unregister_widget('WP_Widget_Calendar');
      if (isset($this->options['widgets']['Categories'])) unregister_widget('WP_Widget_Categories');
      if (isset($this->options['widgets']['Links'])) unregister_widget('WP_Widget_Links');
      if (isset($this->options['widgets']['Meta'])) unregister_widget('WP_Widget_Meta');
      if (isset($this->options['widgets']['Pages'])) unregister_widget('WP_Widget_Pages');
      if (isset($this->options['widgets']['Recent Comments'])) unregister_widget('WP_Widget_Recent_Comments');
      if (isset($this->options['widgets']['Recent Posts'])) unregister_widget('WP_Widget_Recent_Posts');
      if (isset($this->options['widgets']['RSS'])) unregister_widget('WP_Widget_RSS');
      if (isset($this->options['widgets']['Search'])) unregister_widget('WP_Widget_Search');
      if (isset($this->options['widgets']['Tag Cloud'])) unregister_widget('WP_Widget_Tag_Cloud');
      if (isset($this->options['widgets']['Text'])) unregister_widget('WP_Widget_Text');
      if (isset($this->options['widgets']['Menu_Widget'])) unregister_widget('WP_Nav_Menu_Widget');
   } // action_widgets_init();

   function add_filters(){
      if (isset($this->options['admin'])) {
         $options = $this->options['admin'];
         if (!empty($options['admin_bar_menu'])) add_filter('admin_bar_menu', array($this, 'filter_admin_bar_menu'), 25);
         if (isset($options['admin_footer_text'])) add_filter('admin_footer_text', array($this, 'filter_admin_footer_text'));
         if (!empty($options['update_footer'])) add_filter('update_footer', array($this, 'filter_footer_version'), 999);
         define('DISALLOW_FILE_EDIT', isset($options['disable-editor']) ? true : false);
         add_filter('wp_prepare_themes_for_js', array($this, 'filter_themes'));
      }
      //add_filter('custom_menu_order', '__return_true'); 
      //add_filter('custom_menu_order', array($this, 'filter_custom_menu_order'));
      //add_filter('menu_order', array($this, 'filter_menu_order'));
      //add_action('admin_menu', array($this, 'filter_admin_menu'));
   } // add_filters();

   function fields_render($args){
      $id = $args['id'];
      $section = $args['section'];
      switch ($args['type']) {
      case 'check':
         echo '<input name="' . $this->option_name . '[' . $section . '][' . $id . ']" type="checkbox" value="1"' . 
            checked('1', isset($this->options[$section][$id]) ? $this->options[$section][$id] : null, false)  . ' />';
         if (isset($args['notes'])) echo $args['notes'];
         break;
      case 'radio':
         $options = $args['options'];
         for ($ith = 0; $ith < count($options); $ith++) {
            $value = $options[$ith]['value'];
            echo '<input name="' . $this->option_name . '[' . $section . '][' . $id . ']" type="radio" value="' . $value . '"' . 
               checked($value, isset($this->options[$section][$id]) ? $this->options[$section][$id] : null, false)  . ' />';
            if (isset($options[$ith]['notes'])) echo $options[$ith]['notes'];
         }
         break;
      case 'text':
         echo '<input class="large-text" name="' . $this->option_name . '[' . $section . '][' . $id . ']" type="text" value="' . 
            esc_attr(isset($this->options[$section][$id]) ? $this->options[$section][$id] : null) . '" />';
         if (isset($args['notes'])) echo $args['notes'];
         break;
      }
   } // fields_render();

   function fields_validate($input) {
      $scripts = $input['scripts'];
      $new_scripts = array();
      foreach($scripts as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_scripts[$tokens[0]] = $value;
         }
      }
      unset($new_scripts['new-script']);
      $input['scripts'] = $new_scripts;

      $styles = $input['styles'];
      $new_styles = array();
      foreach($styles as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_styles[$tokens[0]] = $value;
         }
      }
      unset($new_styles['new-style']);
      $input['styles'] = $new_styles;

      $templates = $input['templates'];
      $new_templates = array();
      foreach($templates as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_templates[$tokens[0]] = $value;
         }
      }
      unset($new_templates['new-template']);
      $input['templates'] = $new_templates;

      $trim_urls = $input['trim_urls'];
      $new_trim_urls = array();
      foreach($trim_urls as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_trim_urls[$tokens[0]] = $value;
         }
      }
      unset($new_trim_urls['new-trim_url']);
      $input['trim_urls'] = $new_trim_urls;

      $widget_areas = $input['widget_areas'];
      $new_widget_areas = array();
      foreach($widget_areas as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_widget_areas[$tokens[0]] = $value;
         }
      }
      unset($new_widget_areas['new-widget_areas']);
      $input['widget_areas'] = $new_widget_areas;
      return($input);
   } // fields_validate();

   function filter_admin_bar_menu($wp_admin_bar) {
      $role = $this->get_user_role();
      $my_account = $wp_admin_bar->get_node('my-account');
      $greating = str_replace(array('{role}'), array(ucfirst($role)), $this->options['admin']['admin_bar_menu']);
      $new_title = str_replace('Howdy,', $greating, $my_account->title);           
      $wp_admin_bar->add_node(
         array(
            'id' => 'my-account',
            'title' => $new_title,
         )
      );
   } // filter_admin_bar_menu();

   function filter_admin_footer_text() {
      $text = $this->options['admin']['admin_footer_text'];
      echo '<span id="footer-thankyou">' . $text . '</span>';
   } // filter_admin_footer_text();

   function filter_footer_version() {
      return($this->options['admin']['update_footer']);
   } // filter_footer_version();

   function filter_generator_version() {
      return('');
   } // filter_generator_version();

   function filter_admin_menu() {
    global $menu;
    global $submenu;
    usi_log('filter_admin_menu:menu=' . print_r($menu, true) . ' submenu=' . print_r($submenu, true));
   } // filter_menu_order();
   function filter_custom_menu_order($menu_ord) {
     global $menu;
     global $submenu;
     usi_log('filter_custom_menu_order:menu=' . print_r($menu, true) . ' submenu=' . print_r($submenu, true) . ' arg=' . print_r($menu_ord, true));
     return($menu_ord);
   } // filter_menu_order();
   function filter_menu_order($menu_ord) {
    global $menu;
    global $submenu;
    usi_log('filter_menu_order:menu=' . print_r($menu, true) . ' submenu=' . print_r($submenu, true) . ' arg=' . print_r($menu_ord, true));
   return($menu_ord);
   } // filter_menu_order();
   
   function filter_themes($themes) {
      unset($themes['usi-theme-solutions']);
      return($themes);
   } // filter_themes();

   function get_user_role() {
      global $current_user;
      return($current_user->roles[0]);
   } // get_user_role();

   function remove_filters(){
      if (isset($options['remove_the_content_wpautop'])) remove_filter('the_content', 'wpautop');
      if (isset($options['remove_the_content_wptexturize'])) remove_filter('the_content', 'wptexturize');
      if (isset($options['remove_the_excerpt_wpautop'])) remove_filter('the_excerpt', 'wpautop');
   } // remove_filters();

   function render_meta_box($post) {

      wp_nonce_field(basename(__FILE__), 'usi-ts-page-nonce');

      $options = get_post_meta($post->ID, '_usi-ts-page', true);
      $hide = !empty($options['hide']);
      $template = !empty($options['template']) ? $options['template'] : 'default';

?>
<p>
  <strong><?php _e('Template',  USI_Theme_Solutions::TEXTDOMAIN); ?></strong>
</p>
<label class="screen-reader-text" for="usi-ts-page-template"><?php _e('Template',  USI_Theme_Solutions::TEXTDOMAIN); ?></label>
<select name="usi-ts-page-template">
<?php
      $templates = $this->options['templates'];
      foreach ($templates as $key => $value) {
         echo '  <option ' . (($template == $key) ? 'selected ' : '') . 'value="' . $key . '">' . $key . '</option>' . PHP_EOL;
      }
?>
</select>
<p>
  <strong><?php _e('Search Engine Visibility',  USI_Theme_Solutions::TEXTDOMAIN); ?></strong>
</p>
<input id="usi-ts-page-hide"<?php checked($hide, true); ?> name="usi-ts-page-hide" type="checkbox" value="true" />
<label for="usi-ts-page-hide"><?php _e('Hide from search engines', USI_Theme_Solutions::TEXTDOMAIN); ?></label>
<?php
   } // render_meta_box();
   
   function settings_page() {

      $i = $this->indent;
      echo PHP_EOL .
         $i . '<div class="wrap">' . PHP_EOL .
         $i . '  <h1>' . __('Theme-Solutions Settings', USI_Theme_Solutions::TEXTDOMAIN) . '</h1>' . PHP_EOL .
         $i . '  <form method="post" action="options.php">' . PHP_EOL .
         $i . '    ';
      settings_fields($this->section);
      do_settings_sections($this->page_slug);     
      echo PHP_EOL .
         $i . '    <h2>' . __('Versions', USI_Theme_Solutions::TEXTDOMAIN) . '</h2>' . PHP_EOL .
         $i . '    <table class="form-table"><tbody><tr><th scope="row">' . __('.php Files', USI_Theme_Solutions::TEXTDOMAIN) . '</th><td>' . $this->versions . '</td></tr></tbody></table>' . PHP_EOL;
      submit_button(); 
      echo PHP_EOL .
         $i . '  </form>' . PHP_EOL .
         $i . '</div>' . PHP_EOL;

   } // settings_page();

} // Class USI_Theme_Solutions_Settings;

if (!function_exists('usi_log')) {
   function usi_log($action) {
      global $wpdb;
      $wpdb->insert($wpdb->prefix . 'USI_log', 
         array(
            'action' => $action,
            'user_id' => get_current_user_id(), 
         )
      );
   } // usi_log();
} // ENDIF function_exists('usi_log');

// --------------------------------------------------------------------------------------------------------------------------- // ?>
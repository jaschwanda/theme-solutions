<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

define('USI_THEME_SOLUTIONS_WORDPRESS_SETTINGS', WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-settings.php');

if (!file_exists(USI_THEME_SOLUTIONS_WORDPRESS_SETTINGS)) {

   echo '<div class="notice notice-warning is-dismissible"><p>' . 
      sprintf(
         __('The %s plugin is required for the %s framework to run properly.', USI_Theme_Solutions::TEXTDOMAIN), 
         '<b>WordPress-Solutions</b>',
         '<b>Theme-Solutions</b>'
      ) .
      '</p></div>';

   return; // Don't want to pocess the rest of the file;

} 

require_once(USI_THEME_SOLUTIONS_WORDPRESS_SETTINGS);

class USI_Theme_Solutions_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '1.0.8 (2018-01-10)';

   function __construct() {

      parent::__construct(
         USI_Theme_Solutions::NAME, 
         USI_Theme_Solutions::PREFIX, 
         USI_Theme_Solutions::TEXTDOMAIN,
         USI_Theme_Solutions::$options,
         true,
         false
      );

      if (empty($this->options['jquery']['load']))   $this->options['jquery']['load']   = 'none';
      if (empty($this->options['jquery']['source'])) $this->options['jquery']['source'] = 'google';

      $this->add_actions();
      $this->add_filters();
      $this->remove_filters();

   } // __construct();

   function action_add_meta_boxes() {
      add_meta_box(
         'usi-theme-page-meta-box', // Meta box id;
         __('Theme Solutions Options', USI_Theme_Solutions::TEXTDOMAIN), // Title;
         array($this, 'render_meta_box'), // Render meta box callback;
         'page', // Screen type;
         'side', // Location on page;
         'low' // Priority;
      );
   } // action_add_meta_boxes();

   // The $wp_scripts global isn't set when the $this->sections() method is called so update now;
   function action_admin_init() {
      if (empty($this->options['jquery']['load']) || ('none' == $this->options['jquery']['load'])) {
         $this->sections['jquery']['settings']['source']['skip']  = 
         $this->sections['jquery']['settings']['version']['skip'] = true;
      } else {
         global $wp_scripts;
         $jquery_version = str_replace('-wp', '',  $wp_scripts->registered['jquery']->ver);
         $this->sections['jquery']['settings']['version']['notes'] = 
            '<i>' . sprintf(__('The current jQuery version used by WordPress is %s', USI_Theme_Solutions::TEXTDOMAIN),  $jquery_version) . '</i>';
      }
      parent::action_admin_init();
   } // action_admin_init();

   function action_load_help_tab() {
      $screen = get_current_screen();
      $screen->add_help_tab(
         array(
           'id' => 'scripts',
           'title' => __('Scripts, Styles, Templates, Trims and Widgetized Areas'),
           'content' => '<p>' . __( 'The <b>Scripts</b>, <b>Styles</b>, <b>Templates</b>, <b>Trims</b> and <b>Widgetized Areas</b> sections use spaces to seperate the input into individual strings. If you need to use a space in a string then you should include an underscore <b>"_"</b> as the space. If you need to use an underscore in a string then you should include a double underscore <b>"__"</b> as the single underscore.' ) . '</p>',
         ) 
      );
   } // action_load_help_tab();

   function action_save_post($page_id) {
      if (!current_user_can('edit_page', $page_id)) {      
      } else if (wp_is_post_autosave($page_id)) {
      } else if (wp_is_post_revision($page_id)) {
      } else if (empty($_POST['usi-theme-page-nonce'])) {
      } else if (!wp_verify_nonce($_POST['usi-theme-page-nonce'], basename(__FILE__))) {
      } else {
         $new_options = array(
            'hide' => !empty($_POST['usi-theme-page-hide']),
            'template' => !empty($_POST['usi-theme-page-template']) ? $_POST['usi-theme-page-template'] : 'default',
         );
         update_post_meta($page_id, '_usi-theme-page', $new_options);
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

   function add_actions() {
      add_action('add_meta_boxes', array($this, 'action_add_meta_boxes'));
      add_action('save_post', array($this, 'action_save_post'));
      add_action('widgets_init', array($this, 'action_widgets_init'));
   } // add_actions();

   function add_filters(){
      if (isset($this->options['admin'])) {
         $options = $this->options['admin'];
         if (!empty($options['admin_bar_menu'])) add_filter('admin_bar_menu', array($this, 'filter_admin_bar_menu'), 25);
         if (isset($options['admin_footer_text'])) add_filter('admin_footer_text', array($this, 'filter_admin_footer_text'));
         if (!empty($options['update_footer'])) add_filter('update_footer', array($this, 'filter_footer_version'), 999);
         define('DISALLOW_FILE_EDIT', isset($options['disable-editor']) ? true : false);
         add_filter('wp_prepare_themes_for_js', array($this, 'filter_themes'));
      }
   } // add_filters();

   function config_section_footer() {
      submit_button(__('Save Changes', USI_WordPress_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return(null);
   } // config_section_footer();

   function fields_sanitize_section($input, $section_id) {
      $new_section = array();
      foreach($input[$section_id] as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_section[$tokens[0]] = $value;
         }
      }
      unset($new_section['new_' . $section_id]);
      $input[$section_id] = $new_section;
      return($input);
   } // fields_sanitize_section();

   function fields_sanitize_updates($input) {
      if (!empty($input['updates']['automatic_updater_disabled'])) {
         $input['updates']['auto_update_core']              = 
         $input['updates']['allow_dev_auto_core_updates']   = 
         $input['updates']['allow_major_auto_core_updates'] = 
         $input['updates']['allow_minor_auto_core_updates'] = 
         $input['updates']['auto_update_plugin']            = 
         $input['updates']['auto_update_theme']             = 
         $input['updates']['auto_update_translation']       = false;
      } else {
         if (!empty($input['updates']['auto_update_core'])) {
            $input['updates']['allow_major_auto_core_updates'] =
            $input['updates']['allow_minor_auto_core_updates'] =
            $input['updates']['allow_dev_auto_core_updates']   = false;
         }
      }
      return($input);
   } // fields_sanitize_updates();

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
   
   function filter_themes($themes) {
      unset($themes['usi-theme-solutions']);
      return($themes);
   } // filter_themes();

   function get_user_role() {
      global $current_user;
      return($current_user->roles[0]);
   } // get_user_role();

   function remove_filters(){
      $options = $this->options['editor'];
      if (!empty($options['remove_the_content_wpautop']))     remove_filter('the_content', 'wpautop');
      if (!empty($options['remove_the_content_wptexturize'])) remove_filter('the_content', 'wptexturize');
      if (!empty($options['remove_the_excerpt_wpautop']))     remove_filter('the_excerpt', 'wpautop');
   } // remove_filters();

   function render_meta_box($post) {

      wp_nonce_field(basename(__FILE__), 'usi-theme-page-nonce');

      $options = get_post_meta($post->ID, '_usi-theme-page', true);
      $hide = !empty($options['hide']);
      $template = !empty($options['template']) ? $options['template'] : 'default';

?>
<p>
  <strong><?php _e('Template',  USI_Theme_Solutions::TEXTDOMAIN); ?></strong>
</p>
<label class="screen-reader-text" for="usi-theme-page-template"><?php _e('Template',  USI_Theme_Solutions::TEXTDOMAIN); ?></label>
<select name="usi-theme-page-template">
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
<input id="usi-theme-page-hide"<?php checked($hide, true); ?> name="usi-theme-page-hide" type="checkbox" value="true" />
<label for="usi-themes-page-hide"><?php _e('Hide from search engines', USI_Theme_Solutions::TEXTDOMAIN); ?></label>
<?php
   } // render_meta_box();

   function sections() {

      $fields_sanitize_section = array($this, 'fields_sanitize_section');

      $wp_header = array(
         'adjacent_posts_rel_link',
         'adjacent_posts_rel_link_wp_head',  // 10,0;
         'emoji_svg_url',
         'feed_links',                       // 2;
         'feed_links_extra',                 // 3;
         'index_rel_link',
         'print_emoji_detection_script',     // 7;
         'print_emoji_styles',
         'rel_canonical',
         'rel_canonical',
         'remove_gutenberg_css',
         'remove_recent_comments_style',
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
      $wp_header_settings = array();
      for ($ith = 0; $ith < count($wp_header); $ith++) {
         $id = $wp_header[$ith];   
         $wp_header_settings[$id] = array('type' => 'checkbox', 'label' => $id);
      }

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
      $widgets_settings = array();
      for ($ith = 0; $ith < count($widgets); $ith++) {
         $id = $widgets[$ith];   
         $widgets_settings[$id] = array('type' => 'checkbox', 'label' => $id);
      }

      $scripts = isset($this->options['scripts']) ? $this->options['scripts'] : array();
      $scripts['new_scripts'] = 'new_scripts';
      $scripts_settings = array();
      foreach ($scripts as $script_value) {
         $tokens = explode(' ', $script_value);
         $id     = $tokens[0];
         $scripts_settings[$id] = array('class' => 'large-text', 'type' => 'text', 'label' => $id);
         if ('new_script' == $id) {
            $scripts_settings[$id]['label'] = __('Add Script', USI_Theme_Solutions::TEXTDOMAIN);
            $scripts_settings[$id]['notes'] = '<i>unique-id &nbsp; script/path/name &nbsp; version &nbsp; footer</i>';
         }
      }

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
      $social_media_settings = array();
      foreach ($social_media as $media_id => $media_title) {
         $social_media_settings[$media_id] = array('class' => 'large-text', 'type' => 'text', 'label' => $media_title);
      }

      $styles = isset($this->options['styles']) ? $this->options['styles'] : array();
      $styles['new_styles'] = 'new_styles';
      $styles_settings = array();
      foreach ($styles as $style_value) {
         $tokens = explode(' ', $style_value);
         $id     = $tokens[0];
         $styles_settings[$id] = array('class' => 'large-text', 'type' => 'text', 'label' => $id);
         if ('new_styles' == $id) {
            $styles_settings[$id]['label'] = __('Add Style', USI_Theme_Solutions::TEXTDOMAIN);
            $styles_settings[$id]['notes'] = '<i>unique-id &nbsp; style/path/name &nbsp; version &nbsp; media</i>';
         }
      }

      $templates = isset($this->options['templates']) ? $this->options['templates'] : array();
      $templates['new_templates'] = 'new_templates';
      $templates_settings = array();
      foreach ($templates as $template_value) {
         $tokens = explode(' ', $template_value);
         $id     = $tokens[0];
         $templates_settings[$id] = array('class' => 'large-text', 'type' => 'text', 'label' => $id);
         if ('new_templates' == $id) {
            $templates_settings[$id]['label'] = __('Add Template', USI_Theme_Solutions::TEXTDOMAIN);
            $templates_settings[$id]['notes'] = '<i>unique-id &nbsp; first-part &nbsp; second-part &nbsp; . . . &nbsp; nth-part</i>';
         }
      }

      $support = array(
         'menus',
         'post-thumbnails',
      );
      $support_settings = array();
      for ($ith = 0; $ith < count($support); $ith++) {
         $id = $support[$ith];   
         $support_settings[$id] = array('type' => 'checkbox', 'label' => $id);
      }

      $trim_urls = isset($this->options['trim_urls']) ? $this->options['trim_urls'] : array();
      $trim_urls['new_trim_urls'] = 'new_trim_urls';
      $trim_urls_settings = array();
      foreach ($trim_urls as $trim_url_value) {
         $tokens = explode(' ', $trim_url_value);
         $id     = $tokens[0];
         $trim_urls_settings[$id] = array('class' => 'large-text', 'type' => 'text', 'label' => $id);
         if ('new_trim_urls' == $id) {
            $trim_urls_settings[$id]['label'] = __('Add URL trim', USI_Theme_Solutions::TEXTDOMAIN);
            $trim_urls_settings[$id]['notes'] = '<i>unique-id &nbsp; long/url &nbsp; short/url</i>';
         }
      }

      $options  = !empty($this->options['updates']) ? $this->options['updates'] : array();
      $notes = array(
         'automatic_updater_disabled' => null,
         'auto_update_core' => null,
         'allow_dev_auto_core_updates' => null,
         'allow_major_auto_core_updates' => null,
         'allow_minor_auto_core_updates' => ' &nbsp; ' . __('WordPress Default', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_plugin' => ' &nbsp; ' . __('Applies only to plugins that support automatic updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_theme' => ' &nbsp; ' . __('Applies only to themes that support automatic updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_translation' => ' &nbsp; ' . __('WordPress Default', USI_Theme_Solutions::TEXTDOMAIN),
      );
      $readonly = array(
         'automatic_updater_disabled' => false,
         'auto_update_core' => true,
         'allow_dev_auto_core_updates' => true,
         'allow_major_auto_core_updates' => true,
         'allow_minor_auto_core_updates' => true,
         'auto_update_plugin' => true,
         'auto_update_theme' => true,
         'auto_update_translation' => true,
      );
      if (empty($options['automatic_updater_disabled'])) {
         $readonly['auto_update_core']   =
         $readonly['auto_update_plugin'] =
         $readonly['auto_update_theme']  = 
         $readonly['auto_update_translation']  = false;
         if (empty($options['auto_update_core'])) {
            $readonly['allow_major_auto_core_updates'] =
            $readonly['allow_minor_auto_core_updates'] =
            $readonly['allow_dev_auto_core_updates']   = false;
         }
      }
      $updates = array(
         'automatic_updater_disabled' => __('Disable All Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_core' => __('Enable All Core Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_dev_auto_core_updates' => __('Enable Development Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_major_auto_core_updates' => __('Enable Major Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_minor_auto_core_updates' => __('Enable Minor Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_plugin' => __('Enable Plugin Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_theme' => __('Enable Theme Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_translation' => __('Enable Translation Updates', USI_Theme_Solutions::TEXTDOMAIN),
      );
      $updates_settings = array();
      $indent = '<span style="display:inline-block; width:16px;"></span>';
      $index  = 0;
      foreach ($updates as $update_id => $update_label) {
         switch ($index++) {
         case 0: $prefix = ''; break;
         case 1:
         case 5: $prefix = $indent; break;
         case 2: $prefix = $indent . $indent; break;
         }
         $updates_settings[$update_id] = array(
            'type' => 'checkbox', 
            'label' => $update_label, 
            'notes' => $notes[$update_id], 
            'prefix' => $prefix, 
            'readonly' => $readonly[$update_id],
         );
      }

      $widget_areas = isset($this->options['widget_areas']) ? $this->options['widget_areas'] : array();
      $widget_areas['new_widget_areas'] = 'new_widget_areas';
      $widget_areas_settings = array();
      foreach ($widget_areas as $widget_area_value) {
         $tokens = explode(' ', $widget_area_value);
         $id     = $tokens[0];
         $widget_areas_settings[$id] = array('class' => 'large-text', 'type' => 'text', 'label' => $id);
         if ('new_widget_areas' == $id) {
            $widget_areas_settings[$id]['label'] = __('Add Widgetized Area', USI_Theme_Solutions::TEXTDOMAIN);
            $widget_areas_settings[$id]['notes'] = '<i>unique-id &nbsp; name &nbsp; description &nbsp; before_widget_html &nbsp; after_widget_html &nbsp; before_title_html &nbsp; after_title_html</i>';
         }
      }

      $sections = array(

         'admin' => array(
            'label' => 'Administrator Pages',
            'settings' => array(
               'admin_bar_menu' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'admin_bar_menu',
               ),
               'admin_footer_text' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'admin_footer_text',
               ),
               'update_footer' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'update_footer',
               ),
               'disable-editor' => array(
                  'type' => 'checkbox', 
                  'label' => 'Disable File Editor',
                  'notes' => 'Disables the plugin and theme code editor.',
               ),
            ),
         ), // admin;

         'editor' => array(
            'label' => 'Editor Functions',
            'settings' => array(
               'remove the_content wptexturize' => array(
                  'type' => 'checkbox', 
                  'label' => 'remove&nbsp;the_content&nbsp;wptexturize',
               ),
               'remove the_content wpautop' => array(
                  'type' => 'checkbox', 
                  'label' => 'remove the_content wpautop',
               ),
               'remove the_excerpt wpautop' => array(
                  'type' => 'checkbox', 
                  'label' => 'remove the_excerpt wpautop',
               ),
            ),
         ), // editor;

         'header' => array(
            'label' => 'Header Functions',
            'settings' => array(
               'base_url' => array(
                  'type' => 'checkbox', 
                  'label' => 'Base URL',
               ),
            ),
         ), // header;

         'jquery' => array(
            'footer_callback' => array($this, 'config_section_footer'),
            'label' => 'jQuery Libraries',
            'settings' => array(
               'load' => array(
                  'type' => 'radio', 
                  'label' => 'Load',
                  'choices' => array(
                     array(
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('Do Not Load', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'header', 
                        'label' => true, 
                        'notes' => __('In Header', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'footer', 
                        'label' => true, 
                        'notes' => __('In Footer', USI_Theme_Solutions::TEXTDOMAIN), 
                     ),
                  ),
               ), // load;
               'source' => array(
                  'type' => 'radio', 
                  'label' => 'Source',
                  'choices' => array(
                     array(
                        'value' => 'google', 
                        'label' => true, 
                        'notes' => __('Google', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'wordpress', 
                        'label' => true, 
                        'notes' => __('WordPress', USI_Theme_Solutions::TEXTDOMAIN), 
                     ),
                  ),
               ), // source;
               'version' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Version',
                  'notes' => 'dummy',
               ),
            ),
         ), // jquery;

         'meta_tags' => array(
            'label' => 'Administrator Pages',
            'settings' => array(
               'copyright' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'copyright',
               ),
               'description' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'description',
               ),
               'format-detection' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'format-detection',
               ),
               'viewport' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'viewport',
               ),
            ),
         ), // meta_tags;

         'miscellaneous' => array(
            'label' => 'Miscellaneous',
            'settings' => array(
               'log_plugin_install_errors' => array(
                  'type' => 'checkbox', 
                  'label' => 'log_plugin_install_errors',
               ),
            ),
         ), // miscellaneous;

         'wp_head' => array(
            'label' => 'Remove wp_head() Items',
            'settings' => $wp_header_settings,
         ), // wp_head;

         'widgets' => array(
            'label' => 'Remove Unused Default Widgets',
            'settings' => $widgets_settings,
         ), // widgets;

         'scripts' => array(
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Scripts',
            'settings' => $scripts_settings,
         ), // widgets;

         'search' => array(
            'label' => 'Search Engine Tools',
            'settings' => array(
               'google_analytics' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Google Analytics',
               ),
               'page_title_suffix' => array(
                  'class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Page Title Suffix',
               ),
            ),
         ), // search;

         'social' => array(
            'label' => 'Social Media Links',
            'settings' => $social_media_settings,
         ), // social;

         'styles' => array(
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Styles',
            'settings' => $styles_settings,
         ), // widgets;

         'templates' => array(
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Templates',
            'settings' => $templates_settings,
         ), // widgets;

         'support' => array(
            'label' => 'Theme Support',
            'settings' => $support_settings,
         ), // support;

         'trim_urls' => array(
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Trim URLs',
            'settings' => $trim_urls_settings,
         ), // trim_urls;

         'updates' => array(
            'fields_sanitize' => array($this, 'fields_sanitize_updates'),
            'label' => 'Automatic Updates',
            'settings' => $updates_settings,
         ), // updates;

         'widget_areas' => array(
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Widgetized Areas',
            'settings' => $widget_areas_settings,
         ), // widget_areas;

      );

      foreach ($sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['notes']))
               $setting['notes'] = '<i>' . __($setting['notes'], USI_Theme_Solutions::TEXTDOMAIN) . '</i>';
         }
      }
      unset($setting);

      return($sections);

   } // sections();

} // Class USI_Theme_Solutions_Settings;

new USI_Theme_Solutions_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('USI')) goto END_OF_FILE;

class USI_Theme_Solutions_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.0.0 (2024-06-23)';

   function __construct() {

      parent::__construct(
         [
            'name' => USI_Theme_Solutions::NAME, 
            'prefix' => USI_Theme_Solutions::PREFIX, 
            'text_domain' => USI_Theme_Solutions::TEXTDOMAIN,
            'options' => USI_Theme_Solutions::$options,
         ]
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
         [$this, 'render_meta_box'], // Render meta box callback;
         'page', // Screen type;
         'side', // Location on page;
         'low' // Priority;
      );
   } // action_add_meta_boxes();

   function action_admin_head($css = null) {
      parent::action_admin_head(
         '.usi-theme-solutions-mono-font{font-family:courier;}' . PHP_EOL
      );
   } // action_admin_head();

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
         [
           'id' => 'scripts',
           'title' => __('Scripts, Styles, Templates, Trims and Widgetized Areas'),
           'content' => '<p>' . __( 'The <b>Scripts</b>, <b>Styles</b>, <b>Templates</b>, <b>Trims</b> and <b>Widgetized Areas</b> sections use spaces to seperate the input into individual strings. If you need to use a space in a string then you should include an underscore <b>"_"</b> as the space. If you need to use an underscore in a string then you should include a double underscore <b>"__"</b> as the single underscore.' ) . '</p>',
         ] 
      );
   } // action_load_help_tab();

   function action_save_post($page_id) {
      if (!current_user_can('edit_page', $page_id)) {
      } else if (wp_is_post_autosave($page_id)) {
      } else if (wp_is_post_revision($page_id)) {
      } else if (empty($_POST['usi-theme-page-nonce'])) {
      } else if (!wp_verify_nonce($_POST['usi-theme-page-nonce'], basename(__FILE__))) {
      } else {
         $new_options = [
            'hide' => !empty($_POST['usi-theme-page-hide']),
            'template' => !empty($_POST['usi-theme-page-template']) ? $_POST['usi-theme-page-template'] : 'default',
         ];
         update_post_meta($page_id, '_usi-theme-page', $new_options);
      }
   } // action_save_post();

   function action_widgets_init() {
      if (isset($this->options['wp_head']['recent_comments_style'])) {
         global $wp_widget_factory;
         remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']);
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

      add_action('add_meta_boxes', [$this, 'action_add_meta_boxes']);
      add_action('save_post', [$this, 'action_save_post']);
      add_action('widgets_init', [$this, 'action_widgets_init']);

   } // add_actions();

   function add_filters(){
      if (isset($this->options['admin'])) {
         $options = $this->options['admin'];
         if (!empty($options['admin_bar_menu'])) add_filter('admin_bar_menu', [$this, 'filter_admin_bar_menu'], 25);
         if (isset($options['admin_footer_text'])) add_filter('admin_footer_text', [$this, 'filter_admin_footer_text']);
         if (!empty($options['update_footer'])) add_filter('update_footer', [$this, 'filter_footer_version'], 999);
         define('DISALLOW_FILE_EDIT', isset($options['disable-editor']) ? true : false);
         add_filter('wp_prepare_themes_for_js', [$this, 'filter_themes']);
      }
   } // add_filters();

   function config_section_footer() {
      submit_button(__('Save Changes', USI_WordPress_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return null;
   } // config_section_footer();

   function fields_sanitize_section($input, $section_id) {
      $new_section = [];
      foreach($input[$section_id] as $key => $value) {
         if (!empty($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $tokens = explode(' ', $value);
            $new_section[$tokens[0]] = $value;
         }
      }
      unset($new_section['new_' . $section_id]);
      $input[$section_id] = $new_section;
      return $input;
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
      return $input;
   } // fields_sanitize_updates();

   function filter_admin_bar_menu($wp_admin_bar) {
      $role = $this->get_user_role();
      $my_account = $wp_admin_bar->get_node('my-account');
      $greating = str_replace(['{role}'], [ucfirst($role)], $this->options['admin']['admin_bar_menu']);
      $new_title = str_replace('Howdy,', $greating, $my_account->title);           
      $wp_admin_bar->add_node(
         [
            'id' => 'my-account',
            'title' => $new_title,
         ]
      );
   } // filter_admin_bar_menu();

   function filter_admin_footer_text() {
      $text = $this->options['admin']['admin_footer_text'];
      echo '<span id="footer-thankyou">' . $text . '</span>';
   } // filter_admin_footer_text();

   function filter_footer_version() {
      return $this->options['admin']['update_footer'];
   } // filter_footer_version();

   function filter_generator_version() {
      return '';
   } // filter_generator_version();
   
   function filter_themes($themes) {
      unset($themes['usi-theme-solutions']);
      return $themes;
   } // filter_themes();

   function get_user_role() {
      global $current_user;
      return !empty($current_user->roles[0]) ? $current_user->roles[0] : 'unknown';
   } // get_user_role();

   function remove_filters(){
      if (!empty($this->options['editor'])) {;
         $options = $this->options['editor'];
         if (!empty($options['remove-wpautop']))         remove_filter('the_content', 'wpautop');
         if (!empty($options['remove-wptexturize']))     remove_filter('the_content', 'wptexturize');
         if (!empty($options['remove-excerpt-wpautop'])) remove_filter('the_excerpt', 'wpautop');
      }
   } // remove_filters();

   function render_meta_box($post) {

      wp_nonce_field(basename(__FILE__), 'usi-theme-page-nonce');

      $options  = get_post_meta($post->ID, '_usi-theme-page', true);
      $hide     = !empty($options['hide']);
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

      $fields_sanitize_section = [$this, 'fields_sanitize_section'];

      $wp_header = [
         'adjacent_posts_rel_link',
         'adjacent_posts_rel_link_wp_head',  // 10,0;
         'classic-theme-styles',
         'emoji_svg_url',
         'feed_links',                       // 2;
         'feed_links_extra',                 // 3;
         'global-styles',
         'gutenberg_css',
         'index_rel_link',
         'print_emoji_detection_script',     // 7;
         'print_emoji_styles',
         'recent_comments_style',
         'rel_canonical',
         'rest_output_link_wp_head',         // 10,0;
         'rsd_link',
         'site_icon_meta_tags', 
         'start_post_rel_link',
         'wlwmanifest_link',
         'wp_generator',
         'wp_oembed_add_discovery_links', 
         'wp_oembed_add_host_js',
         'wp_print_head_scripts',            // 9;
         'wp_print_styles',                  // 8;
         'wp_resource_hints',                // 2;
         'wp_shortlink_wp_head',             // 10,0;
         'wp_site_icon',                     // 99;
      ];
      $wp_header_settings = [];
      for ($ith = 0; $ith < count($wp_header); $ith++) {
         $id = $wp_header[$ith];   
         $wp_header_settings[$id] = ['type' => 'checkbox', 'label' => $id];
      }

      $widgets = [
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
      ];
      $widgets_settings = [];
      for ($ith = 0; $ith < count($widgets); $ith++) {
         $id = $widgets[$ith];   
         $widgets_settings[$id] = ['type' => 'checkbox', 'label' => $id];
      }

      $scripts = isset($this->options['scripts']) ? $this->options['scripts'] : [];
      $scripts['new_scripts'] = 'new_scripts';
      $scripts_settings = [];
      foreach ($scripts as $script_value) {
         $tokens = explode(' ', $script_value);
         $id     = $tokens[0];
         $scripts_settings[$id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $id];
         if ('new_scripts' == $id) {
            $scripts_settings[$id]['label'] = __('Add Script', USI_Theme_Solutions::TEXTDOMAIN);
            $scripts_settings[$id]['notes'] = '<i>unique-id &nbsp; script/path/name &nbsp; version &nbsp; footer</i>';
         }
      }

      $social_media = [
         'facebook' => 'Facebook',
         'flickr' => 'Flickr',
         'googleplus' => 'Google+',
         'instagram' => 'Instagram',
         'linkedin' => 'LinkedIn',
         'pinterest' => 'Pinterest',
         'twitter' => 'Twitter',
         'youtube' => 'YouTube',
      ];
      $social_media_settings = [];
      foreach ($social_media as $media_id => $media_title) {
         $social_media_settings[$media_id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $media_title];
      }

      $styles = isset($this->options['styles']) ? $this->options['styles'] : [];
      $styles['new_styles'] = 'new_styles';
      $styles_settings = [];
      foreach ($styles as $style_value) {
         $tokens = explode(' ', $style_value);
         $id     = $tokens[0];
         $styles_settings[$id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $id];
         if ('new_styles' == $id) {
            $styles_settings[$id]['label'] = __('Add Style', USI_Theme_Solutions::TEXTDOMAIN);
            $styles_settings[$id]['notes'] = '<i>unique-id &nbsp; style/path/name &nbsp; version &nbsp; media</i>';
         }
      }

      $templates = isset($this->options['templates']) ? $this->options['templates'] : [];
      $templates['new_templates'] = 'new_templates';
      $templates_settings = [];
      foreach ($templates as $template_value) {
         $tokens = explode(' ', $template_value);
         $id     = $tokens[0];
         $templates_settings[$id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $id];
         if ('new_templates' == $id) {
            $templates_settings[$id]['label'] = __('Add Template', USI_Theme_Solutions::TEXTDOMAIN);
            $templates_settings[$id]['notes'] = '<i>unique-id &nbsp; first-part &nbsp; second-part &nbsp; . . . &nbsp; nth-part</i>';
         }
      }

      $support = [
         'menus',
         'post-thumbnails',
      ];
      $support_settings = [];
      for ($ith = 0; $ith < count($support); $ith++) {
         $id = $support[$ith];   
         $support_settings[$id] = ['type' => 'checkbox', 'label' => $id];
      }

      $trim_urls = isset($this->options['trim_urls']) ? $this->options['trim_urls'] : [];
      $trim_urls['new_trim_urls'] = 'new_trim_urls';
      $trim_urls_settings = [];
      foreach ($trim_urls as $trim_url_value) {
         $tokens = explode(' ', $trim_url_value);
         $id     = $tokens[0];
         $trim_urls_settings[$id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $id];
         if ('new_trim_urls' == $id) {
            $trim_urls_settings[$id]['label'] = __('Add URL trim', USI_Theme_Solutions::TEXTDOMAIN);
            $trim_urls_settings[$id]['notes'] = '<i>unique-id &nbsp; long/url &nbsp; short/url</i>';
         }
      }

      $options  = !empty($this->options['updates']) ? $this->options['updates'] : [];
      $notes = [
         'automatic_updater_disabled' => null,
         'auto_update_core' => null,
         'allow_dev_auto_core_updates' => null,
         'allow_major_auto_core_updates' => null,
         'allow_minor_auto_core_updates' => ' &nbsp; ' . __('WordPress Default', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_plugin' => ' &nbsp; ' . __('Applies only to plugins that support automatic updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_theme' => ' &nbsp; ' . __('Applies only to themes that support automatic updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_translation' => ' &nbsp; ' . __('WordPress Default', USI_Theme_Solutions::TEXTDOMAIN),
         'disable_admin_notice' => null,
      ];
      $readonly = [
         'automatic_updater_disabled' => false,
         'auto_update_core' => true,
         'allow_dev_auto_core_updates' => true,
         'allow_major_auto_core_updates' => true,
         'allow_minor_auto_core_updates' => true,
         'auto_update_plugin' => true,
         'auto_update_theme' => true,
         'auto_update_translation' => true,
         'disable_admin_notice' => false,
      ];
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
      $updates = [
         'automatic_updater_disabled' => __('Disable All Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_core' => __('Enable All Core Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_dev_auto_core_updates' => __('Enable Development Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_major_auto_core_updates' => __('Enable Major Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'allow_minor_auto_core_updates' => __('Enable Minor Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_plugin' => __('Enable Plugin Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_theme' => __('Enable Theme Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'auto_update_translation' => __('Enable Translation Updates', USI_Theme_Solutions::TEXTDOMAIN),
         'disable_admin_notice' => __('Disable Non-Admin Notices', USI_Theme_Solutions::TEXTDOMAIN),
      ];
      $updates_settings = [];
      $indent = '<span style="display:inline-block; width:16px;"></span>';
      $index  = 0;
      foreach ($updates as $update_id => $update_label) {
         switch ($index++) {
         case 0:
         case 8: $prefix = ''; break;
         case 1:
         case 5: $prefix = $indent; break;
         case 2: $prefix = $indent . $indent; break;
         }
         $updates_settings[$update_id] = [
            'type' => 'checkbox', 
            'label' => $update_label, 
            'notes' => $notes[$update_id], 
            'prefix' => $prefix, 
            'readonly' => $readonly[$update_id],
         ];
      }

      $versions_settings = [
         'version-wordpress' => [
            'type' => 'html', 
            'label' => 'WordPress',
            'html' =>  get_bloginfo('version'),
         ], // version-wordpress;
         'version-parent' => [
            'type' => 'html', 
            'label' => USI_Theme_Solutions::NAME,
            'html' => USI_WordPress_Solutions_Versions::link(
               USI_Theme_Solutions::VERSION, 
               USI_Theme_Solutions::NAME, 
               USI_Theme_Solutions::VERSION, 
               USI_Theme_Solutions::TEXTDOMAIN, 
               __DIR__ // Folder containing plugin or theme;
            ),
         ], // version-parent;
      ];
      $theme = wp_get_theme();
      if (USI_Theme_Solutions::NAME != $theme->Name) {
         $versions_settings['version-child'] = [
            'type' => 'html', 
            'label' => $theme->Name,
            'html' => USI_WordPress_Solutions_Versions::link(
               $theme->Version, 
               $theme->Name,
               $theme->Version, 
               USI_Theme_Solutions::TEXTDOMAIN, 
               get_stylesheet_directory() // Folder containing plugin or theme;
            ),
         ];
      }

      $widget_areas = isset($this->options['widget_areas']) ? $this->options['widget_areas'] : [];
      $widget_areas['new_widget_areas'] = 'new_widget_areas';
      $widget_areas_settings = [];
      foreach ($widget_areas as $widget_area_value) {
         $tokens = explode(' ', $widget_area_value);
         $id     = $tokens[0];
         $widget_areas_settings[$id] = ['f-class' => 'large-text', 'type' => 'text', 'label' => $id];
         if ('new_widget_areas' == $id) {
            $widget_areas_settings[$id]['label'] = __('Add Widgetized Area', USI_Theme_Solutions::TEXTDOMAIN);
            $widget_areas_settings[$id]['notes'] = '<i>unique-id &nbsp; name &nbsp; description &nbsp; before_widget_html &nbsp; after_widget_html &nbsp; before_title_html &nbsp; after_title_html</i>';
         }
      }

      $sections = [

         'admin' => [
            'label' => __('Administrator Pages', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'admin_bar_menu' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'admin_bar_menu',
               ],
               'admin_footer_text' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'admin_footer_text',
               ],
               'update_footer' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'update_footer',
               ],
               'admin_global_message' => [
                  'f-class' => 'large-text usi-theme-solutions-mono-font', 
                  'rows' => 2,
                  'type' => 'textarea', 
                  'label' => 'global_message',
                  'notes' => 'height <|> style <|> message.',
               ],
               'admin_maintanence_message' => [
                  'f-class' => 'large-text usi-theme-solutions-mono-font', 
                  'rows' => 2,
                  'type' => 'textarea', 
                  'label' => 'maintenance_message',
                  'notes' => '&lt;h1&gt;' . get_bloginfo('name') . '&lt;/h1&gt;&lt;br/&gt;Is down for maintenance, it should be up at?<br/>Note - administrators can still sign in if they go to the wp-login.php page.',
               ],
               'disable-editor' => [
                  'type' => 'checkbox', 
                  'label' => __('Disable File Editor', USI_Theme_Solutions::TEXTDOMAIN),
                  'notes' => 'Disables the plugin and theme code editor (recommended).',
               ],
            ],
         ], // admin;

         'editor' => [
            'label' => __('Editor Functions', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'remove-wptexturize' => [
                  'type' => 'checkbox', 
                  'label' => __('remove&nbsp;the_content&nbsp;wptexturize', USI_Theme_Solutions::TEXTDOMAIN),
               ],
               'remove-wpautop' => [
                  'type' => 'checkbox', 
                  'label' => __('remove the_content wpautop', USI_Theme_Solutions::TEXTDOMAIN),
               ],
               'remove-excerpt-wpautop' => [
                  'type' => 'checkbox', 
                  'label' => __('remove the_excerpt wpautop', USI_Theme_Solutions::TEXTDOMAIN),
               ],
            ],
         ], // editor;

         'header' => [
            'label' => __('Header Functions', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'base_url' => [
                  'type' => 'checkbox', 
                  'label' => 'Base URL',
               ],
               'favicon' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'favicon',
               ],
            ],
         ], // header;

         'jquery' => [
            'footer_callback' => [$this, 'config_section_footer'],
            'label' => __('jQuery Libraries', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'load' => [
                  'type' => 'radio', 
                  'label' => 'Load',
                  'choices' => [
                     [
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('Do Not Load', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ],
                     [
                        'value' => 'header', 
                        'label' => true, 
                        'notes' => __('In Header', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ],
                     [
                        'value' => 'footer', 
                        'label' => true, 
                        'notes' => __('In Footer', USI_Theme_Solutions::TEXTDOMAIN), 
                     ],
                  ],
               ], // load;
               'source' => [
                  'type' => 'radio', 
                  'label' => __('Source', USI_Theme_Solutions::TEXTDOMAIN),
                  'choices' => [
                     [
                        'value' => 'google', 
                        'label' => true, 
                        'notes' => __('Google', USI_Theme_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ],
                     [
                        'value' => 'wordpress', 
                        'label' => true, 
                        'notes' => __('WordPress', USI_Theme_Solutions::TEXTDOMAIN), 
                     ],
                  ],
               ], // source;
               'version' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Version',
                  'notes' => 'dummy',
               ],
            ],
         ], // jquery;

         'meta_tags' => [
            'label' => __('Administrator Pages', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'copyright' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'copyright',
               ],
               'description' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'description',
               ],
               'format-detection' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'format-detection',
               ],
               'viewport' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'viewport',
               ],
            ],
         ], // meta_tags;

         'miscellaneous' => [
            'label' => __('Miscellaneous', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'log_plugin_install_errors' => [
                  'type' => 'checkbox', 
                  'label' => 'log_plugin_install_errors',
                  'notes' => 'Only recommended when installing or testing new plugins. &nbsp; <b>Note:</b> The plugin generated n characters of unexpected output error is often caused when the\'re extra characters before or after the </i>&lt;?php ?&gt;<i> tags or if the .php file is not saved as UTF without BOM.',
               ],
               'log_error_get_last' => [
                  'type' => 'checkbox', 
                  'label' => 'log_error_get_last',
                  'notes' => 'Only recommended when doing debugging, calls the error_get_last() method and processes results via usi::log() method.',
               ],
            ],
         ], // miscellaneous;

         'wp_head' => [
            'label' => __('Remove wp_head() Items', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $wp_header_settings,
         ], // wp_head;

         'widgets' => [
            'label' => __('Remove Unused Default Widgets', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $widgets_settings,
         ], // widgets;

         'scripts' => [
            'fields_sanitize' => $fields_sanitize_section,
            'label' => 'Scripts',
            'settings' => $scripts_settings,
         ], // scripts;

         'search' => [
            'label' => __('Search Engine Tools', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => [
               'google_analytics' => [
                  'f-class' => 'large-text usi-theme-solutions-mono-font', 
                  'rows' => 4,
                  'type' => 'textarea', 
                  'label' => 'Google Analytics',
               ],
               'google_analytics_head' => [
                  'type' => 'checkbox', 
                  'label' => __('Analytics in header', USI_Theme_Solutions::TEXTDOMAIN),
                  'notes' => 'If checked code goes in the &lt;head&gt;&lt;/head&gt; tags, otherwise it goes at the bottom of the page.',
               ],
               'google_analytics_admin' => [
                  'type' => 'checkbox', 
                  'label' => __('Analytics in admin', USI_Theme_Solutions::TEXTDOMAIN),
                  'notes' => 'If checked code goes in all pages including administratative pages.',
               ],
               'page_title_suffix' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Page Title Suffix',
               ],
            ],
         ], // search;

         'social' => [
            'label' => __('Social Media Links', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $social_media_settings,
         ], // social;

         'styles' => [
            'fields_sanitize' => $fields_sanitize_section,
            'label' => __('Styles', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $styles_settings,
         ], // widgets;

         'templates' => [
            'fields_sanitize' => $fields_sanitize_section,
            'label' => __('Templates', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $templates_settings,
         ], // widgets;

         'support' => [
            'label' => __('Theme Support', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $support_settings,
         ], // support;

         'trim_urls' => [
            'fields_sanitize' => $fields_sanitize_section,
            'label' => __('Trim URLs', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $trim_urls_settings,
         ], // trim_urls;

         'updates' => [
            'fields_sanitize' => [$this, 'fields_sanitize_updates'],
            'label' => __('Update Options', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $updates_settings,
         ], // updates;

         'versions' => [
            'label' => __('Versions', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $versions_settings,
         ], // versions;

         'widget_areas' => [
            'fields_sanitize' => $fields_sanitize_section,
            'label' => __('Widgetized Areas', USI_Theme_Solutions::TEXTDOMAIN),
            'settings' => $widget_areas_settings,
         ], // widget_areas;

      ];

      foreach ($sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['notes']))
               $setting['notes'] = '<i>' . __($setting['notes'], USI_Theme_Solutions::TEXTDOMAIN) . '</i>';
         }
      }
      unset($setting);

      return $sections;

   } // sections();

} // Class USI_Theme_Solutions_Settings;

new USI_Theme_Solutions_Settings();

END_OF_FILE: // -------------------------------------------------------------------------------------------------------------- // ?>
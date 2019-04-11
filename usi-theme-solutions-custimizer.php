<?php // ------------------------------------------------------------------------------------------------------------------------ //

class USI_Theme_Solutions_Customizer {

   const VERSION = '1.0.7 (2016-10-20)';

   function __construct() {
      add_action('customize_register', array($this, 'setup'), 30);
   } // __construct();

   function setup($wp_customize) {

      $wp_customize->add_section('usi-theme-settings', array(
         'capability' => 'edit_theme_font_sizes',
         'priority' => 35,
         'title' => 'Font Sizes',
      ));
      
      $wp_customize->add_setting('font_h1', array(
         'default' => '1.0em',
      ));
      
      $wp_customize->add_control('font_h1', array(
         'label'   => 'h1',
         'section' => 'usi-theme-settings',
         'type'    => 'text',
      ));
      
      $wp_customize->add_setting('font_p', array(
         'default' => '1.0em',
      ));
      
      $wp_customize->add_control('font_p', array(
         'label'   => 'p',
         'section' => 'usi-theme-settings',
         'type'    => 'text',
      ));
      
      $wp_customize->add_setting('text_color', array(
         'default' => '#000000'
      ));

   } // setup();

} // Class USI_Theme_Solutions_Customizer;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
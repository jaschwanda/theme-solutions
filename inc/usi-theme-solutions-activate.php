<?php // ------------------------------------------------------------------------------------------------------------------------ //

class USI_Theme_Solutions_Activate {

   const VERSION = '1.0.7 (2016-10-20)';

   function __construct() {
      add_action('after_switch_theme', array($this, 'activate'));
      add_action('switch_theme', array($this, 'deactivate'));
   } // __construct();

   function activate() {
   } // activate();

   function deactivate() {
   } // deactivate();

} // Class USI_Theme_Solutions_Activate;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
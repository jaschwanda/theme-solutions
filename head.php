<!doctype html>
<?php // ------------------------------------------------------------------------------------------------------------------------ //
/* Version: 1.2.1 (2019-07-07) */
$usi_theme_base_url = !empty(USI_Theme_Solutions::$options['header']['base_url']);
$usi_theme_title    = get_the_title() . USI_Theme_Solutions::$options['search']['page_title_suffix'];
// --------------------------------------------------------------------------------------------------------------------------- // ?>
<html class="no-js" lang="en">
  <head>
<?php 
   if ($usi_theme_base_url) echo '    <base href="' . USI_Theme_Solutions::$home_url . '" /><!--' . $_SERVER['SERVER_ADDR'] . '-->' . PHP_EOL;
   wp_head();
   echo '    <title>' . $usi_theme_title . '</title>' . PHP_EOL;
?>
  </head>

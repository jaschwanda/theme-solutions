<!doctype html>
<?php // ------------------------------------------------------------------------------------------------------------------------ //
/* Version: 1.5.2 (2021-06-09) */
global $usi_theme_title;
if (empty($usi_theme_title)) $usi_theme_title = get_the_title() . USI_Theme_Solutions::$options['search']['page_title_suffix'] ?? '';
// --------------------------------------------------------------------------------------------------------------------------- // ?>
<html class="no-js" lang="en">
  <head>
<?php 

   if (!empty(USI_Theme_Solutions::$options['header']['base_url'])) {
      echo '    <base href="' . USI_Theme_Solutions::$home_url . '" /><!--' . $_SERVER['SERVER_ADDR'] . '-->' . PHP_EOL;
   }

   if (!empty(USI_Theme_Solutions::$options['header']['favicon'])) {
      echo '    <link href="' . USI_Theme_Solutions::$options['header']['favicon'] . '" rel="shortcut icon">' . PHP_EOL;
   }

   wp_head();

   echo '    <title>' . $usi_theme_title . '</title>' . PHP_EOL;

?>
  </head>

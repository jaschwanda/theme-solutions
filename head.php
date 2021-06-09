<!doctype html>
<?php // ------------------------------------------------------------------------------------------------------------------------ //
/* Version: 1.5.2 (2021-03-22) */
global $usi_theme_title;
$usi_theme_base_url = !empty(USI_Theme_Solutions::$options['header']['base_url']);
$usi_theme_favicon  = !empty(USI_Theme_Solutions::$options['header']['favicon']);
$usi_theme_title    = (!empty($usi_theme_title) ? $usi_theme_title : get_the_title()) . 
   (!empty(USI_Theme_Solutions::$options['search']['page_title_suffix']) 
    ? USI_Theme_Solutions::$options['search']['page_title_suffix'] 
    : ''
   );
// --------------------------------------------------------------------------------------------------------------------------- // ?>
<html class="no-js" lang="en">
  <head>
<?php 
   if ($usi_theme_base_url) echo '    <base href="' . USI_Theme_Solutions::$home_url . '" /><!--' . $_SERVER['SERVER_ADDR'] . '-->' . PHP_EOL;
   if ($usi_theme_favicon)  echo '    <link href="' . USI_Theme_Solutions::$options['header']['favicon'] . '" rel="shortcut icon">' . PHP_EOL;
   wp_head();
   echo '    <title>' . $usi_theme_title . '</title>' . PHP_EOL;
?>
  </head>

<!doctype html>
<?php // ------------------------------------------------------------------------------------------------------------------------ //
/* Version: 1.1.1 (2019-04-11) */
$usi_ts_base_url = !empty(USI_Theme_Solutions::$options['header']['base_url']);
$usi_ts_title = get_the_title() . USI_Theme_Solutions::$options['search']['page_title_suffix'];
// --------------------------------------------------------------------------------------------------------------------------- // ?>
<html class="no-js" lang="en">
  <head>
<?php 
   if ($usi_ts_base_url) echo '    <base href="' . USI_Theme_Solutions::$home_url . '" /><!--' . $_SERVER['SERVER_ADDR'] . '-->' . PHP_EOL;
   wp_head();
   echo '    <title>' . $usi_ts_title . '</title>' . PHP_EOL;
?>
  </head>

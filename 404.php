<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* Version: 1.5.8 (2023-06-20) */ 

$usi_theme_title = '404';

get_template_part('head');

echo PHP_EOL, '  <body>', PHP_EOL;

if (!empty(USI_Theme_Solutions::$options['templates']['404'])) {

   $template_parts = explode(' ', USI_Theme_Solutions::$options['templates']['404']);

   for ($ith = 1; $ith < count($template_parts); $ith++) get_template_part($template_parts[$ith]);

}

wp_footer();

echo PHP_EOL, '  </body>', PHP_EOL, PHP_EOL, '</html>', PHP_EOL;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
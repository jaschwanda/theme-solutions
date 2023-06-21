<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* Version: 1.5.8 (2023-06-20) */ 

get_template_part('head');

echo PHP_EOL, '  <body>', PHP_EOL;

$template_name = !empty(USI_Theme_Solutions::$options_post['template']) ? USI_Theme_Solutions::$options_post['template'] : 'default';

if (!empty(USI_Theme_Solutions::$options['templates'][$template_name])) {

   $template_parts = explode(' ', USI_Theme_Solutions::$options['templates'][$template_name]);

   for ($ith = 1; $ith < count($template_parts); $ith++) get_template_part($template_parts[$ith]);

}

wp_footer();

echo PHP_EOL, '  </body>', PHP_EOL, PHP_EOL, '</html>', PHP_EOL;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
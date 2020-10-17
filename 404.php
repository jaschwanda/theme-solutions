<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* Version: 1.5.1 (2020-10-17) */ 

$usi_theme_title = '404';

get_template_part('head');

echo PHP_EOL, '  <body>', PHP_EOL;

if (!empty(USI_Theme_Solutions::$options['templates']['404'])) {

   $template_parts = explode(' ', USI_Theme_Solutions::$options['templates']['404']);

   for ($ith = 1; $ith < count($template_parts); $ith++) get_template_part($template_parts[$ith]);

}

echo '    <iframe id="null-frame" style="border:0; height:1px; visibility:hidden; width:1px;"></iframe>', PHP_EOL, PHP_EOL;

wp_footer();

echo PHP_EOL, '  </body>', PHP_EOL, PHP_EOL, '</html>', PHP_EOL;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
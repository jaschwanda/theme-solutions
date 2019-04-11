<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* Version: 1.1.1 (2019-04-11) */ 

get_template_part('head');

echo PHP_EOL, '  <body>', PHP_EOL;

global $post;

if ($post) $options_usi_ts_page = get_post_meta($post->ID, '_usi-ts-page', true);

$template_name = !empty($options_usi_ts_page['template']) ? $options_usi_ts_page['template'] : 'default';

if (!empty(USI_Theme_Solutions::$options['templates'][$template_name])) {

   $template_parts = explode(' ', USI_Theme_Solutions::$options['templates'][$template_name]);

   for ($ith = 1; $ith < count($template_parts); $ith++) get_template_part($template_parts[$ith]);

}

echo '    <iframe id="null-frame" style="border:0; height:1px; visibility:hidden; width:1px;"></iframe>', PHP_EOL, PHP_EOL;

wp_footer();

echo PHP_EOL, '  </body>', PHP_EOL, PHP_EOL, '</html>', PHP_EOL;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
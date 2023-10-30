<?php
global $options;
$mods = get_default_mods();
$abs = isset($options['slider_abs']) ? $options['slider_abs'] : 0;
$menu_style = isset($options['slider_style']) ? $options['slider_style'] : 0;
get_header();
do_action('wpcom_render_page', $mods);
?>
<?php get_footer();?>
<?php
global $ptype, $options;
$term_id = get_queried_object_id();
$tpl = get_term_meta( $term_id, 'wpcom_tpl', true );
$banner = get_term_meta( $term_id, 'wpcom_banner', true );
$sidebar = wpcom_get_sidebar();
if($tpl=='product-fullwidth') {
    $tpl = 'product';
    update_term_meta($term_id, 'wpcom_tpl', $tpl);
    update_term_meta($term_id, 'wpcom_sidebar', '0');
}
if ( ! ($tpl && locate_template('templates/loop-' . $tpl . '.php') != '' ) ) {
    $tpl = 'default';
}
if($tpl == 'product'){
    $ptype = isset($options['product_cat_show_type']) ? $options['product_cat_show_type'] : 'p';
    $ptype = $ptype ?: 'p';
}
$class = $sidebar ? 'main' : 'main main-full';
$cols = get_term_meta( $term_id, 'wpcom_cols', true );
$cols = $cols ?: ($sidebar ? 3 : 4);

get_header(); ?>
<?php the_banner(); ?>
    <div class="container wrap">
        <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php do_action('category_before_list');?>
            <?php if(have_posts()) : ?>
                <ul class="post-loop post-loop-<?php echo esc_attr($tpl);?> cols-<?php echo $cols;?>">
                    <?php while( have_posts() ) : the_post();?>
                        <?php get_template_part( 'templates/loop' , $tpl );?>
                    <?php endwhile; ?>
                </ul>
                <?php wpcom_pagination();?>
            <?php else: ?>
                <ul class="post-loop post-loop-default">
                    <?php get_template_part( 'templates/loop' , 'none' ); ?>
                </ul>
            <?php endif;?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
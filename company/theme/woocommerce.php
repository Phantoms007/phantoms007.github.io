<?php
global $options;
$single_tpl = 0;
if(!is_singular( 'product' )){
    $is_sidebar = isset($options['shop_list_sidebar']) ? $options['shop_list_sidebar'] : 0;
} else {
    $is_sidebar = isset($options['shop_single_sidebar']) ? $options['shop_single_sidebar'] : 0;
    $single_tpl = isset($options['shop_single_tpl']) ? $options['shop_single_tpl'] : 0;
}
$class = $is_sidebar && !$single_tpl ? 'main' : 'main main-full';

get_header();?>
<?php the_banner(); ?>
    <div class="container wrap">
        <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php
            if ( is_singular( 'product' ) ) {

                while ( have_posts() ) : the_post();

                    wc_get_template_part( 'content', 'single-product' );

                endwhile;

            } else { ?>

                <?php if ( have_posts() ) : ?>

                    <?php do_action( 'woocommerce_before_shop_loop' ); ?>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php woocommerce_product_subcategories(); ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php wc_get_template_part( 'content', 'product' ); ?>

                    <?php endwhile; // end of the loop. ?>

                    <?php woocommerce_product_loop_end(); ?>

                    <?php do_action( 'woocommerce_after_shop_loop' ); ?>

                <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

                    <?php do_action( 'woocommerce_no_products_found' ); ?>

                <?php endif;

            }
            ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
<?php
// TEMPLATE NAME: 评论模板
wp_enqueue_script( 'comment-reply' );
$sidebar = wpcom_get_sidebar();
$hide_title = get_post_meta( $post->ID, 'wpcom_hide_title', true);
$class = $sidebar ? 'main' : 'main main-full';
get_header();
?>
    <?php the_banner(); ?>
    <div class="container wrap">
        <div class="<?php echo esc_attr($class);?>">
            <?php wpcom_breadcrumb('breadcrumb'); ?>
            <?php while( have_posts() ) : the_post();?>
                <div class="entry">
                    <?php if(!$hide_title && !is_banner_title()){ ?>
                        <h1 class="entry-title"><?php the_title();?></h1>
                    <?php } ?>
                    <div class="entry-content">
                        <?php the_content();?>
                        <?php wpcom_pagination();?>
                    </div>
                    <?php comments_template();?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
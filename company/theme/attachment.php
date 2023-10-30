<?php get_header();?>
    <?php the_banner(); ?>
    <div class="container wrap">
        <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="main">
            <?php while( have_posts() ) : the_post();?>
            <div class="entry">
                <?php if(!is_banner_title()){ ?><h1 class="entry-title"><?php the_title();?></h1><?php } ?>
                <div class="entry-content entry-attachment clearfix">
                    <?php
                    $image_size = apply_filters( 'wporg_attachment_size', 'large' );
                    echo wp_get_attachment_image( get_the_ID(), $image_size );
                    ?>
                    <?php if ( has_excerpt() ) : ?>
                        <div class="entry-caption">
                            <?php the_excerpt(); ?>
                        </div><!-- .entry-caption -->
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
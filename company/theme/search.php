<?php get_header();?>
<?php the_banner(); ?>
    <div class="container wrap">
        <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="main">
            <?php $kw = get_search_query();?>
            <?php if( have_posts() && $kw!='' ) : ?>
                <ul class="post-loop post-loop-default clearfix">
                    <?php while( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'templates/loop' , 'default' ); ?>
                    <?php endwhile; ?>
                </ul>
                <?php wpcom_pagination();?>
            <?php else : ?>
                <ul class="post-loop post-loop-default">
                    <?php get_template_part( 'templates/loop' , 'none' ); ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
<?php
/*
Template Name: 文章详情
Template Post Type: post
*/
global $cat, $options, $post;
if(!$cat){
    $category = get_the_category();
    $cat = $category[0]->cat_ID;
}
$sidebar = wpcom_get_sidebar();
$class = $sidebar ? 'main' : 'main main-full';
$show_indent = isset($options['show_indent']) ? $options['show_indent'] : get_post_meta($post->ID, 'wpcom_show_indent', true);

get_header(); ?>
<?php the_banner();?>
    <div class="container wrap">
        <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php while( have_posts() ) : the_post();?>
                <div class="entry">
                    <?php if(!is_banner_title()){ ?>
                    <h1 class="entry-title"><?php the_title();?></h1>
                    <?php get_template_part('templates/entry', 'meta');?>
                    <?php } ?>
                    <div class="entry-content<?php echo $show_indent?' text-indent':''?>">
                        <?php the_content();?>
                        <?php wpcom_pagination();?>
                    </div>

                    <?php
                    get_template_part('templates/entry', 'footer');
                    get_template_part('templates/entry', 'related');
                    if ( isset($options['comments_open']) && $options['comments_open']=='1' && comments_open() ) comments_template(); ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
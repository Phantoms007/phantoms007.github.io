<?php
get_header();
$sidebar = wpcom_get_sidebar();
$hide_title = get_post_meta( $post->ID, 'wpcom_hide_title', true);
$page_template = get_post_meta($post->ID, '_wp_page_template', true);
if($page_template == 'page-fullwidth.php'){
    update_post_meta($post->ID, '_wp_page_template', 'default');
    update_post_meta($post->ID, 'wpcom_sidebar', '0');
}
$body_classes = implode(' ', apply_filters( 'body_class', array() ));
if(preg_match('/(qapress|member-profile|member-account|member-login|member-register|member-lostpassword)/i', $body_classes)) {
    $hide_title = 1;
}
if($sidebar && preg_match('/(member-account|member-login|member-register|member-lostpassword)/i', $body_classes)){
    $sidebar = 0;
    update_post_meta($post->ID, 'wpcom_sidebar', '0');
}
$class = $sidebar ? 'main' : 'main main-full';
?>
    <?php the_banner(); ?>
    <div class="container wrap">
    <?php wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php while( have_posts() ) : the_post();?>
                <div class="entry">
                    <?php if(!$hide_title && !is_banner_title()){ ?>
                        <h1 class="entry-title"><?php the_title();?></h1>
                    <?php } ?>
                    <div class="entry-content">
                        <?php the_content();?>
                        <?php wpcom_pagination();?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php get_sidebar();?>
    </div>
<?php get_footer();?>
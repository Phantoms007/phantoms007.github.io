<?php
global $post, $options, $tpl;
$style = isset($options['banner_style']) && $options['banner_style'] ? $options['banner_style'] : 0;
$color = isset($options['banner_color']) ? $options['banner_color'] : 0;
$color = $color ? ' banner-black' : '';

$id = isset($args['id']) && $args['id'] ? $args['id'] : get_queried_object_id();
$banner = get_banner($id);
$title = ''; $desc = '';
if($style){
    if(is_singular()){
        $title = get_the_title();
        if(is_page() && $hide_title = get_post_meta( $post->ID, 'wpcom_hide_title', true)){
            $title = '';
        }
        if(!$tpl) $tpl = get_post_meta( $post->ID, '_wp_page_template', true );
        if(is_singular('post') && !preg_match('/product/', $tpl)){
            ob_start();
            get_template_part('templates/entry', 'meta');
            $desc = ob_get_contents();
            ob_end_clean();
        }
    }else if(is_category() || is_tag() || is_tax()){
        $title = single_term_title('', false);
        $desc = term_description();
    }else if(function_exists('is_woocommerce') && is_shop()){
        $title = get_the_title(wc_get_page_id( 'shop' ));
    }else if(is_search()){
        $title = __('Search', 'wpcom');
        ob_start();
        get_search_form();
        $desc = ob_get_contents();
        ob_end_clean();
    }else if(is_post_type_archive()){
        $title = post_type_archive_title('', 0);
    }else if( is_author() ) {
        $title = get_the_author();
    }else if (is_day()) {
        $title = sprintf( __( 'Daily Archives: %s' , 'wpcom' ), get_the_date() );
    }else if (is_month()) {
        $title = sprintf( __( 'Monthly Archives: %s' , 'wpcom' ), get_the_date(__( 'F Y', 'wpcom' )) );
    }else if (is_year()) {
        $title = sprintf( __( 'Yearly Archives: %s' , 'wpcom' ), get_the_date(__( 'Y', 'wpcom' )) );
    }else if(is_404()){
        $title = __('404 Page not found!', 'wpcom');
    }
}
if($title==='' && $desc===''){
    $style = 'none';
}
?>

<div class="banner banner-style-<?php echo $style; echo $color;?>">
    <img class="banner-img" src="<?php echo esc_url($banner);?>" alt="banner">
    <?php if($style) { ?>
        <div class="banner-content">
            <div class="container">
                <?php if($title!==''){ ?><h1 class="banner-title"><?php echo $title;?></h1><?php } ?>
                <?php if($desc!==''){ ?><div class="banner-desc"><?php echo $desc;?></div><?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

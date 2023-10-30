<?php
global $options;
$menu_location = isset($options['menu_location']) ? $options['menu_location'] : 1;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,maximum-scale=1,viewport-fit=cover">
<meta name="format-detection" content="telephone=no">
<title><?php wp_title( isset($options['title_sep']) && $options['title_sep'] ? $options['title_sep'] : ' | ', true, 'right' ); ?></title>
<?php wp_head();?>
<!--[if lte IE 11]><script src="<?php echo get_template_directory_uri()?>/js/update.js"></script><![endif]-->
</head>
<body <?php body_class()?>>
<header id="header" class="header">
    <div class="<?php echo isset($options['header_fluid']) && $options['header_fluid'] ? 'container-fluid':'container' ?> header-wrap">
        <div class="navbar-header">
            <?php $h1_tag = 'div'; if(is_home()||is_front_page()) $h1_tag = 'h1'; ?>
            <<?php echo $h1_tag;?> class="logo">
                <a href="<?php bloginfo('url');?>" rel="home"><img src="<?php echo wpcom_logo()?>" alt="<?php echo esc_attr(get_bloginfo( 'name' ));?>"></a>
            </<?php echo $h1_tag;?>>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-menu">
                <span class="icon-bar icon-bar-1"></span>
                <span class="icon-bar icon-bar-2"></span>
                <span class="icon-bar icon-bar-3"></span>
            </button>
        </div>

        <nav class="collapse navbar-collapse<?php echo $menu_location ? ' navbar-right' : ''; ?> navbar-menu">
            <?php wp_nav_menu( array(
                    'theme_location'    => 'primary',
                    'depth'             => 3,
                    'container'         => '',
                    'menu_class'        => 'nav navbar-nav main-menu',
                    'fallback_cb'       => 'WPCOM_Nav_Walker::fallback',
                    'advanced_menu'     => true,
                    'walker'            => new WPCOM_Nav_Walker())
            );?><!-- /.navbar-collapse -->

            <div class="navbar-action">
                <?php if(!isset($options['search']) || $options['search']==1){ ?>
                    <div class="search-index">
                        <a class="search-icon" href="javascript:;"><?php WPCOM::icon('search');?></a>
                        <?php get_search_form();?>
                    </div><!-- /.search-index -->
                <?php } ?>
                <?php do_action('wpcom_woo_cart_icon');?>

                <?php if( defined('WPMX_VERSION') ) { ?>
                    <div id="j-user-wrap">
                        <a class="login" href="<?php echo wp_login_url(); ?>"><?php _e('Sign in', 'wpcom');?></a>
                        <a class="login register" href="<?php echo wp_registration_url(); ?>"><?php _e('Sign up', 'wpcom');?></a>
                    </div>
                <?php } ?>
            </div>
        </nav>
    </div><!-- /.container -->
</header>
<div id="wrap">
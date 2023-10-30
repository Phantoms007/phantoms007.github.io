<?php

define( 'THEME_ID', '3e51f31a8cc7ba6c' ); // 主题ID，请勿修改！！！
define( 'THEME_VERSION', '5.12.2' ); // 主题版本号，请勿修改！！！

// Themer 框架路径信息常量，请勿修改，框架会用到
define( 'FRAMEWORK_PATH', is_dir($framework_path = get_template_directory() . '/themer') ? $framework_path : get_theme_root() . '/Themer/themer' );
define( 'FRAMEWORK_URI', is_dir($framework_path) ? get_template_directory_uri() . '/themer' : get_theme_root_uri() . '/Themer/themer' );

require FRAMEWORK_PATH .'/load.php';

function add_menu(){
    return array(
        'primary'   => '导航菜单',
        'footer'   => '页脚菜单（仅对简单风格页脚生效）'
    );
}
add_filter('wpcom_menus', 'add_menu');

add_filter( 'the_content', 'filter_content' );

// sidebar
if ( ! function_exists( 'wpcom_widgets_init' ) ) :
    function wpcom_widgets_init() {
        register_sidebar( array(
            'name'          => '页脚工具',
            'id'            => 'footer',
            'description'   => '通用页脚工具',
            'before_widget' => '<div id="%1$s" class="col-md-4 col-sm-8 hidden-xs widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>'
        ) );
    }
endif;
add_action( 'wpcom_sidebar', 'wpcom_widgets_init' );

// Excerpt length
if ( ! function_exists( 'wpcom_excerpt_length' ) ) :
    function wpcom_excerpt_length( $length ) {
        $lang = get_locale();
        if($lang == 'zh_CN' || $lang == 'zh_TW' || $lang == 'zh_HK') {
            return 250;
        } else {
            return 150;
        }
    }
endif;
add_filter( 'excerpt_length', 'wpcom_excerpt_length', 999 );

add_filter('wpcom_image_sizes', 'module_image_sizes', 20);
function module_image_sizes($image_sizes){
    $image_sizes['product-large'] = array(
        'width' => 800,
        'height' => 800
    );
    $image_sizes['product-small'] = array(
        'width' => 150,
        'height' => 150
    );
    return $image_sizes;
}

function get_banner( $id='' ){
    global $options;
    $banner = isset($options['banner']) && $options['banner'] ? $options['banner'] : get_template_directory_uri().'/images/banner.jpg';
    if(is_page() && $url=get_post_meta($id, 'wpcom_banner', true)){
        $banner = $url;
    }else if($id && (is_category() || is_tag() || is_tax() || is_single())){
        $url = get_term_meta( $id, 'wpcom_banner', true );
        $banner = $url ?: $banner;
    }
    return $banner;
}

function the_banner($id='') {
    global $options;
    if( !isset($options['enable_banner']) || (isset($options['enable_banner']) && $options['enable_banner']) ) {
        if(!$id && is_single()){
            global $cat;
            if(!$cat){
                $category = get_the_category();
                $cat = isset($category[0]) ? $category[0]->cat_ID : '';
            }else{
                $id = $cat;
            }
        }
        get_template_part('templates/banner', '', array('id' => $id));
    }
}

function is_banner_title(){
    global $options;
    if (isset($options['banner_style']) && $options['enable_banner'] && $options['banner_style']) {
        return true;
    }
    return false;
}

function get_default_mods(){
    global $options;
    $mods = array();
    if(isset($options['slider_img']) && !empty($options['slider_img']) && $options['slider_img'][0] ){
        $sliders = array();
        $i = 0;
        foreach($options['slider_img'] as $slider){
            $sliders[] = array(
                'img' => $slider,
                'alt' => isset($options['slider_alt'])?$options['slider_alt'][$i]:'',
                'url' => isset($options['slider_url'])?$options['slider_url'][$i]:''
            );
            $i++;
        }
        $mods[] = array(
            'type' => 'swiper',
            'id' => 1,
            'settings'=> array(
                'abs' => $options['slider_abs'],
                'full'=> $options['slider_full'],
                'style' => $options['slider_style'],
                'margin'=> '0 60px',
                'slider'=> $sliders
            )
        );
    }

    if(isset($options['service_tt']) && !empty($options['service_tt']) && $options['service_tt'][0] ){
        $service = array();
        $i = 0;
        foreach($options['service_tt'] as $sv){
            $service[] = array(
                'title' => $sv,
                'desc' => $options['service_desc'][$i],
                'img'=> $options['service_img'][$i],
                'url'=> $options['service_url'][$i]
            );
            $i++;
        }
        $mods[] = array(
            'type' => 'service',
            'id' => 2,
            'settings' => array(
                'title' => $options['service_title'],
                'center' => isset($options['service_center']) ? $options['service_center'] : 0,
                'margin' => '0 60px',
                'service'=> $service
            )
        );
    }

    if(isset($options['prod_cat']) && $options['prod_cat']){
        $mods[] = array(
            'type' => 'product',
            'id' => 3,
            'settings' => array(
                'title' => $options['prod_title'],
                'cat'=> $options['prod_cat'],
                'child'=> isset($options['prod_child']) ? $options['prod_child'] : 0,
                'number'=> $options['prod_number'],
                'show_type' => $options['prod_show_type'],
                'more' => isset($options['prod_more'])?$options['prod_more']:0,
                'margin' => '0 80px'
            )
        );
    }

    if(isset($options['fea_tt']) && !empty($options['fea_tt']) && $options['fea_tt'][0] ){
        $feature = array();
        $i = 0;
        foreach($options['fea_tt'] as $fea){
            $feature[] = array(
                'title' => $fea,
                'desc' => $options['fea_desc'][$i],
                'icon' => $options['fea_icon'][$i],
                'color' => $options['fea_color'][$i],
                'url' => $options['fea_url'][$i]
            );
            $i++;
        }
        $mods[] = array(
            'type' => 'fullwidth',
            'id' => 4,
            'settings' => array(
                'fluid' => 1,
                'bg-color' => '#f9fbff',
                'bg-image' => '',
                'bg-image-repeat' => 'no-repeat',
                'bg-image-position' => 'center center',
                'margin' => '0 60px',
                'padding' => '60px 0',
                'modules' => array(
                    array(
                        'type' => 'feature',
                        'id' => 5,
                        'settings' => array(
                            'title' => $options['fea_title'],
                            'columns' => $options['fea_columns']?$options['fea_columns']:3,
                            'margin'=> 0,
                            'fea'=> $feature
                        )
                    )
                )
            )
        );
    }

    if(isset($options['news_cat']) && $options['news_cat']){
        $mods[] = array(
            'type' => 'news',
            'id' => 6,
            'settings' => array(
                'title' => $options['news_title'],
                'number'=> $options['news_number'],
                'cat'=> $options['news_cat'],
                'child' => $options['news_child'],
                'more' => isset($options['news_more']) ? $options['news_more'] : 1,
                'margin' => '0 60px'
            )
        );
    }

    if(count($mods)==0){
        $mods[] = array(
            'type' => 'text',
            'id' => 1,
            'settings' => array(
                'content' => '<p class="text-center alert alert-warning">默认首页设置请前往后台 <b>主题设置>默认首页</b></p>',
                'margin' => '50px'
            )
        );
    }

    return $mods;
}

add_filter( 'wpcom_custom_css', 'wpcom_style_output' );
if ( ! function_exists( 'wpcom_style_output' ) ) :
    function wpcom_style_output($css){
        global $options;
        if(!isset($options['theme_color'])) return $css;
        ob_start();
        $theme_color = isset($options['theme_color']) && $options['theme_color'] ? WPCOM::color($options['theme_color']) : '';
        $theme_hover = isset($options['theme_color_hover']) && $options['theme_color_hover'] ? WPCOM::color($options['theme_color_hover']) : '';
        $menu_hover = isset($options['menu_hover']) && $options['menu_hover'] ? WPCOM::color($options['menu_hover']) : '';
        $action_color = isset($options['action_color']) && $options['action_color'] ? $options['action_color'] : '';
        $set_title_font = isset($options['sec-title-font']) && $options['sec-title-font'] ? $options['sec-title-font'] : '';
        $lang = get_locale();
        $is_cn = $lang == 'zh_CN' || $lang == 'zh_TW' || $lang == 'zh_HK';
        $fonts = apply_filters('wpcom_font_style', array());
        $font_str = '';
        if($is_cn && $set_title_font && isset($fonts[$set_title_font]) && $fonts[$set_title_font]){
            $font_str = '--theme-title-font: '.$fonts[$set_title_font]['name'].';--theme-title-font-weight: '.$fonts[$set_title_font]['weight'].';--theme-title-font-size: '.$fonts[$set_title_font]['size'].';';
        }else if(!$is_cn){
            $font_str = '--theme-title-font: Lato;--theme-title-font-weight: 900;--theme-title-font-size: 1.2em;';
        }?>
        :root{<?php echo $theme_color ? '--theme-color:'.$theme_color.';' : '';?><?php echo $theme_hover?'--theme-hover:'.$theme_hover.';':'';?><?php echo $action_color?'--action-color:'.$action_color.';':'';?><?php echo !$is_cn ? '--theme-font-family:"Lato", Helvetica, Arial, Verdana, sans-serif;' : '';?><?php echo $font_str;?>}
        <?php
        if($menu_hover) { ?>body.abs .header:not(.fixed) .nav>li.active>a, body.abs .header:not(.fixed) .nav>li>a:hover,body.abs .header:not(.fixed) .navbar-action .profile .menu-item-user:hover, body.abs .header:not(.fixed) .search-icon:hover, body.abs .header:not(.fixed) .shopping-cart > a:hover{color:<?php echo $menu_hover;?>;} body.abs .header:not(.fixed) .nav.menu-hover-style-1>li>a:hover{border-top-color:<?php echo $menu_hover;?>;}body.abs .header:not(.fixed) .nav.menu-hover-style-2>li>a:before, body.abs .header:not(.fixed) .nav.menu-hover-style-3>li>a:before{background:<?php echo $menu_hover;?>;}<?php }
        if(isset($options['menu_item_margin']) && $options['menu_item_margin']){ ?>.header .nav>li{margin-left: <?php echo $options['menu_item_margin'];?>;}@media (max-width: 1199px){.header .nav>li{margin-left: <?php echo intval($options['menu_item_margin'])*0.8;?>px;}}@media (max-width: 1024px){.header .nav>li{margin-left: 0;}}<?php } ?>
        <?php if( isset($GLOBALS['wpmx_options']) && isset($GLOBALS['wpmx_options']['member_login_bg']) && $GLOBALS['wpmx_options']['member_login_bg'] !='' ) { ?>
            .page-no-sidebar.member-login,.page-no-sidebar.member-register{ background-image: url('<?php echo esc_url($GLOBALS['wpmx_options']['member_login_bg']);?>');}
        <?php }if($action_color) {?>.action.action-color-1 .action-item{background-color: <?php echo $action_color;?>;}<?php }
        $header_bg = isset($options['header_bg']) && $options['header_bg'] ? $options['header_bg'] : '';
        if($header_bg){ ?>
            body>header.header, body.header-fixed>header.header.fixed{<?php echo WPCOM::gradient_color($header_bg);?>;}
            @media (max-width: 767px) { body.abs .header, body.abs.menu-white .header{<?php echo WPCOM::gradient_color($header_bg);?>;}}
        <?php }
        $logo = isset($options['logo2']) ? $options['logo2'] : ''; if($logo){
            $logo = is_numeric($logo) ? wp_get_attachment_image_url( $logo, 'full' ) : $logo;?>
            body.abs.better-logo .logo a{display: block;background-image: url(<?php echo $logo;?>);background-size: auto 100%;background-repeat: no-repeat;}
            body.abs.better-logo .logo a img{visibility: hidden;}
            body.abs.better-logo .fixed .logo a{background: none;}
            body.abs.better-logo .fixed .logo a img{visibility: visible;}
            @media (max-width: 767px){body.abs.better-logo .logo a{background: none;}body.abs.better-logo .logo a img{visibility: visible;}}
        <?php } ?>
        <?php if(isset($options['logo-height']) && $logo_height = intval($options['logo-height'])){
            $logo_height = $logo_height>60 ? 60 : $logo_height;
            ?>
            .header .logo img{max-height: <?php echo $logo_height;?>px;}
            .header.fixed .logo img{max-height: <?php echo $logo_height;?>px;}
        <?php } if(isset($options['logo-height-mobile']) && $mob_logo_height = intval($options['logo-height-mobile'])){
            $mob_logo_height = $mob_logo_height>40 ? 40 : $mob_logo_height;
            ?>
            @media (max-width: 767px){
            .header .logo img{max-height: <?php echo $mob_logo_height;?>px;}
            .header.fixed .logo img{max-height: <?php echo $mob_logo_height;?>px;}
            }
        <?php } ?>
        <?php if(get_locale() !== 'zh_CN'){ ?>
            .n-item-wrap .thumb:after{content:'<?php _e('Read More', 'wpcom');?>'!important;width: 80px!important;margin-left: -40px!important;}
        <?php }
        if(!$is_cn){
            echo '.entry .entry-content, .entry .entry-content>p{line-height: 1.47;}';
        }
        if(isset($options['sidebar_left']) && $options['sidebar_left']==0){
            echo '.main{float: left;}.sidebar{float:right;}';
        }
        if(isset($options['theme_set_gray']) && $options['theme_set_gray'] == '1'){
            echo 'html{-webkit-filter: grayscale(100%);filter:grayscale(100%);}';
        }
        echo $options['custom_css'];
        $_css = ob_get_contents();
        ob_end_clean();
        $css .= $_css;
        return $css;
    }
endif;

add_action('wp_head', function(){
    global $options;
    if(isset($options['theme_set_gray']) && $options['theme_set_gray'] == '2' && (is_home() || is_front_page())){
        echo '<style>html{-webkit-filter: grayscale(100%);filter:grayscale(100%);}</style>';
    }
});

// 新旧版本配置信息兼容处理
add_filter( 'option_izt_theme_options', 'wpcom_update_theme_options' );
function wpcom_update_theme_options( $value ){
    if(!$value) return $value;
    if($value && is_string($value)) $value = json_decode($value, true);
    if( !(isset($value['fticon_i']) && $value['fticon_i']) ) {
        $value['fticon_i'] = array();
        $value['fticon_t'] = array();
        $value['fticon_u'] = array();
        if( isset($value['ios']) && trim($value['ios']) ){
            $value['fticon_i'][] = 'apple';
            $value['fticon_t'][] = '1';
            $value['fticon_u'][] = $value['ios'];
        }
        if( isset($value['android']) && trim($value['android']) ){
            $value['fticon_i'][] = 'android';
            $value['fticon_t'][] = '1';
            $value['fticon_u'][] = $value['android'];
        }
        if( isset($value['weixin']) && trim($value['weixin']) ){
            $value['fticon_i'][] = 'weixin';
            $value['fticon_t'][] = '1';
            $value['fticon_u'][] = $value['weixin'];
        }
        if( isset($value['weibo']) && trim($value['weibo']) ){
            $value['fticon_i'][] = 'weibo';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['weibo'];
        }
        if( isset($value['qq_weibo']) && trim($value['qq_weibo']) ){
            $value['fticon_i'][] = 'tencent-weibo';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['qq_weibo'];
        }
        if( isset($value['facebook']) && trim($value['facebook']) ){
            $value['fticon_i'][] = 'facebook';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['facebook'];
        }
        if( isset($value['twitter']) && trim($value['twitter']) ){
            $value['fticon_i'][] = 'twitter';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['twitter'];
        }
        if( isset($value['linkedin']) && trim($value['linkedin']) ){
            $value['fticon_i'][] = 'linkedin';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['linkedin'];
        }
        if( isset($value['instagram']) && trim($value['instagram']) ){
            $value['fticon_i'][] = 'instagram';
            $value['fticon_t'][] = '0';
            $value['fticon_u'][] = $value['instagram'];
        }
    }
    if(isset($value['tongji']) && $value['tongji']) {
        $value['footer_code'] = $value['tongji'];
        unset($value['tongji']);
    }
    if(isset($value['footer_bar_target']) && $value['footer_bar_target']){
        foreach ($value['footer_bar_target'] as $i => $target){
            if($target && $value['footer_bar_url'] && $value['footer_bar_url'][$i] && $value['footer_bar_type'][$i]=='0')
                $value['footer_bar_url'][$i] = $value['footer_bar_url'][$i] . ', _blank';
        }
        unset($value['footer_bar_target']);
    }
    if(isset($value['kl_newwindow']) && $value['kl_newwindow']){
        foreach ($value['kl_newwindow'] as $i => $new){
            if($new && $value['kl_link'] && $value['kl_link'][$i]) $value['kl_link'][$i] = $value['kl_link'][$i] . ', _blank';
        }
        unset($value['kl_newwindow']);
    }
    if(isset($value['kl_nofollow']) && $value['kl_nofollow']){
        foreach ($value['kl_nofollow'] as $i => $nof){
            if($nof && $value['kl_link'] && $value['kl_link'][$i]) $value['kl_link'][$i] = $value['kl_link'][$i] . ', nofollow';
        }
        unset($value['kl_nofollow']);
    }
    return $value;
}

function wpcom_the_post_thumbnail( $size='post-thumbnail' ){
    global $options;
    if( get_the_post_thumbnail(null, $size) ){
        the_post_thumbnail($size);
    }else if(isset($options['wx_thumb']) && $options['wx_thumb']){
        $image_sizes = apply_filters('wpcom_image_sizes', array());
        $thumb_img = is_numeric($options['wx_thumb']) ? wp_get_attachment_image_url( $options['wx_thumb'], 'full' ) : $options['wx_thumb'];
        $thumb = WPCOM::thumbnail($thumb_img, $image_sizes[$size]['width'], $image_sizes[$size]['height'], true, 0, $size);
        if( $thumb && isset($thumb[0]) ) $thumb_img = $thumb[0];
        if( !WPCOM::is_spider() && (!isset($options['thumb_img_lazyload']) || $options['thumb_img_lazyload']=='1') ) { // 非蜘蛛，并且开启了延迟加载
            $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? $options['lazyload_img'] : FRAMEWORK_URI.'/assets/images/lazy.png';
            $lazy = WPCOM::thumbnail($lazy_img, $image_sizes[$size]['width'], $image_sizes[$size]['height'], true, 0, $size);
            if( $lazy && isset($lazy[0]) ) $lazy_img = $lazy[0];

            echo '<img class="size-'.$size.' j-lazy" src="'.$lazy_img.'" data-original="'.$thumb_img.'" alt="'.esc_attr(get_the_title()).'">';
        }else{
            echo '<img class="size-'.$size.'" src="'.$thumb_img.'" alt="'.esc_attr(get_the_title()).'">';
        }
    }
}

function wpcom_post_target(){
    global $options;
    return isset($options['post_target']) && $options['post_target']==='' ? '' : ' target="_blank"';
}

add_filter('wpcom_member_show_profile', '__return_false');

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'wpcom_woo_options_html', 10, 2 );
function wpcom_woo_options_html( $html, $args ){
    // Get selected value.
    if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
        $selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
        $args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( urldecode( wp_unslash( $_REQUEST[ $selected_key ] ) ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
    }

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $id                    = $args['id'] ?: sanitize_title( $attribute );

    if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[ $attribute ];
    }

    $html .= '<div id="' . esc_attr( $id ) . '" class="select-wrap">';

    if ( ! empty( $options ) ) {
        if ( $product && taxonomy_exists( $attribute ) ) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms( $product->get_id(), $attribute, array(
                'fields' => 'all',
            ) );

            foreach ( $terms as $term ) {
                if ( in_array( $term->slug, $options, true ) ) {
                    $html .= '<span class="select-option" data-value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</span>';
                }
            }
        } else {
            foreach ( $options as $option ) {
                // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                $html    .= '<span class="select-option" data-value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</span>';
            }
        }
    }

    $html .= '</div>';
    return $html;
}


if ( ! function_exists( 'woocommerce_template_loop_product_link_open' ) ) {
	function woocommerce_template_loop_product_link_open() {
		global $product;
		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
		echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"' . wpcom_post_target() . '>';
	}
}

add_action('woocommerce_shop_loop_item_title', 'wpcom_woo_product_loop_cat', 9);
function wpcom_woo_product_loop_cat(){
    global $product;
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    if ( !is_wp_error( $terms ) ) {
        $cats = array();
        foreach ( $terms as $term ) {
            $cats[] = $term->name;
        }
        echo '<span class="category-list">' . implode( ', ', $cats ) . '</span>';
    }
}

add_action('wp_enqueue_scripts', 'wpcom_font_scripts');
function wpcom_font_scripts(){
    global $options;
    $lang = get_locale();
    $src = 'https://fonts.googleapis.com/css2';
    $font = 'Lato:wght@400;600;700;900';
    if($lang == 'zh_CN' || $lang == 'zh_TW' || $lang == 'zh_HK') {
        $font = '';
        if(isset($options['sec-title-font']) && $options['sec-title-font']){
            $fonts = apply_filters('wpcom_font_style', array());
            $_font = isset($fonts[$options['sec-title-font']]) ? $fonts[$options['sec-title-font']] : '';
            $font = $_font ? (str_replace(' ', '+', $_font['name']) . ':wght@' . $_font['weight']) : '';
        }
    }
    if($lang == 'zh_CN'){
        $src = 'https://googlefonts.wp-china-yes.net/css2?family=' . $font . '&display=swap';
    }else{
        $src = $src . '?family=' . $font . '&display=swap';
    }
    if((isset($options['google-font-local']) && $options['google-font-local'] == '1') || !isset($options['google-font-local']))
        $src = WPCOM_Static_Cache::get_font_css($src);
    if($font) wp_enqueue_style('wpcom-fonts', preg_replace('/^(http:|https:)/i', '', $src), array('stylesheet'), THEME_VERSION);
}

function wpcom_module_title($title, $subtitle='', $style=''){
    global $options;
    global $title_style;
    $title_style = $style!=='' ? $style : (isset($options['sec-title-style']) ? $options['sec-title-style'] : 1);
    if($title){ ?>
        <div class="sec-title sec-title-<?php echo $title_style;?>">
            <div class="sec-title-wrap">
                <h2><?php echo $title; ?></h2>
                <?php if($subtitle){ ?><span><?php echo $subtitle;?></span><?php } ?>
            </div>
        </div>
    <?php }
}

add_filter('module_default_margin_value', 'wpcom_module_margin');
function wpcom_module_margin(){
    return '0 60px';
}

add_filter('body_class', 'wpcom_body_class_for_theme', 20);
function wpcom_body_class_for_theme($classes){
    global $options;
    if(in_array('page-template-page-home-php', $classes)) { // 可视化编辑器页面
        global $post;
        $modules = get_post_meta($post->ID, '_page_modules', true);
        if (!$modules) $modules = array();
        if (isset($modules['type'])) $modules = array($modules);
    }else if( is_home() && function_exists('get_default_mods') ){ // 默认首页页面
        $modules = get_default_mods();
    }else if(!is_home() && !is_front_page() && isset($options['banner_style']) && $options['banner_style'] == '2' && !in_array('member-account', $classes)) {
        $classes[] = 'abs';
        if(!isset($options['banner_color']) || (isset($options['banner_color']) && !$options['banner_color'])) {
            $classes[] = 'menu-white';
        }else{
            $classes[] = 'menu-default';
        }
    }
    if( isset($modules) && is_array($modules) && $modules ) {
        $abs = 0; $menu_style = 0;
        if(isset($modules[0]) && in_array($modules[0]['type'], array('swiper', 'fullwidth')) && isset($modules[0]['settings']['abs']) && $modules[0]['settings']['abs']==1){
            $abs = 1;
            $menu_style = isset($modules[0]['settings']['style']) ? $modules[0]['settings']['style'] : 0;
        }else if(isset($modules[0]) && $modules[0]['type'] == 'my-module' && isset($modules[0]['settings']['mid']) && $modules[0]['settings']['mid']){
            $post = get_post($modules[0]['settings']['mid']);
            if(isset($post->post_status) && $post->post_status === 'publish') {
                $mds = get_post_meta($post->ID, '_page_modules', true);
                if ($mds && is_array($mds)) {
                    if(isset($mds['type'])) $mds = array($mds);
                    if(isset($mds[0]) && $mds[0]['type'] == 'swiper' && $mds[0]['settings']['abs']==1){
                        $abs = 1;
                        $menu_style = isset($mds[0]['settings']['style']) ? $mds[0]['settings']['style'] : 0;
                    }
                }
            }
        }else if(isset($modules[0]) && $modules[0]['type'] == 'fullwidth' && isset($options['banner_style']) && $options['banner_style'] == '2'){
            $abs = 1;
            $menu_style = $options['banner_color'] == '1' ? 0 : 1;
        }
        if($abs) $classes[] = 'abs';
        if($menu_style){
            $classes[] = 'menu-white';
        }else{
            $classes[] = 'menu-default';
        }
    }
    $header_style = 0;
    if(isset($options['header_bg']) && $options['header_bg'] && $options['header_style'] == '1') {
        $classes[] = 'menu-white';
        $header_style = 1;
    }
    if(!isset($options['header_fixed']) ||  $options['header_fixed'] == '1' ) $classes[] = 'header-fixed';

    if(in_array('abs', $classes) &&
        ( (in_array('menu-default', $classes) && in_array('menu-white', $classes)) || (!in_array('menu-default', $classes) && !$header_style))
    ){
        $classes[] = 'better-logo';
    }
    return $classes;
}

add_filter( 'wpcom_localize_script', function($scripts){
    global $options;
    $scripts['menu_style'] = isset($options['header_bg']) && $options['header_bg'] && $options['header_style'] == '1' ? 1 : 0;

    return $scripts;
});

add_filter('wpcom_font_style', 'wpcom_font_style');
if(!function_exists('wpcom_font_style')){
    function wpcom_font_style($font){
        $font = array(
            '1' => array(
                'name' => 'Noto Serif SC', // 思源宋体
                'weight' => '900',
                'size' => '1em'
            ),
            '2' => array(
                'name' => 'ZCOOL XiaoWei', // 站酷小微
                'weight' => '400',
                'size' => '1.2em'
            ),
            '3' => array(
                'name' => 'ZCOOL QingKe HuangYou', // 站酷庆科黄油
                'weight' => '400',
                'size' => '1.2em'
            )
        );
        return $font;
    }
}

add_action( 'load-post.php', 'wpcom_module_post_script' );
add_action( 'load-post-new.php', 'wpcom_module_post_script' );
function wpcom_module_post_script(){
    add_action('admin_print_footer_scripts', 'wpcom_module_post_script_handle', 20);
}
function wpcom_module_post_script_handle(){ ?>
    <script>
        (function ($){
            const $body = $(document.body);
            $body.off('ready.attr_tpl', '#wpcom-metas').on('ready.attr_tpl', '#wpcom-metas', function (){
                let $label = $('[for="wpcom_attrs"]').closest('.form-group');
                $('#wpcom_attr_tpl').trigger('change', ['']);
                if($label.length && $label.find('.repeat-wrap').length){
                    setTimeout(function(){$('#wpcom_attr_tpl').trigger('change', ['0']);}, 0);
                }
                $body.off('delRepeat.attr_tpl', '.wpcom-panel-repeat').on('delRepeat.attr_tpl', '.wpcom-panel-repeat', function (){
                    $label = $('[for="wpcom_attrs"]').closest('.form-group');
                    if($label.find('.repeat-wrap').length === 0){
                        $('#wpcom_attr_tpl').trigger('change', ['']);
                    }
                });

                $body.off('change.attr_tpl', '[name="_wpcom_attr_tpl"]').on('change.attr_tpl', '[name="_wpcom_attr_tpl"]', function (){
                    let val = $(this).val();
                    $label = $('[for="wpcom_attrs"]').closest('.form-group');
                    if(val && val !== '0'){
                        if(_panel_options && _panel_options['theme-settings'] && _panel_options['theme-settings']['product_attr_id']){
                            let index  = _panel_options['theme-settings']['product_attr_id'].indexOf(val);
                            $label.find('.wpcom-panel-repeat').trigger('repeat', [{
                                pname: _panel_options['theme-settings']['product_attr_name'][index],
                                pvalue: _panel_options['theme-settings']['product_attr_val'][index]
                            }]);
                        }
                    }
                })
            });
        })(jQuery);
    </script>
<?php }

add_filter('wpcom_blocks_script', 'wpcom_exclude_blocks');
function wpcom_exclude_blocks($blocks){
    $blocks['exclude'] = $blocks['exclude'] ?: array();
    $blocks['exclude'][] = 'wpcom/hidden-content';
    return $blocks;
}

add_filter('wpcom_module_options', 'wpcom_fullwidth_module_options', 10, 2);
function wpcom_fullwidth_module_options($ops, $id){
    global $options;
    if($id === 'fullwidth'){
        $_ops = array();
        foreach ($ops[0] as $k => $v){
            $_ops[$k] = $v;
            if($k === 'tab-name'){
                if(!(isset($options['banner_style']) && $options['banner_style'] == '2')){
                    $_ops['abs'] = array(
                        'name' => '置顶显示',
                        'type' => 'toggle',
                        'desc' => '开启后将置顶显示，与头部融合在一起，<b>仅当模块排在第一时有效</b>',
                        'value'  => '1'
                    );
                }
                $_ops['style'] = array(
                    'name' => '菜单风格',
                    'type' => 'r',
                    'filter' => 'abs:1',
                    'options' => array(
                        '0' => '黑色，适合浅色幻灯图片',
                        '1' => '白色，适合深色幻灯图片'
                    )
                );
            }
        }
        $ops[0] = $_ops;
    }
    return $ops;
}

add_filter( 'wp_nav_menu_args', function( $args ){
    global $options;
    $hover_style = isset($options['menu_item_hover']) ? $options['menu_item_hover'] : '1';
    $class = 'menu-hover-style-' . $hover_style;
    if( isset($args['menu_class']) && $args['menu_class'] ){
        $args['menu_class'] .= ' ' . $class;
    }else{
        $args['menu_class'] = $class;
    }
    return $args;
});
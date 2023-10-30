<?php
defined( 'ABSPATH' ) || exit;

// sidebar
add_action( 'widgets_init', 'wpcom_sidebar_init' );
if ( ! function_exists( 'wpcom_sidebar_init' ) ) :
    function wpcom_sidebar_init() {
        global $options;
        $sidebar = array('primary' => '默认边栏');
        if(isset($options['sidebar_id']) && $options['sidebar_id']) {
            foreach ($options['sidebar_id'] as $i => $id) {
                if($id && $options['sidebar_name'][$i]) {
                    $sidebar[$id] = $options['sidebar_name'][$i];
                }
            }
        }

        $sidebar = apply_filters( 'wpcom_sidebars', $sidebar );

        foreach($sidebar as $k=>$v){
            if( $k ) {
                register_sidebar(array(
                    'name' => $v,
                    'id' => $k,
                    'before_widget' => '<div class="widget %2$s">',
                    'after_widget' => '</div>',
                    'before_title' => '<h3 class="widget-title"><span>',
                    'after_title' => '</span></h3>',
                ));
            }
        }
        do_action('wpcom_sidebar');
    }
endif;

add_filter('wpcom_tax_metas', 'wpcom_tax_sidebar_meta');
function wpcom_tax_sidebar_meta( $metas ){
    global $options;
    $sidebar = array('' => ' 默认边栏');

    if(isset($options['sidebar_id']) && $options['sidebar_id']) {
        foreach ($options['sidebar_id'] as $i => $id) {
            if($id && $options['sidebar_name'][$i]) {
                $sidebar[$id] = $options['sidebar_name'][$i];
            }
        }
    }

    $sidebar = apply_filters( 'wpcom_sidebars', $sidebar);

    $exclude_taxonomies = array('nav_menu', 'link_category', 'post_format', 'user-groups', 'qa_cat');
    $taxonomies = get_taxonomies();
    foreach ($taxonomies as $key => $taxonomy) {
        if( ! in_array( $key , $exclude_taxonomies ) ){
            $metas[$key] = isset($metas[$key]) && is_array($metas[$key]) ? $metas[$key] : array();
            $metas[$key][] = array(
                'title' => '显示边栏',
                'type' => 'select',
                'options' => $sidebar,
                'name' => 'sidebar',
                'desc' => '如果有边栏，则显示所选择的边栏'
            );
        }
    }
    return $metas;
}

function wpcom_post_sidebar($post_id, $tax = 'category'){
    global $options;
    $sidebar = get_post_meta( $post_id, 'wpcom_sidebar', true );
    if($sidebar==='') {
        $single_sidebar = isset($options['single_sidebar']) ? $options['single_sidebar'] : '0';
        if($single_sidebar=='0' || $single_sidebar=='1'){
            $category = get_the_terms( $post_id, $tax );
            if ( ! $category || is_wp_error( $category ) ) {
                $category = array();
            }

            $category = array_values( $category );

            foreach ( array_keys( $category ) as $key ) {
                _make_cat_compat( $category[ $key ] );
            }

            $cat = $category && isset($category[0]) ? $category[0]->cat_ID : '';
            $sidebar = $cat ? get_term_meta( $cat, 'wpcom_sidebar', true ) : '';
            if(!$sidebar && $single_sidebar=='0'){
                $sidebar = 'primary';
            }else if($sidebar==='' && $single_sidebar=='1'){
                $sidebar = 'primary';
            }
        }else if($single_sidebar=='2'){
            $sidebar = 'primary';
        }else if($single_sidebar=='3'){
            $sidebar = '0';
        }
    }
    return $sidebar;
}

function wpcom_get_sidebar(){
    global $options, $is_sidebar;
    if(isset($is_sidebar) && !$is_sidebar) return false;
    if(is_home()){
        $sidebar = 'home';
    }else if(is_page()){
        global $wp_query;
        $sidebar = get_post_meta($wp_query->queried_object_id, 'wpcom_sidebar', true);
        if($sidebar === '' && isset($options['page_sidebar']) && $options['page_sidebar'] !== ''){
            $sidebar = $options['page_sidebar'];
        }
    }else if(is_category()){
        global $cat;
        $sidebar = get_term_meta( $cat, 'wpcom_sidebar', true );
        if($sidebar === '' && isset($options['category_sidebar']) && $options['category_sidebar'] !== ''){
            $sidebar = $options['category_sidebar'];
        }
    }else if(is_singular('post')){
        global $wp_query;
        $sidebar = wpcom_post_sidebar($wp_query->queried_object_id);
    }else if(is_tag() || is_tax()){
        $term = get_queried_object();
        $sidebar = get_term_meta( $term->term_id, 'wpcom_sidebar', true );
        if($sidebar === '' && isset($options[$term->taxonomy.'_sidebar']) && $options[$term->taxonomy.'_sidebar'] !== ''){
            $sidebar = $options[$term->taxonomy.'_sidebar'];
        }
    }else if(function_exists('is_woocommerce') && (is_post_type_archive( 'product' ) || is_woocommerce()) ){
        global $wp_query;
        if(is_tax('product_cat')){
            $term = get_queried_object();
            $sidebar = get_term_meta( $term->term_id, 'wpcom_sidebar', true );
        }else if(is_singular( 'product' )){
            $sidebar = wpcom_post_sidebar($wp_query->queried_object_id, 'product_cat');
        }else{
            $sidebar = get_post_meta(wc_get_page_id( 'shop' ), 'wpcom_sidebar', true);
        }
    }
    if(isset($sidebar)){
        $sidebar = $sidebar === '' ? 'primary' : ($sidebar == '0' ? false : $sidebar);
    }else{
        $sidebar = 'primary';
    }
    return apply_filters('wpcom_get_sidebar', $sidebar);
}

function wpcom_sidebar_class($class = ''){
    $classes = 'sidebar';
    if($class) $classes .= ' ' . $class;
    $classes = apply_filters('wpcom_sidebar_classes', $classes);
    echo esc_attr($classes);
}
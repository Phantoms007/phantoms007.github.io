<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_woo_carousel extends WPCOM_Module {
    function __construct() {
        $catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
            'menu_order' => __( 'Default sorting', 'woocommerce' ),
            'popularity' => __( 'Sort by popularity', 'woocommerce' ),
            'rating'     => __( 'Sort by average rating', 'woocommerce' ),
            'date'       => __( 'Sort by latest', 'woocommerce' ),
            'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
            'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
        ) );
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题',
                    'desc' => '可选'
                ),
                'cat' => array(
                    'name' => '商品分类',
                    'type' => 'cat-single',
                    'tax' => 'product_cat',
                    'desc' => '不选择则不区分分类'
                ),
                'orderby' => array(
                    'name' => '商品排序',
                    'type' => 'select',
                    'value' => 'menu_order',
                    'options' => $catalog_orderby_options
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '12'
                )
            ),
            array(
                'tab-name' => '风格样式',
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => '0 60px'
                ),
                'color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                )
            )
        );
        parent::__construct( 'woo_carousel', '商品轮播', $options, 'shopping_bag', '/module/mod-woo_carousel.png' );
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:after' => '{{background-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $cat = $this->value('cat', 0);
        $ordering_args = WC()->query->get_catalog_ordering_args( $this->value('orderby') );
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <div class="hot-slider-wrap">
            <div class="hot-slider j-slider woocommerce columns-4">
                <ul class="swiper-wrapper products">
                    <?php
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => $this->value('number'),
                        'orderby'             => $ordering_args['orderby'],
                        'order'               => $ordering_args['order']
                    );
                    if($cat) $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'id',
                            'terms' => $cat
                        )
                    );
                    if(isset($ordering_args['meta_key']) && $ordering_args['meta_key']) $args['meta_key'] = $ordering_args['meta_key'];
                    $posts = get_posts($args);
                    global $post;
                    foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                        <?php get_template_part( 'templates/loop' , 'woo' ); ?>
                    <?php endforeach; wp_reset_postdata(); ?>
                </ul>
                <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
                <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
            </div>
        </div>
    <?php }
}
if(function_exists('is_woocommerce')) register_module( 'WPCOM_Module_woo_carousel' );
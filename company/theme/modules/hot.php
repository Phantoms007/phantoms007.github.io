<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_hot extends WPCOM_Module{
    function __construct(){
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
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '12'
                ),
                'from' => array(
                    'name' => '文章调用',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'options' => array(
                        '0' => '按分类调用',
                        '1' => '按标签调用'
                    )
                ),
                'cat' => array(
                    'f' => 'from:0',
                    'name' => '分类',
                    'type' => 'cs'
                ),
                'tag' => array(
                    'f' => 'from:1',
                    'name' => '标签',
                    'desc' => '多个标签使用英文逗号隔开'
                ),
                'orderby' => array(
                    'name' => '排序',
                    'type'  => 'select',
                    'value'   => '0',
                    'options' => array(
                        '0' => '发布时间',
                        '1' => '评论数',
                        '2' => '浏览数(需安装WP-PostViews插件)',
                        '3' => '随机排序'
                    )
                ),
                'show_type' => array(
                    'name' => '显示方式',
                    'value' => 'p',
                    't' => 'r',
                    'ux' => 2,
                    'o' => array(
                        'p' => '/module/product-list-tpl-1.png',
                        's' => '/module/product-list-tpl-2.png',
                        'n' => '/module/product-list-tpl-3.png'
                    )
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
        parent::__construct('hot', '产品轮播', $options, 'view_carousel', '/module/mod-hot.png');
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $orderby_id = $this->value('orderby');
        $number = $this->value('number');
        $orderby = 'date';
        $cat = $this->value('cat', 0);
        $tag = $this->value('tag');
        $from = $this->value('from');
        if($orderby_id==1){
            $orderby = 'comment_count';
        }else if($orderby_id==2){
            $orderby = 'meta_value_num';
        }else if($orderby_id==3){
            $orderby = 'rand';
        }
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <div class="hot-slider j-slider">
            <ul class="swiper-wrapper post-loop post-loop-product">
                <?php
                $args = array(
                    'posts_per_page' => $number,
                    'orderby' => $orderby
                );
                if($from==0 && $cat) $args['cat'] = $cat;
                if($from==1 && $tag){
                    $tags = explode(',', $tag);
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'post_tag',
                            'field'    => 'name',
                            'terms'    => $tags
                        ),
                    );
                }
                if($orderby=='meta_value_num') $parg['meta_key'] = 'views';
                $posts = get_posts($args);
                global $post, $ptype;
                $ptype = $this->value('show_type');
                foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                    <?php get_template_part( 'templates/loop', 'product' ); ?>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
            <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
            <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_hot' );
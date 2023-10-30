<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_post_slider extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题',
                    'desc' => '可选'
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '12'
                ),
                'per-view' => array(
                    'name' => '显示图片',
                    'type' => 'r',
                    'ux' => 1,
                    'desc' => '可视区显示的图片数量',
                    'value'  => '4',
                    'options' => array(
                        '3' => '3张',
                        '4' => '4张',
                        '5' => '5张'
                    )
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
                'orderby'    => array(
                    'name' => '排序',
                    'type'  => 'select',
                    'value'   => '0',
                    'options' => array(
                        '0' => '发布时间',
                        '1' => '评论数',
                        '2' => '浏览数(需安装WP-PostViews插件)',
                        '3' => '随机排序'
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => '0 60px'
                )
            )
        );
        parent::__construct('post-slider', '文章轮播', $options, 'view_week', '/module/mod-post-slider.png');
    }
    function style($atts) {
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $orderby_id = $this->value('orderby'); //排序
        $number = $this->value('number'); //显示数量
        $per_view = $this->value('per-view');
        $orderby = 'date';
        $cat = $this->value('cat', 0); //分类
        $tag = $this->value('tag');
        $from = $this->value('from'); //文章调用
        if($orderby_id==1){
            $orderby = 'comment_count';
        }else if($orderby_id==2){
            $orderby = 'meta_value_num';
        }else if($orderby_id==3){
            $orderby = 'rand';
        }
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <div class="post-slider j-slider-<?php echo $atts['modules-id'];?>">
            <ul class="swiper-wrapper post-slider-wrap cols-<?php echo $per_view ?>">
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
                global $post;
                foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                    <li class="post-item">
                        <div class="post-thumb">
                            <?php the_post_thumbnail('full');?>
                        </div>
                        <div class="post-item-hover">
                            <h3 class="post-item-title">
                                <a href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?> rel="bookmark"><?php the_title();?></a>
                            </h3>
                            <div class="post-item-tag"><?php the_tags('', ',');?></div>
                            <a class="post-item-more" href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?> rel="bookmark"><?php _e('Read More', 'wpcom');?><?php WPCOM::icon('arrow-right');?></a>
                        </div>
                    </li>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
            <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
            <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
        </div>

        <script>
            jQuery(function($){
                var args = {
                    slideClass: 'post-item',
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                    simulateTouch: true,
                    breakpoints: {
                        768: {
                            slidesPerView: <?php echo $per_view;?>,
                            simulateTouch: false
                        }
                    }
                };
                $(document.body).trigger('swiper', { el: '.j-slider-<?php echo $this->value('modules-id');?>', args: args });
            });
        </script>
    <?php }
}

register_module( 'WPCOM_Module_post_slider' );
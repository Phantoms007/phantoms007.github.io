<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_review extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题'
                ),
                'review' => array(
                    'type' => 'repeat',
                    'name' => '评价',
                    'o' => array(
                        'author' => array(
                            'name' => '客户称呼'
                        ),
                        'avatar' => array(
                            'name' => '客户头像',
                            'type' => 'upload',
                            'desc' => '客户头像可选填，并且图片宽高1:1，否则显示可能异常'
                        ),
                        'content' => array(
                            'name' => '评价',
                            'type' => 'textarea'
                        )
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'style' => array(
                    'name' => '显示风格',
                    'type' => 'r',
                    'ux' => 1,
                    'value' => '0',
                    'o' => array(
                        '0' => '每次单个显示',
                        '1' => '每次三个显示'
                    )
                ),
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
                    'name' => '文字颜色',
                    'type' => 'color',
                    'desc' => '默认文字颜色'
                ),
                'content-color' => array(
                    'name' => '内容颜色',
                    'type' => 'color',
                    'desc' => '评价内容文字的颜色'
                ),
                'quote-color' => array(
                    'name' => '引号颜色',
                    'type' => 'color',
                    'desc' => '引号修饰符颜色'
                )
            )
        );
        parent::__construct( 'review', '客户评价', $options, 'reviews', '/module/mod-review.png' );
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'content-color' => array(
                '.review-wrap' => '{{color}};'
            ),
            'quote-color' => array(
                '.review-quote' => '{{color}};',
            )
        );
    }
    function template( $atts, $depth ){
        wpcom_module_title($this->value('title'), $this->value('sub-title'));
        $style = $this->value('style'); ?>
        <?php if($this->value('review')){ ?>
            <div class="review-wrap j-review-<?php echo $atts['modules-id'];?> review-style-<?php echo $style;?>">
                <ul class="swiper-wrapper">
                    <?php $a=0; foreach($this->value('review') as $review){ ?>
                        <li class="review-item swiper-slide">
                            <div class="review-item-content">
                                <?php if($style=='0') WPCOM::icon('double-quotes-l', true, 'review-quote');?>
                                <p><?php echo $review['content']?></p>
                                <?php if($style=='0') WPCOM::icon('double-quotes-r', true, 'review-quote review-quote-right');?>
                            </div>
                            <div class="review-item-author">
                                <?php if(isset($review['avatar']) && $review['avatar']) { ?><?php echo wpcom_lazyimg($review['avatar'], $review['author']);?><?php } ?>
                                <p><?php echo $review['author']?></p>
                            </div>
                        </li>
                        <?php $a++; } ?>
                </ul>
                <div class="swiper-pagination"></div>
            </div>
            <script>
                <?php if(isset($a) && $a){ ?>
                jQuery(function($){
                    var args = {
                        slidesPerView: 1,
                        spaceBetween: 0,
                        slidesPerGroup: 1,
                        simulateTouch: true,
                        pagination:{
                            el: '.j-review-<?php echo $this->value('modules-id');?> .swiper-pagination',
                            clickable: true
                        },
                        breakpoints: {
                            768: {
                                slidesPerView: <?php echo $style=='1' ? 3 : 1;?>,
                                slidesPerGroup: <?php echo $style=='1' ? 3 : 1;?>,
                            }
                        }
                    };
                    $(document.body).trigger('swiper', { el: '.j-review-<?php echo $this->value('modules-id');?>', args: args });
                });
                <?php } ?>
            </script>
        <?php } ?>
    <?php }
}
register_module( 'WPCOM_Module_review' );
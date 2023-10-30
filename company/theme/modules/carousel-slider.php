<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_carousel_slider extends WPCOM_Module {
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
                'show-title' => array(
                    'name' => '显示标题',
                    'type' => 'toggle',
                    'desc' => '开启会显示标题，不开启则标题只作为图片的alt信息',
                    'value'  => '1'
                ),
                'per-view' => array(
                    'name' => '显示图片',
                    'type' => 'r',
                    'ux' => 1,
                    'desc' => '可视区显示的图片数量',
                    'value'  => '6',
                    'options' => array(
                        '3' => '3张',
                        '4' => '4张',
                        '5' => '5张',
                        '6' => '6张',
                    )
                ),
                'images' => array(
                    'type' => 'repeat',
                    'name' => '案例',
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'upload'
                        ),
                        'url' => array(
                            'name' => '链接',
                            'type' => 'url'
                        )
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
        parent::__construct('carousel_slider', '轮播案例', $options, 'settings_ethernet', '/module/mod-carousel_slider.png');
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
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>

        <div class="carousel-slider">
            <div class="j-slider-<?php echo $atts['modules-id'];?> cs-inner">
                <ul class="swiper-wrapper">
                    <?php if($this->value('images')){ $i = 0; foreach ( $this->value('images') as $img ) { ?>
                        <li class="swiper-slide">
                            <?php if(isset($img['url'])&&$img['url']){ ?>
                                <a <?php echo WPCOM::url($img['url']);?>>
                                    <?php echo wpcom_lazyimg($img['img'], $img['title']);?>
                                    <?php if($this->value('show-title')){ ?><span class="item-title"><?php echo $img['title'];?></span><?php } ?>
                                </a>
                            <?php }else{ ?>
                                <?php echo wpcom_lazyimg($img['img'], $img['title']);?>
                                <?php if($this->value('show-title')){ ?><span class="item-title"><?php echo $img['title'];?></span><?php } ?>
                            <?php } ?>
                        </li>
                    <?php $i++;}} ?>
                </ul>
            </div>
            <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
            <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
        </div>
        <script>
            <?php
            if(isset($i) && $i){
            $per_view = $this->value('per-view');
            $per_group = 1;
            if($i%2 === 0)  {
                $per_group = 2;
            } else if($i%3 === 0) {
                $per_group = 3;
            } else if($i%$per_view === 0){
                $per_group = $per_view;
            } ?>
            jQuery(function($){
                var args = {
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                    spaceBetween: 10,
                    simulateTouch: true,
                    navigation:{
                        nextEl: '#modules-<?php echo $atts['modules-id'];?> .swiper-button-next',
                        prevEl: '#modules-<?php echo $atts['modules-id'];?> .swiper-button-prev',
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: <?php echo $per_view > 4 ? 4 : 3;?>,
                            slidesPerGroup: 1,
                            simulateTouch: false
                        },
                        1025: {
                            slidesPerView: <?php echo $per_view;?>,
                            slidesPerGroup: <?php echo $per_group;?>,
                            spaceBetween: <?php echo $per_view > 4 ? 10 : 15;?>
                        }
                    }
                };
                $(document.body).trigger('swiper', { el: '.j-slider-<?php echo $this->value('modules-id');?>', args: args });
            });
            <?php } ?>
        </script>
    <?php }
}

register_module( 'WPCOM_Module_carousel_slider' );
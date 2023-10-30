<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_tab_images extends WPCOM_Module {
    function __construct() {
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
                'tabs' => array(
                    'type' => 'repeat',
                    'options' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'desc' => array(
                            'name' => '描述',
                            'type' => 'textarea'
                        ),
                        'icon' => array(
                            'name' => '图标',
                            'type' => 'icon',
                            'img' => 1,
                            'desc' => '如果使用图片建议宽高比例1:1'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'upload',
                            'desc' => '建议每个tab选项的图片宽高比例保持一致'
                        ),
                        'color' => array(
                            'name' => '颜色',
                            'type' => 'color',
                            'desc' => '用于图标以及Tab焦点元素左边线条颜色'
                        )
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'title-color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'color' => array(
                    'name' => '内容颜色',
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
        parent::__construct( 'tab-images', '图片介绍切换', $options, 'vertical_split', '/module/mod-tab-images.png');
    }
    function style( $atts ){
        $style = array(
            'title-color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'color' => array(
                '.ti-tab-item-inner' => '{{color}};'
            )
        );
        if($this->value('tabs')){ foreach($this->value('tabs') as $i => $tab){
            $style['tabs.'.$i.'.color'] = array(
                '.ti-tab-wrap .ti-tab-'.$i.' .ti-tab-icon' => '{{color}};{{border-color}};',
                '.ti-tab-wrap .ti-tab-'.$i.'.active' => '{{border-color}};'
            );
        }}
        return $style;
    }
    function template( $atts, $depth ){
        $tabs = $this->value('tabs');
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <div class="tab-images-wrap">
            <div class="ti-tab-wrap">
                <?php if($tabs){ foreach($tabs as $i => $tab){ ?>
                    <div class="ti-tab-item ti-tab-<?php echo $i;?><?php echo $i===0 ? ' active' : '';?>">
                        <div class="ti-tab-item-inner">
                            <?php WPCOM::icon($tab['icon'], true, 'ti-tab-icon', $tab['title']);?>
                            <h4 class="ti-tab-item-title"><?php echo $tab['title'];?></h4>
                            <p class="ti-tab-item-desc"><?php echo $tab['desc'];?></p>
                        </div>
                    </div>
                    <?php } }?>
            </div>
            <?php if($tabs){ foreach($tabs as $i => $tab){ ?>
                <div class="ti-image<?php echo $i===0 ? ' active' : '';?>">
                    <?php echo wpcom_lazyimg($tab['img'], $tab['title']);?>
                </div>
            <?php } }?>
        </div>
        <script>
            jQuery(function($){
                var winWidth = document.documentElement.clientWidth;
                window.onresize=function(){
                    winWidth = document.documentElement.clientWidth;
                }
                jQuery('#modules-<?php echo $atts['modules-id'];?>').on('mouseover', '.ti-tab-item', function (){
                    var $this = jQuery(this);
                    if(winWidth < 768) return ;
                    var $el = $this.closest('.tab-images-wrap'), index = $this.index(), $wrap = $el.find('.ti-image');
                    $el.find('.ti-tab-item').removeClass('active').eq(index).addClass('active');
                    $wrap.removeClass('active').eq(index).addClass('active');
                    jQuery(window).trigger('scroll');
                }).on('click', '.ti-tab-item', function (){
                    if(winWidth >= 768) return ;
                    var $this = jQuery(this), $el = $this.closest('.tab-images-wrap'), index = $this.index();
                    if(!$this.find('.ti-tab-img').length){
                        var img = $el.find('.ti-image').eq(index).find('img').data('original');
                        img = img ? img : $el.find('.ti-image').eq(index).find('img').attr('src');
                        $this.append('<img class="ti-tab-img" src="'+img+'">');
                    }
                    $el.find('.ti-tab-item').removeClass('active').eq(index).addClass('active');
                    jQuery(window).trigger('scroll');
                });
            });
        </script>
    <?php }
}
register_module( 'WPCOM_Module_tab_images' );
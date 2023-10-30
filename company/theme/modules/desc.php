<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_desc extends WPCOM_Module {
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
                'content' => array(
                    'name' => '内容',
                    'type' => 'editor',
                    'mini' => 1
                ),
                'center' => array(
                    'name' => '居中显示',
                    'type' => 'toggle',
                    'desc' => '模块内容是否居中显示',
                    'value'  => '1'
                ),
                'btn' => array(
                    'name' => '按钮文字',
                    'value'  => '查看更多'
                ),
                'url' => array(
                    'name' => '按钮链接',
                    'type' => 'url',
                    'desc' => '不设置按钮链接则不显示按钮'
                ),
                'more-style' => array(
                    'f' => 'url:!!!',
                    'name' => '按钮风格',
                    'type' => 'r',
                    'value'  => '0',
                    'o' => array(
                        '0' => '暗色风格，适合浅色背景',
                        '1' => '亮色风格，适合深色背景'
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
                    'name' => '文字颜色',
                    'type' => 'color',
                    'desc' => '文字颜色，比如标题的颜色'
                ),
                'content-color' => array(
                    'name' => '内容颜色',
                    'type' => 'color',
                    'desc' => '内容文字颜色'
                )
            )
        );
        parent::__construct( 'desc', '文字简介', $options, 'edit_note', '/module/mod-desc.png' );
    }
    function classes( $atts, $depth ){
        $classes = $depth==0 ? 'container' : '';
        return $classes;
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'content-color' => array(
                '.desc-inner' => '{{color}};',
            )
        );
    }
    function template( $atts, $depth ){
        $center = $this->value('center') == '1';
        $more_style = $this->value('more-style') ? 'light' : 'dark';
        wpcom_module_title($this->value('title'), $this->value('sub-title'), $center ? '' : 4);?>
        <div class="desc-inner<?php echo $center ? ' desc-center':'';?>">
            <p><?php echo $this->value('content'); ?></p>
        </div>
        <?php if($this->value('url')){ ?>
            <div class="module-more<?php echo $center ? ' desc-center':'';?>">
                <a class="btn btn-round btn-<?php echo $more_style;?> more-link" <?php echo WPCOM::url($this->value('url')); ?>>
                    <?php echo $this->value('btn', __('Read more', 'wpcom'));?><?php WPCOM::icon('arrow-right');?>
                </a>
            </div>
        <?php } ?>
    <?php }
}
register_module( 'WPCOM_Module_desc' );
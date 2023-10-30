<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_mix extends WPCOM_Module {
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '标题'
                ),
                'content' => array(
                    'name' => '内容',
                    'type' => 'textarea'
                ),
                'btn' => array(
                    'name' => '按钮文字',
                    'value'  => ''
                ),
                'url' => array(
                    'name' => '按钮链接',
                    'type' => 'url'
                ),
                'img' => array(
                    'name' => '图片',
                    'type' => 'upload'
                ),
                'right' => array(
                    'name' => '图片位置',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'o' => array(
                        '0' => '左边',
                        '1' => '右边'
                    )
                ),
                'text-align' => array(
                    'name' => '文字对齐',
                    'type' => 'r',
                    'ux' => 1,
                    'mobile' => 1,
                    'value'  => 'left',
                    'options' => array(
                        'left' => '<i class="material-icons">format_align_left</i>',
                        'center' => '<i class="material-icons">format_align_center</i>',
                        'right' => '<i class="material-icons">format_align_right</i>'
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
                'padding' => array(
                    'name' => '内间距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'units' => 'px, %',
                    'value'  => '20px'
                ),
                'img-shadow' => array(
                    'name' => '图片阴影效果',
                    'type' => 'toggle',
                    'value'  => '1',
                    'desc' => '开启后图片左(右)下角会显示阴影效果，阴影部分颜色为下方<b>背景颜色/图片阴影颜色</b>选项设置颜色的<b>20%透明度</b>颜色'
                ),
                'color' => array(
                    'name' => '文字颜色',
                    'type' => 'color',
                    'desc' => '用于模块标题、内容'
                ),
                'bg-color' => array(
                    'name' => '背景颜色/图片阴影颜色',
                    'type' => 'color',
                    'desc' => '如果开启图片阴影效果，则为阴影部分的颜色；未开启则为模块背景色'
                ),
                'btn-bg-color' => array(
                    'name' => '按钮背景颜色',
                    'type' => 'color'
                ),
            )
        );
        add_filter('wpcom_module_mix_default_style', array($this, 'default_style'));
        parent::__construct('mix', '图文混排', $options, 'chrome_reader_mode', '/module/mod-mix.png');
    }

    function default_style($style){
        if($style && isset($style['padding'])) {
            unset($style['padding']);
            unset($style['padding_mobile']);
        }
        return $style;
    }

    function style( $atts ){
        return array(
            'color' => array(
                '.mc-item-wrap' => '{{color}};'
            ),
            'bg-color' => array(
                '.mc-shadow-0,.mc-shadow-1 .mc-item-img:before' => '{{background-color}};'
            ),
            'btn-bg-color' => array(
                '.more-link' => '{{background-color}};'
            ),
            'padding' => array(
                '.mc-text' => WPCOM::trbl($this->value('padding'), 'padding', 'tb')
            ),
            'padding_mobile' => array(
                '@[(max-width: 991px)] .mc-text' => WPCOM::trbl($this->value('padding_mobile'), 'padding', 'tb')
            ),
            'text-align_mobile' => array(
                '@[(max-width: 991px)] .mc-text' => 'text-align: {{value}};'
            )
        );
    }

    function template( $atts, $depth ){ ?>
        <div class="mc-item-wrap<?php echo $this->value('right')==1?' mc-item-right':'';?> mc-shadow-<?php echo $this->value('img-shadow') ?>">
            <div class="mc-item">
                <div class="mc-item-img">
                    <?php echo wpcom_lazyimg($this->value('img'), $this->value('title'));?>
                </div>
            </div>
            <div class="mc-item mc-text<?php echo ($this->value('text-align')?' text-'.$this->value('text-align'):'');?>">
                <?php if($this->value('title')){ ?>
                    <h3 class="mc-title"><?php echo $this->value('title'); ?></h3>
                <?php } ?>
                <p><?php echo $this->value('content'); ?></p>
                <?php if($this->value('url')){ ?>
                    <a class="btn btn-primary btn-round btn-lg more-link" <?php echo WPCOM::url($this->value('url')); ?>>
                        <?php echo $this->value('btn', __('Read more', 'wpcom'));?><?php WPCOM::icon('arrow-right-2');?>
                    </a>
                <?php } ?>
            </div>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_mix' );
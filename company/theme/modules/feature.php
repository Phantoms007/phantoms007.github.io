<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_feature extends WPCOM_Module {
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
                'layout' => array(
                    'name' => '风格设置',
                    'value' => '1',
                    't' => 'r',
                    'ux' => 2,
                    'o' => array(
                        '0' => '/module/feature-0.png',
                        '1' => '/module/feature-1.png',
                        '2' => '/module/feature-2.png',
                        '3' => '/module/feature-3.png',
                        '4' => '/module/feature-4.png',
                        '5' => '/module/feature-5.png'
                    )
                ),
                'columns' => array(
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '3',
                    'desc' => '选择每行显示<b>1个或2个</b>的时候单独显示效果可能不太好，建议配合栅格模块和其他模块搭配使用',
                    'options' => array(
                        '1' => '1个',
                        '2' => '2个',
                        '3' => '3个',
                        '4' => '4个',
                        '5' => '5个'
                    )
                ),
                'fea' => array(
                    'type' => 'repeat',
                    'items' => array(
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
                            'desc' => '如果使用图片，尺寸建议为90*90px'
                        ),
                        'color' => array(
                            'name' => '颜色',
                            'type' => 'color',
                            'desc' => '图标如果使用的是图片则此选项可忽略'
                        ),
                        'url' => array(
                            'name' => '链接',
                            'type' => 'url',
                            'desc' => '可选'
                        )
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
                ),
                'desc-color' => array(
                    'name' => '内容颜色',
                    'type' => 'color'
                )
            )
        );
        parent::__construct( 'feature', '特色介绍', $options, 'tips_and_updates', '/module/mod-feature.png' );
    }
    function style( $atts ){
        $style = array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'desc-color' => array(
                '.fea-item-title,.fea-item-desc' => '{{color}};'
            )
        );
        if($this->value('fea')){ foreach($this->value('fea') as $i => $fea){
            $a = $i + 1;
            $style['fea.'.$i.'.color'] = array(
                '.feature-wrap .fea-'.$a.' .fea-icon' => '{{color}};{{border-color}};',
                '.f-layout-1 .fea-'.$a.':hover .fea-icon' => '{{background-color}};color:#fff;',
                '.f-layout-3 .fea-'.$a.' .fea-icon' => '{{background-color}};',
                '.f-layout-4 a.fea-'.$a.':hover .fea-item-title' => '{{color}};'
            );
        }}
        return $style;
    }
    function template( $atts, $depth ){
        $layout =$this->value('layout', 1);
        $columns = $this->value('columns');
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <div class="feature-wrap f-layout-<?php echo $layout;?> cols-<?php echo $columns;?> total-<?php echo (is_array($this->value('fea')) ? count($this->value('fea')) : 0);?>">
            <?php if($this->value('fea')){ $a = 1; foreach($this->value('fea') as $fea){
            $tag = isset($fea['url']) && $fea['url'] ? 'a' : 'div'; ?>
            <<?php echo $tag;?> class="fea-item fea-<?php echo $a;?>" <?php if($tag==='a') echo WPCOM::url($fea['url']);?>>
                <div class="fea-item-wrap">
                    <?php WPCOM::icon($this->get_icon($fea['icon']), true, 'fea-icon', $fea['title']);?>
                    <?php if(isset($fea['title']) && $fea['title']) { ?>
                        <h4 class="fea-item-title"><?php echo $fea['title'];?></h4>
                    <?php } ?>
                    <?php if(isset($fea['desc']) && $fea['desc']) {?>
                        <p class="fea-item-desc"><?php echo $fea['desc'];?></p>
                    <?php } ?>
                    <?php if($layout=='2' && $tag === 'a') echo WPCOM::icon('arrow-right-4', true, 'more-icon');?>
                </div>
            </<?php echo $tag; ?>><?php $a++; } }?>
        </div>
    <?php }
    /**
     * 兼容旧版图标没有前缀的情况
     */
    function get_icon($icon) {
        if (preg_match('/([^:]+):([^:]+)$/', $icon, $m)) {
            return $icon;
        } else {
            return 'fa:' . $icon;
        }
    }
}
register_module( 'WPCOM_Module_feature' );
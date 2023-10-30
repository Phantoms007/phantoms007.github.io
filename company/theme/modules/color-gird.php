<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_color_gird extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题',
                    'desc' => '可选'
                ),
                'sub-title' => array(
                    'name' => '模块副标题',
                    'desc' => '可选'
                ),
                'cols' => array(
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '4',
                    'options' => array(
                        '2' => '2个',
                        '3' => '3个',
                        '4' => '4个',
                        '5' => '5个',
                    )
                ),
                'layout' => array(
                    'name' => '布局风格',
                    'type' => 'r',
                    'ux' => 2,
                    'value'  => '0',
                    'options' => array(
                        '0' => '/module/color-gird-1.png',
                        '1' => '/module/color-gird-2.png',
                        '2' => '/module/color-gird-3.png',
                        '3' => '/module/color-gird-4.png',
                        '4' => '/module/color-gird-5.png',
                    )
                ),
                'colors' => array(
                    'type' => 'repeat',
                    'name' =>'格子选项',
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'icon' => array(
                            'name' => '图标',
                            'img' => 1,
                            'type' => 'icon',
                            'desc' => '如选择了反色风格，建议使用图标，使用图片无法根据设置的颜色自动适配的'
                        ),
                        'url' => array(
                            'name' => '链接',
                            'type' => 'url'
                        ),
                        'desc' => array(
                            'name' => '介绍',
                            'type' => 'textarea'
                        ),
                        'color' => array(
                            'name' => '文字颜色',
                            'type' => 'color'
                        ),
                        'bg-color' => array(
                            'name' => '背景颜色',
                            'type' => 'color'
                        ),
                        'icon-color' => array(
                            'name' => '图标颜色',
                            'type' => 'color'
                        )
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'type' => array(
                    'name' => '显示风格',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'options' => array(
                        '0' => '默认风格',
                        '1' => '反色风格'
                    )
                ),
                'color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'border' => array(
                    'name' => '格子间距',
                    'type' => 'toggle',
                    'desc' => '格子左右之间是否有间距',
                    'value'  => '1'
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
        parent::__construct( 'color-gird', '色彩格子', $options, 'apps', '/module/mod-color-gird.png' );
    }
    function style( $atts ){
        $color = isset($atts['color']) ? $this->value('color') : $this->value('title-color');
        $style = array(
            'color' => array(
                ', .sec-title' => 'color: ' . $color . ';',
                '.sec-title-wrap:after, .sec-title-wrap:before' => 'background-color: ' . $color . ';'
            )
        );

        if($this->value('colors')){
            foreach($this->value('colors') as $index => $color) {
                $style['colors.'.$index.'.color'] = array(
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner' => '{{color}};',
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more' => '{{color}};{{border-color}};',
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more:hover' => '{{background-color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover,.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover .cg-fa' => '{{color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more' => '{{color}};{{border-color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more:hover' => '{{background-color}};'
                );
                $style['colors.'.$index.'.bg-color'] = array(
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner' => '{{background-color}};',
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more:hover' => '{{color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover' => '{{background-color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner:hover .cg-item-more:hover' => '{{color}};',
                    '.cg-layout-4.cg-type-1 .cg-i-'.$index.' .cg-item-inner .cg-fa:before' => '{{color}};',
                    '.cg-type-0.cg-layout-4 .cg-i-'.$index.' .cg-item-inner .cg-fa,.cg-type-1.cg-layout-4 .cg-i-'.$index.' .cg-item-inner:hover .cg-fa' => '{{background-color}};'
                );
                $style['colors.'.$index.'.icon-color'] = array(
                    '.cg-type-0 .cg-i-'.$index.' .cg-item-inner .cg-fa' => '{{color}};',
                    '.cg-type-1 .cg-i-'.$index.' .cg-item-inner .cg-fa' => '{{color}};',
                    '.cg-layout-4 .cg-i-'.$index.' .cg-item-inner .cg-fa:before' => '{{color}};'
                );
            }
        }
        return $style;
    }
    function template($atts, $depth){
        $border = $this->value('border')=='1' ? ' cg-list-border' : '';
        $type = $this->value('type');
        $layout = $this->value('layout');
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <ul class="cg-list<?php echo $border;?> cg-layout-<?php echo $layout;?> cg-type-<?php echo $type;?>">
            <?php if($this->value('colors')){ foreach($this->value('colors') as $index => $color){ ?>
                <li class="cg-item cg-item-<?php echo $this->value('cols');?> cg-i-<?php echo $index;?>">
                    <?php if(($layout=='0'||$layout=='2'||$layout=='4') && $color['url']){ ?><a class="cg-item-inner" <?php echo WPCOM::url($color['url']) ?>><?php } else { echo '<div class="cg-item-inner">'; } ?>
                        <?php
                        if($layout=='0'||$layout=='1') {
                            if ($color['icon']) WPCOM::icon($this->get_icon($color['icon']), true, 'cg-fa', $color['title']);
                            if ($color['title'] != '') {
                                echo '<div class="cg-item-text"><h3 class="cg-title">' . $color['title'] . '</h3></div>';
                            }
                        } else if ($layout=='4') {
                            if ($color['title'] != '') {
                                echo '<div class="flex-left"><div class="cg-item-text"><h3 class="cg-title">' . $color['title'] . '</h3></div>';
                            }
                        } else {
                            if ($color['title'] != '') {
                                echo '<div class="cg-item-text"><h3 class="cg-title">' . $color['title'] . '</h3></div>';
                            }
                            if ($color['icon']) WPCOM::icon($this->get_icon($color['icon']), true, 'cg-fa', $color['title']);
                        }
                        if($layout=='4') {
                            echo '<div class="cg-item-text"><p class="cg-desc">' . $color['desc'] . '</p></div></div>';
                            if ($color['icon']) WPCOM::icon($this->get_icon($color['icon']), true, 'cg-fa', $color['title']);
                        } else if ($color['desc'] != '') {
                            echo '<div class="cg-item-text"><p class="cg-desc">' . $color['desc'] . '</p></div>';
                        }
                        if(($layout=='1'||$layout=='3') && $color['url']) { ?>
                            <a class="cg-item-more" <?php echo WPCOM::url($color['url']);?>><?php _e('Read more', 'wpcom');?></a>
                        <?php } ?>
                        <?php if(($layout=='0'||$layout=='2'||$layout=='4') && $color['url']){ echo '</a>'; } else { echo '</div>';} ?>
                </li>
            <?php }} ?>
        </ul>
    <?php }
    /**
     * 兼容旧版图标没有前缀的情况
     */
    function get_icon($icon){
        if(preg_match('/([^:]+):([^:]+)$/', $icon, $m)){
            return $icon;
        }else{
            return 'fa:'. $icon;
        }
    }
}

register_module( 'WPCOM_Module_color_gird' );
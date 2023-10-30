<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_process extends WPCOM_Module {
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
                'style' => array(
                    'name' => '显示风格',
                    'value' => '0',
                    't' => 'r',
                    'ux' => 2,
                    'o' => array(
                        '0' => '/module/process-0.png',
                        '1' => '/module/process-1.png'
                    )
                ),
                'items' => array(
                    'type' => 'repeat',
                    'max' => 5,
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
                            'img' => 1,
                            'type' => 'icon'
                        ),
                        'color' => array(
                            'name' => '颜色',
                            'type' => 'color',
                            'desc' => '图标如果使用的是图片则此选项可忽略'
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
                    'name' => '文字颜色',
                    'type' => 'color'
                ),
                'line-color' => array(
                    'name' => '虚线颜色',
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
        parent::__construct( 'process', '步骤流程', $options, 'linear_scale', '/module/mod-process.png' );
    }
    function style( $atts ){
        $style = array(
            'title-color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'color' => array(
                '.prcs-content' => '{{color}};'
            ),
            'line-color' => array(
                '.prcs-content' => '{{border-color}};',
                '.prcs-content .prcs-dot' => '{{background-color}};',
                '.prcs-content .prcs-dot:after' => '{{background-color}};'
            )
        );
        if($this->value('items')){ foreach($this->value('items') as $i => $item){
            $style['items.'.$i.'.color'] = array(
                '.prcs-item-' . $i . ' .prcs-icon' => '{{color}};',
            );
        }}
        return $style;
    }
    function template( $atts, $depth ){
        $style = $this->value('style',1);
        wpcom_module_title($this->value('title'), $this->value('sub-title')) ;?>
        <ul class="process prcs-<?php echo $style ?>">
            <?php if($this->value('items')){ foreach($this->value('items') as $i => $item) { ?>
            <li class="prcs-item prcs-item-<?php echo $i;?>">
                <?php WPCOM::icon($item['icon'], true, 'prcs-icon', $item['title']);?>
                <div class="prcs-content">
                    <i class="prcs-dot"></i>
                    <h3 class="prcs-title"><?php echo $item['title'] ?></h3>
                    <p class="prcs-desc"><?php echo $item['desc'] ?></p>
                </div>
            </li>
            <?php }} ?>
        </ul>
    <?php }
}
register_module( 'WPCOM_Module_process' );
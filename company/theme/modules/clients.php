<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_clients extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题'
                ),
                'cols' => array(
                    'name' => '每行显示数量',
                    'value'  => '8',
                    'desc' => '请填写 <b>4-16</b> 之间的数字'
                ),
                'images' => array(
                    'type' => 'repeat',
                    'name' => '案例',
                    'items' => array(
                        'img' => array(
                            'name' => '案例图片',
                            'type' => 'upload'
                        ),
                        'title' => array(
                            'name' => '案例图片alt'
                        ),
                        'url' => array(
                            'name' => '案例链接',
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
        parent::__construct('clients', '客户案例', $options, 'diversity_2', '/module/mod-clients.png');
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title'  => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before'  => '{{background-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <ul class="c-list cols-<?php echo $this->value('cols');?>">
            <?php if($this->value('images')){ foreach ( $this->value('images') as $img ) { ?>
                <li class="c-item">
                    <?php if(isset($img['url'])&&$img['url']){ ?>
                        <a <?php echo WPCOM::url($img['url']);?>>
                            <?php echo wpcom_lazyimg($img['img'], $img['title']);?>
                        </a>
                    <?php }else{ ?>
                        <?php echo wpcom_lazyimg($img['img'], $img['title']);?>
                    <?php } ?>
                </li>
            <?php }} ?>
        </ul>
    <?php }
}
register_module( 'WPCOM_Module_clients' );
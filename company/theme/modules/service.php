<?php
class WPCOM_Module_service extends WPCOM_Module {
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
                'center' => array(
                    'name' => '居中对齐',
                    'type' => 'toggle',
                    'desc' => '文字对齐方式',
                    'value'  => '0'
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
                'service' => array(
                    'type' => 'repeat',
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'desc' => array(
                            'name' => '描述',
                            'type' => 'textarea'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'upload'
                        ),
                        'url' => array(
                            'name' => '链接',
                            'type' => 'url',
                            'desc' => '链接为可选项'
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
                    'name' => '内容标题/简介颜色',
                    'type' => 'color'
                )
            )
        );
        parent::__construct( 'service', '服务内容', $options, 'star', '/module/mod-service.png' );
    }

    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'desc-color' => array(
                '.service-list' => '{{color}};'
            )
        );
    }

    function template( $atts, $depth ){
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <?php
        $class = 'cols-' . $this->value('cols');
        if($this->value('center')=='1') $class .= ' text-center';
        ?>
        <ul class="service-list">
            <?php if($this->value('service')){ foreach($this->value('service') as $service){ ?>
                <li class="service-item <?php echo $class;?>">
                    <div class="service-item-wrap">
                        <?php if($service['url']) { ?>
                            <a <?php echo WPCOM::url($service['url']);?>>
                                <?php echo wpcom_lazyimg($service['img'], $service['title']);?>
                            </a>
                        <?php } else { ?>
                            <?php echo wpcom_lazyimg($service['img'], $service['title']);?>
                        <?php } ?>
                        <h3 class="service-item-title">
                            <?php if($service['url']) { ?>
                                <a <?php echo WPCOM::url($service['url']);?>><?php echo $service['title']; ?></a>
                            <?php } else { ?>
                                <?php echo $service['title']; ?>
                            <?php } ?>
                        </h3>
                        <?php echo $service['desc'] ? '<p class="service-item-desc">'.$service['desc'].'</p>':''; ?>
                    </div>
                </li>
            <?php } } ?>
        </ul>
    <?php }
}
register_module( 'WPCOM_Module_service' );
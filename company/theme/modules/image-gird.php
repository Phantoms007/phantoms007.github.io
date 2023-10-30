<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_image_gird extends WPCOM_Module {
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
                'cols' => array(
                    'name' => '每行显示',
                    'type' => 'select',
                    'desc' => '每行显示图片数量',
                    'value'  => '4',
                    'options' => array(
                        '1' => '1张',
                        '2' => '2张',
                        '3' => '3张',
                        '4' => '4张',
                        '5' => '5张',
                        '6' => '6张'
                    )
                ),
                'images' => array(
                    'type' => 'repeat',
                    'name' => '图片',
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'upload',
                            'desc' => '图片尺寸请根据需要自己统一即可，无其他要求'
                        ),
                        'url' => array(
                            'name' => '链接',
                            'type' => 'url'
                        ),
                        'desc' => array(
                            'name' => '介绍',
                            'type' => 'textarea'
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
                'title-color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'color' => array(
                    'name' => '文字颜色',
                    'type' => 'color',
                    'desc' => '图片上方的文字显示颜色',
                    'value'  => '#ffffff'
                ),
                'bg-color' => array(
                    'name' => '背景颜色',
                    'type' => 'color',
                    'desc' => '背景颜色，例如鼠标悬停后背景色',
                    'value'  => '#0088cc'
                ),
                'center' => array(
                    'name' => '文字居中',
                    'type' => 'toggle',
                    'value'  => '0'
                )
            )
        );
        parent::__construct( 'image-gird', '图片格子', $options, 'view_module', '/module/mod-image-gird.png' );
    }

    function style( $atts ){
        $rgba = '';
        if( $this->value('bg-color') ){
            $rgb = WPCOM::color($this->value('bg-color'), true);
            $rgba = "rgba(".$rgb['r'].",".$rgb['g'].",".$rgb['b'].", ".($rgb['a']==1?0.85:$rgb['a']).")";
        }
        return array(
            'bg-color' => array(
                '.ig-item .ig-item-inner:hover .ig-item-text' => $rgba ? 'background: ' . $rgba . ';' : ''
            ),
            'color' => array(
                '.ig-item .ig-item-text' => '{{color}};'
            ),
            'title-color' => array(
                '.sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            )
        );
    }

    function template($atts, $depth){
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <ul class="ig-list<?php echo $this->value('center')==1 ? ' text-center' : '';?>">
            <?php if($this->value('images')){ foreach($this->value('images') as $image){ ?>
                <li class="ig-item ig-item-<?php echo $this->value('cols');?>">
                    <?php if($image['url']){ ?><a class="ig-item-inner" <?php echo WPCOM::url($image['url']); ?>><?php } else { echo '<div class="ig-item-inner">'; } ?>
                        <?php $alt = $image['title']==''?basename($image['img']):$image['title'];?>
                        <?php echo wpcom_lazyimg($image['img'], $alt);?>
                        <?php if($image['title']!=''||$image['desc']!=''){?><div class="ig-item-text">
                            <?php if($image['title']!=''){?><h3<?php echo $image['desc']==''?' class="ig-title"':'';?>><?php echo $image['title'];?></h3><?php } ?>
                            <?php if($image['desc']!=''){?><p<?php echo $image['title']==''?' class="ig-desc"':'';?>><?php echo $image['desc'];?></p><?php } ?>
                            </div><?php } ?>
                        <?php if($image['url']){ echo '</a>'; } else { echo '</div>';} ?>
                </li>
            <?php }} ?>
        </ul>
    <?php }
}

register_module( 'WPCOM_Module_image_gird' );
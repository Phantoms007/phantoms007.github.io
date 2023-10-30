<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_flex_feature extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题',
                    'desc' => '可选'
                ),
                'cols' => array(
                    'type' => 'repeat',
                    'name' => '图片',
                    'max' => 5,
                    'options' => array(
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
                            'name' => '链接地址',
                            'type' => 'url'
                        ),
                        'more' => array(
                            'name' => '链接文本',
                            'f' => 'url:!!!',
                            'value' => '查看更多'
                        ),
                        'bg-color' => array(
                            'name' => '背景颜色',
                            'type' => 'color'
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
        parent::__construct('flex-feature', '弹性图文介绍', $options, 'view_array', '/module/mod-flex-feature.png');
    }
    function style($atts) {
            $style = array(
                'color' => array(
                    ', .sec-title' => '{{color}};',
                    '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
                )
            );
            if($this->value('cols')){
                foreach($this->value('cols') as $index => $color) {
                    $style['cols.'.$index.'.bg-color'] = array(
                        '.flex-feature-wrap .ff-item-'.$index => '{{background-color}};',
                    );
                }
            }
        return $style;

    }
    function template( $atts, $depth ){
        $cols = $this->value('cols') && is_array($this->value('cols')) ? count($this->value('cols')) : 3;
        if($cols<3) $cols = 3; if($cols>5) $cols = 5;
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <ul class="flex-feature-wrap cols-<?php echo $cols ?>">
            <?php if($this->value('cols')){
                foreach ( $this->value('cols') as $i => $img ) { if($i>=5) break; ?>
                <li class="ff-item ff-item-<?php echo $i?> <?php echo $i ==0? 'active':''?>">
                    <?php if($img['title']){ ?><h3 class="ff-item-title"><?php echo $img['title'] ?></h3><?php } ?>
                    <?php if($img['desc']){ ?><div class="ff-item-desc"><?php echo wpautop($img['desc']); ?></div><?php } ?>
                    <?php if(isset($img['url']) && $img['url']){ ?>
                        <div class="ff-item-go-wrap">
                            <a class="ff-item-go" <?php echo WPCOM::url($img['url']);?>>
                                <?php echo $img['more'] ?: '查看更多'; ?>
                                <?php WPCOM::icon('arrow-right-2');?>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if(isset($img['img'])&&$img['img']){ ?>
                        <?php echo wpcom_lazyimg($img['img'], $img['title']);?>
                    <?php } ?>
                </li>
            <?php }} ?>
        </ul>
        <script>
            jQuery(function($){
                $('#modules-<?php echo $this->value('modules-id');?> .ff-item').mouseenter(function() {
                    $(this).addClass('active').siblings('.ff-item').removeClass('active');
                });
             });
        </script>
    <?php }
}
register_module( 'WPCOM_Module_flex_feature' );
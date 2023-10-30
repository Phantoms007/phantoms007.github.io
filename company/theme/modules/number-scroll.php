<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_number_scroll extends WPCOM_Module {
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
                'numbers' => array(
                    'type' => 'repeat',
                    'max' => 5,
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'num' => array(
                            'name' => '数量',
                            'desc' => '添加的内容必须为数字'
                        ),
                        'unit' => array(
                            'name' => '单位'
                        )
                    )
                ),

            ),
            array(
                'tab-name' => '风格样式',
                'center' => array(
                    'name' => '居中对齐',
                    'type' => 't'
                ),
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
        parent::__construct( 'number-scroll', '数字动效', $options, 'exposure_plus_1', '/module/mod-number-scroll.png');
    }
    function style( $atts ){
        return array(
            'title-color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'color' =>array(
                '.number-item-wrap' => '{{color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $center = $this->value('center');
        wpcom_module_title($this->value('title'), $this->value('sub-title'));
        $length = is_array($this->value('numbers')) ? count($this->value('numbers')) : 5;
        ?>
        <div class="number-scroll cols-<?php echo $length;?>">
            <?php if($this->value('numbers')) {foreach($this->value('numbers') as $i => $num){ ?>
            <div class="number-item-wrap<?php echo $center ? ' text-center' : '' ?>">
                <div class="item-content">
                <h4 class="item-title"><?php echo $num['title'] ?></h4>
                <p class="data">
                    <span id="data-count-<?php echo $atts['modules-id'] . '-' . $i ?>" class="data-number j-countup" data-num="<?php echo esc_attr($num['num']); ?>"><?php echo $num['num'] ?></span>
                    <span class="data-unit"><?php echo $num['unit'] ?></span>
                </p>
                </div>
            </div>
            <?php }} ?>
            <script>
                jQuery(function($) {
                    var flag = true;
                    var $win = jQuery(window), $doc = jQuery(document);
                    var Height = $win.height() - 50;
                    var $dom = jQuery('#modules-<?php echo $atts['modules-id'];?> .number-item-wrap');
                    $win.on('scroll', function () {
                        let scrollTop = $doc.scrollTop();
                        let domTop = $dom.offset().top;
                        if ((scrollTop + Height > domTop) && flag) {
                            for (let i = 0; i < <?php echo $length ?>; i++) {
                                if(CountUpList['data-count-<?php echo $atts['modules-id'] . '-';?>'+i]){
                                    flag = false;
                                    CountUpList['data-count-<?php echo $atts['modules-id'] . '-';?>'+i].start();
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_number_scroll' );
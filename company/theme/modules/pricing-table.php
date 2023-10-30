<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_pricing_table extends WPCOM_Module{
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
                'style' => array(
                    'name' => '风格设置',
                    'type' => 'r',
                    'ux' => 2,
                    'value' => '0',
                    'options' => array(
                        '0' => '风格1||/module/pricing-table-0.png',
                        '1' => '风格2||/module/pricing-table-1.png',
                        '2' => '风格3||/module/pricing-table-2.png'
                    )
                ),
                'sign' => array(
                    'name' => '货币符号',
                    'value' => '￥'
                ),
                'recommend' => array(
                    'name' => '设置推荐方案',
                    'value'  => '1',
                    'desc' => '填写推荐方案序号'
                ),
                'items' => array(
                    'type' => 'repeat',
                    'name' => '价格方案',
                    'max' => 5,
                    'options' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'sub-title' => array(
                            'f' => 'style:0',
                            'name' => '副标题'
                        ),
                        'content' => array(
                            'name' => '描述内容',
                            'type' => 'editor'
                        ),
                        'price' => array(
                            'name' => '价格'
                        ),
                        'unit' => array(
                            'name' => '单位',
                            'desc' => '风格3为计费周期说明'
                        ),
                        'btn' => array(
                            'name' => '按钮文本'
                        ),
                        'url' => array(
                            'f' => 'btn:!!!',
                            'name' => '按钮链接',
                            'type' => 'url'
                        ),
                        'color' =>array(
                            'name' => '标题文字颜色',
                            'type' => 'color',
                            'desc' => '风格3为价格和高亮颜色'
                        ),
                        'bg-color' => array(
                            'f' => 'style:0,style:1',
                            'name' => '标题背景颜色',
                            'type' => 'c',
                            'gradient' => 1
                        ),
                        'desc' => array(
                            'name' => '右上角推荐文案',
                            'desc' => '可选，右上角推荐文案'
                        ),
                        're-color' => array(
                            'f' => 'desc:!!!',
                            'name' => '推荐文案背景颜色',
                            'type' => 'color',
                            'gradient' => 1
                        )
                    )
                ),
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
        parent::__construct('pricing-table', '价格表', $options, 'local_offer', '/module/mod-pricing-table.png');
    }
    function get_li_prefix($color){
        $color = preg_replace('/^#/', '%23', $color);
        return 'background-image: url("data:image/svg+xml,%3Csvg class=\'icon\' viewBox=\'0 0 1024 1024\' xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Cpath d=\'M426.688 647.36L818.88 255.104l60.352 60.352L426.688 768 155.136 496.448l60.352-60.288z\' fill=\''.$color.'\'/%3E%3C/svg%3E");';
    }
    function style($atts) {
        global $options;
        $theme_color = isset($options['theme_color']) && $options['theme_color'] ? WPCOM::color($options['theme_color']) : '#206be7';
        $style = array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'items' => array(
                '.pricing-table.pt-style-1 .pt-content li:before, .pricing-table.pt-style-2 .pt-content li:before' => $this->get_li_prefix($theme_color)
            )
        );
        if($this->value('items')){
            foreach($this->value('items') as $index => $item) {
                $item['bg-color'] = isset($item['bg-color']) ? $item['bg-color'] : '';
                $item['re-color'] = isset($item['re-color']) ? $item['re-color'] : '';
                $bg_rgba = WPCOM::color(isset($item['bg-color']) ? $item['bg-color'] : '#000', 1);
                $style['items.'.$index.'.bg-color'] = array(
                    '.pricing-table .pt-item-'.$index.' .pt-header, .pricing-table .pt-item-'.$index.':hover .pt-btn, .pricing-table .pt-item-'.$index.'.active .pt-btn'=> WPCOM::gradient_color($item['bg-color']),
                    '.pt-style-1 .pt-item-'.$index.' .pt-box .pt-btn::before' => WPCOM::gradient_color($item['bg-color']),
                    '.pt-style-1 .pt-item-'.$index.' .pt-btn' => 'color: ' . WPCOM::color($item['bg-color']) .';',
                    '.pricing-table .pt-item-'.$index.':hover .pt-btn' => 'color: #fff;',
                    '.pt-style-0 .pt-item-'.$index.' .pt-content li:before,.pt-style-1 .pt-item-'.$index.' .pt-content li:after' => WPCOM::gradient_color($item['bg-color']),
                    '.pricing-table.pt-style-1 .pt-item-'.$index.' .pt-content li:before' => $this->get_li_prefix(WPCOM::color($item['bg-color'])),
                    '.pricing-table .pt-item-'.$index.':hover, .pricing-table .pt-item-'.$index.'.active' => 'box-shadow: 0 10px 20px 0 rgba('.$bg_rgba['r'].', '.$bg_rgba['g'].', '.$bg_rgba['b'].', 0.15);'
                );
                $style['items.'.$index.'.color'] = array(
                    'pt-style-0 .pt-item-'.$index.' .pt-header,.pt-style-1 .pt-item-'.$index.' .pt-header,.pt-style-2 .pt-item-'.$index.' .pt-price-wrap' => '{{color}};',
                    '.pricing-table .pt-item-'.$index.':hover .pt-btn,.pricing-table .pt-item-'.$index.'.active .pt-btn,.pt-style-2 .pt-item-'.$index.' .pt-btn' => '{{color}};',
                    '.pt-style-2 .pt-item-'.$index.':hover,.pt-style-2 .pt-item-'.$index.'.active' => 'border-top-color: {{value}};',
                    '.pricing-table.pt-style-2 .pt-item-'.$index.' .pt-content li:before' => $this->get_li_prefix(WPCOM::color($item['color'])),
                    '.pricing-table.pt-style-2 .pt-item-'.$index.' .pt-btn:before,.pt-style-2 .pt-item-'.$index.':hover .pt-btn,.pt-style-2 .pt-item-'.$index.'.active .pt-btn' => '{{background-color}};',
                    '.pt-style-2 .pt-item-'.$index.':hover .pt-btn,.pt-style-2 .pt-item-'.$index.'.active .pt-btn' => 'color: #fff;',
                );
                $style['items.'.$index.'.re-color'] = array(
                    '.pricing-table .pt-item-'.$index.' .pt-recommend' => WPCOM::gradient_color($item['re-color'])
                );
            }
        }
        return $style;
    }
    function template( $atts, $depth ){
        $style = $this->value('style', '0');
        $items = $this->value('items');
        $recommend = $this->value('recommend');
        $per_view = $items && is_array($items) ? count($items) : 3;
        $sign = $this->value('sign', '￥');

        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <ul class="pricing-table pt-style-<?php echo $style;?> cols-<?php echo $per_view ?>">
            <?php if($items){foreach($items as $i => $item){ ?>
                <li class="pt-item pt-item-<?php echo $i; if ($i+1 == $recommend) echo ' active';?>">
                    <?php  if ($item['desc']) {?>
                        <div class="pt-recommend"><?php echo $item['desc']?></div>
                    <?php }?>
                    <div class="pt-header">
                        <h3 class="pt-title"><?php echo $item['title']?></h3>
                        <span class="pt-desc"><?php echo isset($item['sub-title'])?$item['sub-title']:'' ?></span>
                        <?php if (($style == 1 || $style == 2) && $item['price'] !== '') { ?>
                        <div class="pt-price-wrap">
                            <span class="pt-sign"><?php echo $sign ?></span>
                            <span class="pt-price"><?php echo $item['price'] ?></span>
                            <?php if (isset($item['unit']) && $item['unit']) {?>
                                <span class="pt-unit"><?php if($style != 2){ echo '/';} echo $item['unit'] ?></span>
                            <?php }?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="pt-content">
                        <?php echo wpautop($item['content']);?>
                    </div>
                    <div class="pt-box">
                        <?php if($style == 0 && $item['price'] !== '') { ?>
                        <div class="pt-price-wrap">
                            <span class="pt-sign"><?php echo $sign ?></span>
                            <span class="pt-price"><?php echo $item['price'] ?></span>
                            <?php if (isset($item['unit']) && $item['unit']) {?>
                                <span class="pt-unit">/<?php echo $item['unit'] ?></span>
                            <?php }?>
                        </div>
                        <?php } ?>
                        <?php if($item['btn'] && isset($item['url']) && $item['url']) { ?>
                            <a class="btn btn-lg pt-btn" <?php echo WPCOM::url($item['url'])?>><?php echo $item['btn'] ?></a>
                        <?php }?>
                    </div>
                </li>
            <?php }} ?>
        </ul>
    <?php }
}
register_module( 'WPCOM_Module_pricing_table' );
<?php
class WPCOM_Module_swiper extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'tips' => array(
                    'name' => '温馨提示',
                    'type' => 'a',
                    'style' => 'info',
                    'std' => '幻灯滑块默认<b>全屏宽度</b>显示，如果不希望全屏宽度的话，可先添加<b>全宽模块</b>并<b>开启固定宽度</b>选项，再往全宽模块里面插入幻灯滑块模块'
                ),
                'abs' => array(
                    'name' => '置顶显示',
                    'type' => 'toggle',
                    'desc' => '开启后将置顶显示，与头部融合在一起，<b>仅当模块排在第一时有效</b>',
                    'value'  => '1'
                ),
                'style' => array(
                    'name' => '菜单风格',
                    'type' => 'r',
                    'filter' => 'abs:1',
                    'options' => array(
                        '0' => '黑色，适合亮色幻灯图片',
                        '1' => '白色，适合暗色幻灯图片'
                    )
                ),
                'full' => array(
                    'name' => '全屏高度',
                    'type' => 'toggle',
                    'desc' => '开启后将全屏显示，高度为100%',
                    'value'  => '1'
                ),
                'align' => array(
                    'name' => '内容对齐方式',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => 'center',
                    'options' => array(
                        'left' => '<i class="material-icons">format_align_left</i>',
                        'center' => '<i class="material-icons">format_align_center</i>',
                        'right' => '<i class="material-icons">format_align_right</i>'
                    )
                ),
                'slider' => array(
                    'type' => 'repeat',
                    'name' => '滑块',
                    'items' => array(
                        'alt' => array(
                            'name' => '图片alt',
                            'desc' => '图片替代文字，利于SEO，建议填写，如果开启了全屏高度可忽略'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'upload',
                            'desc' => '图片宽度推荐1920px，高度统一即可，如果选择全屏显示，建议高度810px'
                        ),
                        'video' => array(
                            'name' => '视频',
                            'type' => 'u',
                            'desc' => '可选，MP4格式视频，另外由于手机端部分机型无法自动播放，所以为兼容建议再设置上面的图片选项；图片和视频同时配置时，图片为视频的预览图'
                        ),
                        'text' => array(
                            'name' => '文字内容',
                            'type' => 'editor',
                            'desc' => '可在图片上添加文字内容'
                        ),
                        'btn' => array(
                            'name' => '按钮文字',
                            'desc' => '按钮文字，可选，需要填写了下面的链接才会显示'
                        ),
                        'url' => array(
                            'name' => '图片/按钮链接',
                            'type' => 'url',
                            'desc' => '链接，可选，如果填写了上面的按钮，则是按钮链接，如果没有填写按钮，则为整个图片的链接'
                        )
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'bg-shadow' => array(
                    'name' => '背景处理',
                    'type' => 'r',
                    'ux' => 1,
                    'desc' => '适合当图片上面有文字的情况下，可优化背景显示效果',
                    'value'  => '0',
                    'options' => array(
                        '0' => '不处理',
                        '1' => '暗化处理',
                        '2' => '亮化处理'
                    )
                ),
                'btn-color' => array(
                    'name' => '按钮颜色',
                    'type' => 'color',
                    'desc' => '按钮文字颜色'
                ),
                'btn-bg' => array(
                    'name' => '按钮背景',
                    'type' => 'color',
                    'desc' => '按钮背景颜色'
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
        parent::__construct( 'swiper', '幻灯滑块', $options, 'ondemand_video', '/module/mod-swiper.png' );
    }

    function style( $atts ){
        return array(
            'btn-color' => array(
                '.slide-btn' => '{{color}};'
            ),
            'btn-bg' => array(
                '.slide-btn' => '{{background-color}};',
            )
        );
    }

    function classes( $atts, $depth = 0 ){
        $type = $this->value('full')==0 ? 'normal' : 'full';
        $classes = 'swiper-container swiper-' . $type;
        return $classes;
    }

    function template( $atts, $depth ){
        $shadow = $this->value('bg-shadow') ? $this->value('bg-shadow') : 0;
        $type = $this->value('full')==0 ? 'normal' : 'full';
        $align = $this->value('align');
        ?>
        <div class="swiper-wrapper">
            <?php if($this->value('slider')){ foreach($this->value('slider') as $slider){
                $btn = isset($slider['btn']) && $slider['btn'] ? $slider['btn'] : '';
                $text = isset($slider['text']) && $slider['text'] ? $slider['text'] : '';
                $video = isset($slider['video']) && $slider['video'] ? $slider['video'] : '';
                $url = isset($slider['url']) && $slider['url'] ? $slider['url'] : '';
                if($type=='full'){
                    $img = 'style="background-image: url(\''.(isset($slider['img']) && $slider['img']?$slider['img']:'').'\');"';
                }else{
                    $img = '<img src="'.(isset($slider['img']) && $slider['img']?$slider['img']:'').'" alt="'.(isset($slider['alt']) && $slider['alt']?$slider['alt']:'slider').'">';
                }
                ?>
                <div class="swiper-slide"<?php echo $text!=''?' style="height:auto;"':'';?>>
                    <?php echo !$btn && $url ?
                        '<a class="slide-img" '.WPCOM::url($url).' ' . ($type=='full' ? $img : '') . '>' :
                        '<div class="slide-img" ' . ($type=='full' ? $img : '') . '>';?>
                        <?php if($video) { ?>
                            <video class="slide-video" muted autoplay loop playsinline preload="auto" src="<?php echo esc_url($video);?>" poster="<?php echo $slider['img']?:'' ?>"></video>
                        <?php } else {
                            echo $type=='normal'?$img:'';
                        }?>
                        <?php if($text!=''){ ?>
                            <div class="slide-content<?php echo ($shadow?' shadow-'.$shadow:'')?>">
                                <div class="slide-content-inner container align-<?php echo $align ?>">
                                    <?php echo wpautop($text);?>
                                    <?php if($btn && $url){ ?>
                                        <a class="btn btn-primary btn-round slide-btn" <?php echo WPCOM::url($url);?>>
                                            <?php echo $btn;?><?php WPCOM::icon('arrow-right-2');?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php echo !$btn && $url ? '</a>' : '</div>'; ?>
                </div>
            <?php } } ?>
        </div>
        <?php if($this->value('slider') && count($this->value('slider'))>1){ ?><!-- Add Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add Navigation -->
            <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
            <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
        <?php } ?>
    <?php }
}

register_module( 'WPCOM_Module_swiper' );
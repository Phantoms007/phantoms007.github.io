<?php
class WPCOM_Module_typed extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'format' => array(
                    'name' => '格式',
                    'type' => 's',
                    'value' => 'p',
                    'options' => array(
                        'p' => '段落',
                        'h1' => '一级标题',
                        'h2' => '二级标题',
                        'h3' => '三级标题',
                        'h4' => '四级标题',
                        'h5' => '五级标题',
                        'h6' => '六级标题'
                    )
                ),
                'content' => array(
                    'name' => '输入内容',
                    'type' => 'rp',
                    'o' => array(
                        'text' => array(
                            'name' => '文字内容',
                            'type' => 'editor',
                            'mini' => 1
                        )
                    )
                ),
                'prefix' => array(
                    'name' => '添加前缀',
                    'type' => 'toggle'
                ),
                'prefix-content' => array(
                    'f' => 'prefix:1',
                    'name' => '前缀内容',
                    'type' => 'editor',
                    'mini' => 1
                ),
                'suffix' => array(
                    'name' => '添加后缀',
                    'type' => 'toggle'
                ),
                'suffix-content' => array(
                    'f' => 'suffix:1',
                    'name' => '后缀内容',
                    'type' => 'editor',
                    'mini' => 1
                ),
                'loop' => array(
                    'name' => '循环播放',
                    'type' => 't'
                ),
                'type-speed' => array(
                    'name' => '输入速度',
                    'type' => 'range',
                    'max' => 1000,
                    'min' => 10,
                    'step' => 10,
                    'value' => 50,
                    'desc' => '文字输入速度，默认是50，单位：毫秒'
                ),
                'back-speed' => array(
                    'name' => '回退速度',
                    'type' => 'range',
                    'max' => 1000,
                    'min' => 10,
                    'step' => 10,
                    'value' => 50,
                    'desc' => '文字回退速度，默认是50，单位：毫秒'
                ),
                'back-delay' => array(
                    'name' => '回退延迟',
                    'type' => 'range',
                    'max' => 5000,
                    'min' => 100,
                    'step' => 100,
                    'value' => 1000,
                    'desc' => '即文字输入完成后与回退之间的等待时间，默认是1000，单位：毫秒'
                ),
                'align' => array(
                    'name' => '对齐',
                    'type' => 'r',
                    'ux' => 1,
                    'value' => 'left',
                    'mobile' => 1,
                    'o' => array(
                        'left' => '<i class="material-icons">format_align_left</i>',
                        'center' => '<i class="material-icons">format_align_center</i>',
                        'right' => '<i class="material-icons">format_align_right</i>'
                    )
                ),
                'size' => array(
                    'name' => '文字尺寸',
                    'type' => 'l',
                    'mobile' => 1,
                    'min' => 6
                ),
                'line-height' => array(
                    'name' => '文字行高',
                    'type' => 'l',
                    'mobile' => 1,
                    'units' => 'px, em'
                ),
            ),
            array(
                'tab-name' => '风格样式',
                'cursor-color' => array(
                    'name' => '光标颜色',
                    'type' => 'c'
                ),
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => '0 60px'
                ),
                'padding' => array(
                    'name' => '内边距',
                    'type' => 'trbl',
                    'mobile' => 1,
                    'desc' => '模块内容区域与边界的距离',
                    'units' => 'px, %',
                    'value'  => '10px'
                )
            )
        );
        parent::__construct( 'typed', '文字输入', $options, 'keyboard', '/module/mod-typed.png' );
    }

    function register_script(){
        wp_enqueue_script('typed', get_template_directory_uri() . '/js/typed-2.0.12.min.js', array( 'jquery' ), '2.0.12', true);
    }

    function style( $atts ){
        return array(
            'align' => array(
                '.typed-content' => 'text-align: {{value}};'
            ),
            'size' => array(
                '.typed-content' => 'font-size: {{value}};'
            ),
            'line-height' => array(
                '.typed-content' => 'line-height: {{value}};'
            ),
            'cursor-color' => array(
                '.typed-cursor' => '{{color}};'
            )
        );
    }

    function template($atts, $depth){
        $tag = $this->value('format', 'p');
        $prefix = $this->value('prefix') ? $this->value('prefix-content') : '';
        $suffix = $this->value('suffix') ? $this->value('suffix-content') : '';
        $content = $this->value('content', array());
        $module_id = $this->value('modules-id');
        ?>
        <<?php echo $tag;?> class="typed-content">
            <?php echo $prefix;?> <span class="typed-text" id="typed-text-<?php echo $module_id;?>"></span> <?php echo $suffix;?>
        </<?php echo $tag;?>>
        <?php if(!empty($content)){ ?>
        <script>
            jQuery(function ($){
                new Typed('#typed-text-<?php echo $module_id;?>', {
                    strings: <?php echo json_encode(array_column($content, 'text'));?>,
                    loop: <?php echo !!$this->value('loop', 0) ? 'true' : 'false';?>,
                    typeSpeed: <?php echo $this->value('type-speed', 50);?>,
                    backSpeed: <?php echo $this->value('back-speed', 50);?>,
                    backDelay: <?php echo $this->value('back-delay', 1000);?>,
                    startDelay: 100,
                    smartBackspace: false
                });
            });
        </script>
        <?php }
    }
}
register_module( 'WPCOM_Module_typed' );
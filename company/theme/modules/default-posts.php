<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_default_posts extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题',
                ),
                'sub-title' => array(
                    'name' => '模块副标题',
                    'desc' => '副标题是可选的'
                ),
                'cols' => array(
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value' => 1,
                    'options' => array(
                        '1' => '1篇',
                        '2' => '2篇'
                    )
                ),
                'cat' => array(
                    'name' => '分类',
                    'type' => 'cat-single'
                ),
                'child' => array(
                    'name' => '子项目',
                    'type' => 'r',
                    'ux' => 1,
                    'desc' => '模块标题下方显示Tab切换功能',
                    'value'  => '1',
                    'options' => array(
                        '0' => '不显示',
                        '1' => '子分类',
                        '2' => '自定义分类'
                    )
                ),
                'child-cats' => array(
                    'name' => '子项目分类',
                    'filter' => 'child:2',
                    'type' => 'cms'
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '6'
                ),
                'more' => array(
                    'name' => '更多链接',
                    'type' => 'toggle',
                    'desc' => '是否显示更多链接的按钮',
                    'value'  => '1'
                ),
                'more-style' => array(
                    'f' => 'more:1',
                    'name' => '更多链接风格',
                    'type' => 'r',
                    'value'  => '0',
                    'o' => array(
                        '0' => '暗色风格，适合浅色背景',
                        '1' => '亮色风格，适合深色背景'
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
                    'name' => '常规颜色',
                    'type' => 'color',
                    'desc' => '常规内容、文字颜色'
                ),
                'tab-color' => array(
                    'f' => 'child:1,child:2',
                    'name' => 'Tab颜色',
                    'type' => 'color'
                ),
                'hover-color' => array(
                    'name' => '悬停颜色',
                    'type' => 'color',
                    'desc' => '链接、Tab等内容的悬停颜色'
                )
            )
        );
        parent::__construct( 'default-posts', '文章列表', $options, 'view_list', '/module/mod-default-posts.png' );
    }
    function style( $atts ){
        return array(
            'color' => array(
                ', .sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'tab-color' => array(
                '.module-tab-item' => '{{color}};'
            ),
            'hover-color' => array(
                '.item-title a:hover,.item-meta a:hover,.module-tab-item.active,.module-tab-left .module-tab-item:hover' => '{{color}};',
                '.module-tab-item.active' => '{{border-color}};',
                '.module-tab-center .module-tab-item:hover' => '{{background-color}};{{border-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $more = $this->value('more');
        $more_style = $this->value('more-style') ? 'light' : 'dark';
        $cat = $this->value('cat', 0);
        $child = $this->value('child');
        $child_cats = null;
        $number = $this->value('number');
        $cols = $this->value('cols');
        if($child==1){
            $child_cats = get_terms(array(
                'taxonomy' => 'category',
                'parent' => $cat
            ));
        }else if($child==2 && $this->value('child-cats')){
            $child_cats = get_terms(array(
                'taxonomy' => 'category',
                'orderby' => 'include',
                'include' => $this->value('child-cats')
            ));
        }
        wpcom_module_title($this->value('title'), $this->value('sub-title'));?>
        <?php if($child_cats && count($child_cats)){ ?>
            <div class="module-tab j-tabs <?php global $title_style; echo $title_style == 1 ? 'module-tab-left' : 'module-tab-center'; ?> ">
                <div class="module-tab-item j-tabs-item active"><?php _e('All', 'wpcom'); ?></div>
                <?php foreach($child_cats as $c){ ?>
                    <div class="module-tab-item j-tabs-item"><?php echo $c->name;?></div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="module-tab-wrap j-tabs-wrap active">
            <ul class="post-loop post-loop-default cols-<?php echo $cols;?>">
                <?php
                $arg = array(
                    'posts_per_page' => $number,
                );
                if($cat) $arg['cat'] = $cat;
                $news = get_posts($arg);
                global $post;
                foreach ( $news as $post ) : setup_postdata( $post );
                    get_template_part( 'templates/loop' , 'default' );
                endforeach; wp_reset_postdata(); ?>
            </ul>
            <?php if($more){ ?>
                <div class="module-more">
                    <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo get_category_link($cat);?>">
                        <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php if($child_cats && count($child_cats)){ foreach($child_cats as $c){ ?>
            <div class="module-tab-wrap j-tabs-wrap">
                <ul class="post-loop post-loop-default cols-<?php echo $cols;?>">
                    <?php
                    $arg = array(
                        'posts_per_page' => $number,
                        'cat' => $c->term_id
                    );
                    $news = get_posts($arg);
                    global $post;
                    foreach ( $news as $post ) : setup_postdata( $post );
                        get_template_part( 'templates/loop' , 'default' );
                    endforeach; wp_reset_postdata(); ?>
                </ul>
                <?php if($more){ ?>
                    <div class="module-more">
                        <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo get_category_link($c->term_id);?>">
                            <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } } ?>
    <?php }
}
register_module( 'WPCOM_Module_default_posts' );
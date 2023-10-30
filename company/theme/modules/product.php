<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_product extends WPCOM_Module{
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
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '4',
                    'options' => array(
                        '2' => '2个',
                        '3' => '3个',
                        '4' => '4个',
                        '5' => '5个'
                    )
                ),
                'from' => array(
                    'name' => '文章调用',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'options' => array(
                        '0' => '按分类调用',
                        '1' => '按标签调用'
                    )
                ),
                'wrap' => array(
                    'f' => 'from:0',
                    'type' => 'w',
                    'o' => array(
                        'cat' => array(
                            'name' => '分类',
                            'type' => 'cs'
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
                            'f' => 'child:2',
                            'type' => 'cms'
                        ),
                    )
                ),
                'tag' => array(
                    'f' => 'from:1',
                    'name' => '标签',
                    'desc' => '多个标签使用英文逗号隔开'
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '8'
                ),
                'orderby'    => array(
                    'name' => '排序',
                    'type'  => 'select',
                    'value'   => '0',
                    'options' => array(
                        '0' => '发布时间',
                        '1' => '评论数',
                        '2' => '浏览数(需安装WP-PostViews插件)',
                        '3' => '随机排序'
                    )
                ),
                'show_type' => array(
                    'name' => '显示方式',
                    'value' => 'p',
                    't' => 'r',
                    'ux' => 2,
                    'o' => array(
                        'p' => '/module/product-list-tpl-1.png',
                        's' => '/module/product-list-tpl-2.png',
                        'n' => '/module/product-list-tpl-3.png'
                    )
                ),
                'more' => array(
                    'f' => 'from:0',
                    'name' => '更多链接',
                    'type' => 'toggle',
                    'desc' => '是否显示更多链接的按钮',
                    'value'  => '0'
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
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'tab-color' => array(
                    'f' => 'child:1,child:2',
                    'name' => 'Tab颜色',
                    'type' => 'color'
                ),
                'tab-hover' => array(
                    'f' => 'child:1,child:2',
                    'name' => 'Tab选中颜色',
                    'type' => 'color'
                )
            )
        );
        parent::__construct('product', '产品列表', $options, 'view_comfy', '/module/mod-product.png');
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
            'tab-hover' => array(
                '.module-tab-item.active,.module-tab-left .module-tab-item:hover' => '{{color}};',
                '.module-tab-item.active' => '{{border-color}};',
                '.module-tab-center .module-tab-item:hover' => '{{background-color}};{{border-color}};'
            )
        );
    }

    function template( $atts, $depth ){
        $more = $this->value('more');
        $more_style = $this->value('more-style') ? 'light' : 'dark';
        $cat = $this->value('cat', 0);
        $tag = $this->value('tag');
        $child = $this->value('child');
        $child_cats = null;
        $number = $this->value('number');
        $cols = $this->value('cols');
        $from = $this->value('from');
        if($from==0) {
            if ($child == 1) {
                $child_cats = get_terms(array(
                    'taxonomy' => 'category',
                    'parent' => $cat
                ));
            } else if ($child == 2 && $this->value('child-cats')) {
                $child_cats = get_terms(array(
                    'taxonomy' => 'category',
                    'orderby' => 'include',
                    'include' => $this->value('child-cats')
                ));
            }
        }
        $orderby_id = isset($atts['orderby']) && $atts['orderby'] ? $atts['orderby'] :  '0';
        $orderby = 'date';
        if($orderby_id==1){
            $orderby = 'comment_count';
        }else if($orderby_id==2){
            $orderby = 'meta_value_num';
        }else if($orderby_id==3){
            $orderby = 'rand';
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
            <ul class="post-loop post-loop-product cols-<?php echo $cols;?>">
                <?php
                $args = array(
                    'posts_per_page' => $number,
                    'orderby' => $orderby
                );
                if($from==0 && $cat) $args['cat'] = $cat;
                if($from==1 && $tag){
                    $tags = explode(',', $tag);
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'post_tag',
                            'field'    => 'name',
                            'terms'    => $tags
                        ),
                    );
                }
                if($orderby=='meta_value_num') $parg['meta_key'] = 'views';
                $posts = get_posts($args);
                global $post, $ptype;
                $ptype = $this->value('show_type');
                foreach ( $posts as $post ) : setup_postdata( $post );
                    get_template_part( 'templates/loop' , 'product' );
                endforeach; wp_reset_postdata(); ?>
            </ul>
            <?php if($from==0 && $more && $cat){ ?>
                <div class="module-more">
                    <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo get_category_link($cat);?>">
                        <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php if($child_cats && count($child_cats)){ foreach($child_cats as $c){ ?>
            <div class="module-tab-wrap j-tabs-wrap">
                <ul class="post-loop post-loop-product cols-<?php echo $cols;?>">
                    <?php
                    $args = array(
                        'posts_per_page' => $number,
                        'cat' => $c->term_id,
                        'orderby' => $orderby
                    );
                    if($orderby=='meta_value_num') $parg['meta_key'] = 'views';
                    $posts = get_posts($args);
                    global $post, $ptype;
                    $ptype = $this->value('show_type');
                    foreach ( $posts as $post ) : setup_postdata( $post );
                        get_template_part( 'templates/loop' , 'product' );
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
register_module( 'WPCOM_Module_product' );
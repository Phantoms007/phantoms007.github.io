<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_woo_product extends WPCOM_Module {
    function __construct() {
        $catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
            'menu_order' => __( 'Default sorting', 'woocommerce' ),
            'popularity' => __( 'Sort by popularity', 'woocommerce' ),
            'rating'     => __( 'Sort by average rating', 'woocommerce' ),
            'date'       => __( 'Sort by latest', 'woocommerce' ),
            'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
            'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
        ) );
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
                'cat' => array(
                    'name' => '商品分类',
                    'type' => 'cat-single',
                    'tax' => 'product_cat',
                    'desc' => '不选择则不区分分类'
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
                    'type' => 'cms',
                    'tax' => 'product_cat'
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '8'
                ),
                'orderby' => array(
                    'name' => '商品排序',
                    'type' => 'select',
                    'value' => 'menu_order',
                    'options' => $catalog_orderby_options
                ),
                'more' => array(
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
        parent::__construct( 'woo_product', '商品展示', $options, 'shopping_bag', '/module/mod-woo_product.png' );
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
        global $post;
        $more = $this->value('more');
        $more_style = $this->value('more-style') ? 'light' : 'dark';
        $cat = (int) $this->value('cat', 0);
        $child = $this->value('child');
        $child_cats = null;
        $number = $this->value('number');
        $cols = $this->value('cols');

        if($child==1) {
            $child_cats = get_terms(array(
                'taxonomy' => 'product_cat',
                'parent' => $cat
            ));
        }else if($child==2 && $this->value('child-cats')){
            $child_cats = get_terms(array(
                'taxonomy' => 'product_cat',
                'orderby' => 'include',
                'include' => $this->value('child-cats')
            ));
        }
        $ordering_args = WC()->query->get_catalog_ordering_args( $this->value('orderby') );
        wpcom_module_title($this->value('title'), $this->value('sub-title'));
        ?>

        <?php if($child_cats && count($child_cats)){ ?>
            <div class="module-tab j-tabs <?php global $title_style; echo $title_style == 1 ? 'module-tab-left' : 'module-tab-center'; ?> ">
                <div class="module-tab-item j-tabs-item active"><?php _e('All', 'wpcom'); ?></div>
                <?php foreach($child_cats as $c){ ?>
                    <div class="module-tab-item j-tabs-item"><?php echo $c->name;?></div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="module-tab-wrap woocommerce columns-<?php echo $cols;?> j-tabs-wrap active">
            <ul class="products">
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => $number,
                    'orderby' => $ordering_args['orderby'],
                    'order' => $ordering_args['order']
                );
                if($cat) $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $cat
                    )
                );
                if(isset($ordering_args['meta_key']) && $ordering_args['meta_key']) $args['meta_key'] = $ordering_args['meta_key'];
                $posts = get_posts($args);
                foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                    <?php get_template_part( 'templates/loop' , 'woo' ); ?>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
            <?php if($more){
                if($cat){
                    $more_url = get_term_link($cat, 'product_cat');
                }else{
                    $more_url = get_permalink( wc_get_page_id( 'shop' ) );
                }
                if($more_url && !is_wp_error($more_url)){ ?>
                <div class="module-more">
                    <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo $more_url;?>">
                        <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                    </a>
                </div>
            <?php } } ?>
        </div>
        <?php if($child_cats && count($child_cats)){ foreach($child_cats as $c){ ?>
            <div class="module-tab-wrap woocommerce columns-<?php echo $cols;?> j-tabs-wrap">
                <ul class="products">
                    <?php
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => $number,
                        'orderby' => $ordering_args['orderby'],
                        'order' => $ordering_args['order'],
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field' => 'id',
                                'terms' => $c->term_id
                            )
                        )
                    );
                    if(isset($ordering_args['meta_key']) && $ordering_args['meta_key']) $args['meta_key'] = $ordering_args['meta_key'];
                    $posts = get_posts($args);
                    foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                        <?php get_template_part( 'templates/loop' , 'woo' ); ?>
                    <?php endforeach; wp_reset_postdata(); ?>
                </ul>
                <?php if($more && !is_wp_error($more_url = get_term_link($c->term_id, 'product_cat'))){ ?>
                    <div class="module-more">
                        <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo $more_url;?>">
                            <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } } ?>
    <?php }
}
if(function_exists('is_woocommerce')) register_module( 'WPCOM_Module_woo_product' );
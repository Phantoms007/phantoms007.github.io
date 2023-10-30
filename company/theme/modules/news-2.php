<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_news_style_two extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题'
                ),
                'style' => array(
                    'n' => '显示风格',
                    'value' => '0',
                    't' => 'r',
                    'ux' => 2,
                    'o' => array(
                        '0' => '/module/news2-0.png',
                        '1' => '/module/news2-1.png',
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
        parent::__construct('news-two', '新闻动态', $options, 'newspaper', '/module/mod-news-2.png');
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
        $child = $this->value('child');
        $style = $this->value('style');
        $number = $this->value('style')== '0' ? '5' : '6';
        $child_cats = null;

        if($child==1 && $cat){
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
            <div class="module-tab j-tabs <?php global $title_style; echo $title_style == 1 ? 'module-tab-left' : 'module-tab-center'; ?>">
                <div class="module-tab-item j-tabs-item active"><?php _e('All', 'wpcom'); ?></div>
                <?php foreach($child_cats as $c){ ?>
                    <div class="module-tab-item j-tabs-item"><?php echo $c->name;?></div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if($style == '0') { ?>
            <div class="module-tab-wrap j-tabs-wrap active">
                <div class="news2-inner">
                    <?php
                    $arg = array(
                        'posts_per_page' => $number,
                        'cat' => $cat
                    );
                    $news = get_posts($arg);
                    global $post;
                    $i = 0;
                    foreach ( $news as $post ) { setup_postdata( $post ) ; ?>
                    <?php if($i === 0) {?>
                    <a class="news2-poster news2-first" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                        <?php the_post_thumbnail('large'); ?>
                        <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                    </a>
                    <ul class="news2-right">
                        <?php } else { ?>
                            <li class="news2-right-item">
                                <a class="news2-item-thumb" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                    <?php the_post_thumbnail(); ?>
                                </a>
                                <div class="news2-item-info">
                                    <a class="news2-item-title" href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?>><?php the_title();?></a>
                                    <span class="news2-item-time"><?php the_time( get_option( 'date_format' ) ); ?></span>
                                </div>
                            </li>
                        <?php } $i++;} wp_reset_postdata(); ?>
                    </ul>
                </div>
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
                    <div class="news2-inner">
                        <?php
                        $arg = array(
                            'posts_per_page' => $number,
                            'cat' => $c->term_id
                        );
                        $news = get_posts($arg);
                        global $post;
                        $i = 0;
                        foreach ( $news as $post ) { setup_postdata( $post ) ; ?>
                        <?php if($i === 0) {?>
                        <a class="news2-poster news2-first" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                            <?php the_post_thumbnail('large'); ?>
                            <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                        </a>
                        <ul class="news2-right">
                            <?php } else { ?>
                                <li class="news2-right-item">
                                    <a class="news2-item-thumb" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                        <?php the_post_thumbnail(); ?>
                                    </a>
                                    <div class="news2-item-info">
                                        <a class="news2-item-title" href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?>><?php the_title();?></a>
                                        <span class="news2-item-time"><?php the_time( get_option( 'date_format' ) ); ?></span>
                                    </div>
                                </li>
                            <?php } $i++;} wp_reset_postdata(); ?>
                        </ul>
                    </div>
                    <?php if($more){ ?>
                        <div class="module-more">
                            <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo get_category_link($c->term_id);?>">
                                <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

            <?php } } } else if($style == '1') { ?>
            <div class="module-tab-wrap j-tabs-wrap active">
                <div class="news2-inner news2-inner-2">
                    <?php
                    $arg = array(
                        'posts_per_page' => $number,
                        'cat' => $cat
                    );
                    $news = get_posts($arg);
                    global $post;
                    if($news && isset($news[0])){
                        $post = $news[0];setup_postdata( $post );
                        ?>
                        <div class="news2-col news2-col-left">
                            <a class="news2-poster" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                <?php the_post_thumbnail(); ?>
                                <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                            </a>
                        </div>
                    <?php } wp_reset_postdata();
                    if($news && isset($news[1])){ ?>
                        <div class="news2-col news2-col-center">
                            <?php for ($i=1; $i<3; $i++){ if(isset($news[$i])){ $post = $news[$i]; setup_postdata( $post );?>
                                <a class="news2-poster" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                    <?php the_post_thumbnail(); ?>
                                    <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                                </a>
                            <?php } wp_reset_postdata(); } ?>
                        </div>
                    <?php }

                    if($news && isset($news[3])){ ?>
                        <ul class="news2-col news2-col-right">
                            <?php for ($i=3; $i<6; $i++){ if(isset($news[$i])){ $post = $news[$i]; setup_postdata( $post );?>
                                <li class="news2-right-item">
                                    <a class="news2-item-thumb" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                        <?php the_post_thumbnail(); ?>
                                    </a>
                                    <div class="news2-item-info">
                                        <a class="news2-item-title" href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?>><?php the_title();?></a>
                                        <span class="news2-item-time"><?php the_time( get_option( 'date_format' ) ); ?></span>
                                    </div>
                                </li>
                            <?php } wp_reset_postdata(); } ?>
                        </ul>
                    <?php } ?>
                </div>
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
                    <div class="news2-inner news2-inner-2">
                        <?php
                        $arg = array(
                            'posts_per_page' => $number,
                            'cat' => $c->term_id
                        );
                        $news = get_posts($arg);
                        global $post;
                        if($news && isset($news[0])){
                            $post = $news[0];setup_postdata( $post );
                            ?>
                            <div class="news2-col news2-col-left">
                                <a class="news2-poster" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                    <?php the_post_thumbnail(); ?>
                                    <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                                </a>
                            </div>
                        <?php } wp_reset_postdata();
                        if($news && isset($news[1])){ ?>
                            <div class="news2-col news2-col-center">
                                <?php for ($i=1; $i<3; $i++){ if(isset($news[$i])){ $post = $news[$i]; setup_postdata( $post );?>
                                    <a class="news2-poster" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                        <?php the_post_thumbnail(); ?>
                                        <h3 class="news2-poster-title"><span><?php the_title();?></span></h3>
                                    </a>
                                <?php } wp_reset_postdata(); } ?>
                            </div>
                        <?php }

                        if($news && isset($news[3])){ ?>
                            <ul class="news2-col news2-col-right">
                                <?php for ($i=3; $i<6; $i++){ if(isset($news[$i])){ $post = $news[$i]; setup_postdata( $post );?>
                                    <li class="news2-right-item">
                                        <a class="news2-item-thumb" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                                            <?php the_post_thumbnail(); ?>
                                        </a>
                                        <div class="news2-item-info">
                                            <a class="news2-item-title" href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?>><?php the_title();?></a>
                                            <span class="news2-item-time"><?php the_time( get_option( 'date_format' ) ); ?></span>
                                        </div>
                                    </li>
                                <?php } wp_reset_postdata(); } ?>
                            </ul>
                        <?php } ?>
                    </div>
                    <?php if($more){ ?>
                        <div class="module-more">
                            <a class="btn btn-round btn-<?php echo $more_style;?> more-link" href="<?php echo get_category_link($c->term_id);?>">
                                <?php _e('Read more', 'wpcom');?><?php WPCOM::icon('arrow-right');?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } } ?>
        <?php } }

}

register_module( 'WPCOM_Module_news_style_two' );
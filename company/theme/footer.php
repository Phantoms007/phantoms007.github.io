<?php
global $options;
$footer_style = (isset($options['footer_style'])&&$options['footer_style']=='0') || !isset($options['footer_style']);
?>
</div>
<footer class="<?php echo wpcom_footer_class($footer_style?'':'footer-simple');?>">
    <div class="container">
        <?php if($footer_style){ ?>
            <div class="footer-widget row hidden-xs">
                <?php dynamic_sidebar('footer'); ?>
                <?php if(isset($options['contact_title']) && $options['contact_title']){ ?>
                    <div class="col-md-6 col-md-offset-2 col-sm-16 col-xs-24 widget widget_contact">
                        <h3 class="widget-title"><?php echo $options['contact_title']; ?></h3>
                        <div class="widget-contact-wrap">
                            <div class="widget-contact-tel"><?php echo isset($options['contact_tel'])?$options['contact_tel']:'';?></div>
                            <div class="widget-contact-time"><?php echo isset($options['contact_time'])?$options['contact_time']:'';?></div>
                            <?php if(isset($options['contact_btn_url']) && $options['contact_btn_url']) { ?>
                                <a class="contact-btn" <?php echo WPCOM::url($options['contact_btn_url']);?>>
                                    <?php echo $options['contact_btn'] ? $options['contact_btn'] : __('Contact Us', 'wpcom');?>
                                </a>
                            <?php } ?>
                            <div class="widget-contact-sns">
                                <?php if(isset($options['fticon_i']) && $options['fticon_i']){
                                    foreach ($options['fticon_i'] as $i => $icon){ if($icon){ ?>
                                        <a <?php if($options['fticon_t'][$i]=='1'){ echo 'class="sns-wx" href="javascript:;"'; } else { echo WPCOM::url($options['fticon_u'][$i]);} ?>>
                                            <?php WPCOM::icon($icon, true, 'sns-icon');?>
                                            <?php if($options['fticon_t'][$i]=='1'){ ?><span style="background-image:url(<?php echo trim($options['fticon_u'][$i]); ?>);"></span><?php } ?>
                                        </a>
                                    <?php } } } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if((is_home()||is_front_page()) && is_numeric(get_query_var('paged')) && get_query_var('paged') == 0) {
            $bookmarks = get_bookmarks(array('limit' => -1, 'category' => '', 'category_name' => '', 'hide_invisible' => 1, 'show_updated' => 0 ));
            if(count($bookmarks)>0){ ?>
                <div class="bookmarks hidden-xs">
                    <h3 class="bookmarks-title"><?php _e('Links: ', 'wpcom');?></h3>
                    <div class="bookmarks-list">
                        <?php foreach($bookmarks as $link){ if($link->link_visible=='Y'){ ?>
                            <a <?php if($link->link_target){?>target="<?php echo $link->link_target;?>" <?php } ?><?php if($link->link_description){?>title="<?php echo esc_attr($link->link_description);?>" <?php } ?>href="<?php echo $link->link_url?>"<?php if($link->link_rel){?> rel="<?php echo $link->link_rel;?>"<?php } ?>><?php echo $link->link_name?></a>
                        <?php }} ?>
                    </div>
                </div>
            <?php } } ?>
        <div class="copyright">
            <?php if(!$footer_style){ wp_nav_menu( array( 'container' => false, 'depth'=> 1, 'theme_location' => 'footer', 'items_wrap' => '<ul class="footer-menu">%3$s</ul>' ) );} ?>
            <?php echo ($copyright=isset($options['copyright'])?$options['copyright']:'')?wpautop($copyright):'Copyright © 2023 '.get_bloginfo("name").' 版权所有  Powered by <a href="http://www.wpcom.cn" target="_blank">WordPress</a>'?>
        </div>
    </div>
</footer>
<?php wp_footer();?>
</body>
</html>
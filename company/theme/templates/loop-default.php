<li class="post-item">
    <?php if(get_the_post_thumbnail(null, 'default')){ ?>
        <div class="item-img">
            <a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
                <?php the_post_thumbnail('default'); ?>
            </a>
        </div>
    <?php } ?>
    <div class="item-content">
        <h2 class="item-title">
            <a href="<?php the_permalink();?>"<?php echo wpcom_post_target();?> rel="bookmark">
                <?php the_title();?>
            </a>
        </h2>
        <div class="item-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <div class="item-meta">
            <?php the_category(', '); ?>
            <span class="item-meta-li date"<?php echo ($post->post_type!='post' ? ' style="margin-left:0;"' : '');?>><?php the_time( get_option( 'date_format' ) ); ?></span>
        </div>
    </div>
</li>
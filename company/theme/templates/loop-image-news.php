<li class="post-item">
    <div class="post-item-inner">
        <a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
            <?php wpcom_the_post_thumbnail('default');?>
        </a>
        <h2 class="item-title">
            <a href="<?php the_permalink();?>"<?php echo wpcom_post_target();?> rel="bookmark"><?php the_title();?></a>
        </h2>
        <div class="item-excerpt"><?php the_excerpt();?></div>
        <div class="item-meta">
            <?php
            $category = get_the_category();
            $cat = $category?$category[0]:'';
            if($cat){ ?>
                <a class="item-cat" href="<?php echo get_term_link($cat->cat_ID);?>" target="_blank">
                    <?php echo $cat->name;?>
                </a>
            <?php } ?>
            <span class="item-date"><?php the_time( get_option( 'date_format' ) ); ?></span>
        </div>
    </div>
</li>
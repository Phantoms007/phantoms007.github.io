<?php
global $ptype, $post;
$ptype = $ptype ?: 'p';
?>
<li class="post-item">
    <div class="<?php echo $ptype;?>-item-wrap">
        <a class="thumb" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
            <?php the_post_thumbnail();?>
        </a>
        <h3 class="title">
            <a href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?> rel="bookmark"><?php the_title();?></a>
        </h3>
    </div>
</li>
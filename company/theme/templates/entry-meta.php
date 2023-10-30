<div class="entry-meta">
    <?php $cats = get_the_category_list( ', ', '', false );?>
    <span class="entry-emta-item"><?php echo ($cats ? WPCOM::icon('folder-open', false) . ' ' . $cats : '') ?></span>
    <time class="entry-emta-item entry-date published" datetime="<?php echo get_post_time( 'c', false, $post );?>" pubdate>
        <?php WPCOM::icon('date');?> <?php the_time(get_option('date_format'));?> <?php the_time(get_option('time_format'));?>
    </time>
    <?php
    if(function_exists('the_views')) {
        $views = intval(get_post_meta($post->ID, 'views', true));
        echo '<span class="entry-emta-item">' . WPCOM::icon('eye', false) . ' ' . $views . '</span>';
    } ?>
</div>
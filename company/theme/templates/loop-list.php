<li class="list-item">
    <span class="date pull-right"><?php the_time(get_option('date_format'));?></span>
    <a href="<?php echo esc_url( get_permalink() );?>"<?php echo wpcom_post_target();?> rel="bookmark">
        <?php the_title();?>
    </a>
</li>
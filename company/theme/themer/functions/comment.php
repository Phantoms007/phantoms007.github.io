<?php
defined( 'ABSPATH' ) || exit;

function wpcom_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    extract($args, EXTR_SKIP);

    if ( 'div' == $args['style'] ) {
        $tag = 'div';
        $add_below = 'comment';
    } else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
    $author = get_comment_author();
    if( $comment->user_id && class_exists('WPCOM_Member') ){
        $url = get_author_posts_url( $comment->user_id );
        $author = '<a href="'.$url.'" target="_blank">'.$author.'</a>';
    }else if( $comment->comment_author_url ){
        $author = '<a href="'.esc_url($comment->comment_author_url).'" target="_blank" rel="nofollow">'.$author.'</a>';
    } ?>
    <<?php echo $tag ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php comment_ID() ?>">
    <div id="div-comment-<?php comment_ID() ?>">
        <div class="comment-author vcard">
            <?php if ( $args['avatar_size'] != 0 ) echo get_avatar( $comment, $args['avatar_size'], '', get_comment_author() ); ?>
        </div>
        <div class="comment-body">
            <div class="nickname"><?php echo $author;?>
                <span class="comment-time"><?php echo get_comment_date().' '.get_comment_time(); ?></span>
            </div>
            <?php if ( $comment->comment_approved == '0' ) : ?>
                <div class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wpcom' ); ?></div>
            <?php endif; ?>
            <div class="comment-text"><?php comment_text(); ?></div>
        </div>

        <div class="reply">
            <?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
        </div>
    </div>
<?php
}
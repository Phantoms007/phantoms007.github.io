<?php global $options, $post;?>
<div id="preview" class="entry-img">
    <?php
    $thumb = get_post_meta( $post->ID, 'wpcom_thumb', true );
    if(preg_match('/^\d+$/i', $thumb[0])){
        $img = wp_get_attachment_image_url( $thumb[0], 'product-large' );
    }else{
        $_img = $thumb[0] ? WPCOM::thumbnail($thumb[0], 800, 800, true) : '';
        $img = isset($_img[0]) ? $_img[0] : '';
    } ?>
    <div id="pg-img" class="jqzoom product-img">
        <img src="<?php echo $img;?>" alt="<?php echo esc_attr(get_the_title());?>" jqimg="<?php echo $img;?>">
    </div>
    <div id="pg-list">
        <a href="javascript:;" class="pg-control sc-prev" id="pg-forward"></a>
        <a href="javascript:;" class="pg-control sc-next" id="pg-backward"></a>
        <div class="pg-items">
            <ul class="lh clearfix">
                <?php foreach($thumb as $img){
                    if(preg_match('/^\d+$/i', $img)){
                        $image1 = wp_get_attachment_image_url( $img, 'product-small' );
                        $image2 = wp_get_attachment_image_url( $img, 'product-large' );
                    }else{
                        $_image1 = $img ? WPCOM::thumbnail($img, 150, 150, true) : '';
                        $image1 = isset($_image1[0]) ? $_image1[0] : '';
                        $_image2 = $img ? WPCOM::thumbnail($img, 800, 800, true) : '';
                        $image2 = isset($_image2[0]) ? $_image2[0] : '';
                    } ?>
                    <li><img src="<?php echo $image1;?>" data-url="<?php echo $image2;?>" alt="<?php echo esc_attr(get_the_title());?>"></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
<div class="entry-info">
    <?php if(!is_banner_title()){ ?><h1 class="entry-title"><?php the_title();?></h1><?php } ?>
    <?php
    $pname = get_post_meta( $post->ID, 'wpcom_pname', true );
    $pvalue = get_post_meta( $post->ID, 'wpcom_pvalue', true );
    $i = 0;
    if($pname && is_array($pname)){ ?>
    <div class="entry-info-item-wrap">
        <?php foreach($pname as $n){ if($n){ ?>
            <div class="entry-info-item"><span><?php echo $n;?>: </span><?php echo $pvalue[$i];?></div>
        <?php };$i++;} ?>
    </div>
    <?php }
    if($post->post_excerpt){ ?>
        <div class="entry-info-excerpt">
            <?php the_excerpt(); ?>
        </div>
    <?php }
    $enquiry = get_post_meta( $post->ID, 'wpcom_enquiry_text', true );
    $enquiry = ($enquiry=='' && isset($options['enquiry_text']) && $options['enquiry_text']) ? $options['enquiry_text'] : $enquiry;
    $enquiry_url = get_post_meta( $post->ID, 'wpcom_enquiry_url', true );
    $enquiry_url = ($enquiry_url=='' && isset($options['enquiry_url']) && $options['enquiry_url']) ? $options['enquiry_url'] : $enquiry_url;
    $enquiry_url = $enquiry_url ? $enquiry_url : 'javascript:;';
    if($enquiry) { ?>
        <a class="btn btn-primary btn-lg btn-enquiry" <?php echo WPCOM::url($enquiry_url);?>><?php echo $enquiry;?></a>
    <?php } ?>
</div>
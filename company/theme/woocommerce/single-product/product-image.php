<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;
$attachment_ids = $product->get_gallery_image_ids();

?>
<div id="preview" class="entry-img">
		<?php

        if($attachment_ids){
            $img = wp_get_attachment_image_src( $attachment_ids[0], 'woocommerce_single' );
        } else if ( has_post_thumbnail() ) {
            $post_thumbnail_id = get_post_thumbnail_id( $post->ID );
            $img = wp_get_attachment_image_src( $post_thumbnail_id, 'woocommerce_single' );
        } else {
            $img = array( wc_placeholder_img_src(), '', '' );
        }

        $html  = ' <div id="pg-img" class="jqzoom product-img">';
        $html .= '<img src="'.esc_url($img[0]).'" alt="'.esc_attr($post->post_title).'" jqimg="'.esc_url($img[0]).'" width="'.$img[1].'" height="'.$img[2].'">';
        $html .= '</div>';

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, get_post_thumbnail_id( $post->ID ) );

		do_action( 'woocommerce_product_thumbnails' );
		?>
</div>

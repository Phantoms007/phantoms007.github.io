<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper entry-content clearfix">
		<ul class="entry-tab j-tabs clearfix" role="tablist">
			<?php $i = 0;foreach ( $tabs as $key => $tab ) : ?>
				<li class="entry-tab-item j-tabs-item <?php echo esc_attr( $key ); ?>_tab<?php echo $i==0?' active':'';?>" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab">
                    <?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?>
				</li>
			<?php $i++; endforeach; ?>
		</ul>
		<?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
			<div class="entry-tab-content j-tabs-wrap<?php echo $i==0?' active':'';?>" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel">
				<?php call_user_func( $tab['callback'], $key, $tab ); ?>
			</div>
		<?php $i++; endforeach; ?>
        <?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

<?php endif; ?>

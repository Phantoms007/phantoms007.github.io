<?php
global $options, $tpl;
$category = get_the_category(get_queried_object_id());
$cat = $category[0]->cat_ID;

$tpl = get_term_meta( $cat, 'wpcom_tpl', true );

if ( $tpl ) {
    if($tpl=='product' || $tpl=='product-fullwidth'){ // 产品分类
        $sing_tpl = isset($options['product_single_tpl']) && $options['product_single_tpl'] ? $options['product_single_tpl'] : '';
        $tpl = $sing_tpl && $sing_tpl == 2 ? 'product2' : 'product';
    }
    if(locate_template('single-' . $tpl . '.php') != ''){
        get_template_part( 'single', $tpl );
    }else{
        get_template_part( 'single', 'news' );
    }
} else {
    get_template_part( 'single', 'news' );
}
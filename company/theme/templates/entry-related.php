<?php
global $options, $tpl;
$class = '';
if(!isset($tpl)){
    $category = get_the_category(get_queried_object_id());
    $cat = $category[0]->cat_ID;
    $tpl = get_term_meta( $cat, 'wpcom_tpl', true );
}else if($tpl && preg_match('/^single-product/i', $tpl)){
    $tpl = 'product';
}
$tpl = $tpl ?: 'news';
$only_img = false;

if($tpl === 'product' || $tpl === 'product2'){
    global $ptype;
    $num = isset($options['related_num2']) && $options['related_num2']!=='' ? $options['related_num2'] : 6;
    $ptype = isset($options['product_cat_show_type']) ? $options['product_cat_show_type'] : 'p';
    $ptype = $ptype ?: 'p';
    $title = isset($options['related_product']) ? $options['related_product'] : '';
    $title = $title ? $title : __('Related products', 'wpcom');
    $tpl = 'templates/loop-product';
    $class = 'post-loop post-loop-product';
    $only_img = true;
}else{
    $num = isset($options['related_num']) && $options['related_num']!=='' ? $options['related_num'] : 10;
    $title = isset($options['related_news']) ? $options['related_news'] : '';
    $title = $title ? $title : __('Related posts', 'wpcom');
    if($tpl === 'image-news'){
        $class = 'post-loop post-loop-' . $tpl;
        $tpl = 'templates/loop-' . $tpl;
    }else{
        $tpl = '';
    }
}

wpcom_related_post( $num, $title, $tpl, $class, $only_img );
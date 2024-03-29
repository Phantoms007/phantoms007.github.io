<?php
/**
 * Title         : Aqua Resizer
 * Description   : Resizes WordPress images on the fly
 * Version       : 1.2.1
 * Author        : Syamil MJ
 * Author URI    : http://aquagraphite.com
 * License       : WTFPL - http://sam.zoy.org/wtfpl/
 * Documentation : https://github.com/sy4mil/Aqua-Resizer/
 *
 * @param string  $url      - (required) must be uploaded using wp media uploader
 * @param int     $width    - (required)
 * @param int     $height   - (optional)
 * @param bool    $crop     - (optional) default to soft crop
 * @param bool    $single   - (optional) returns an array if false
 * @param bool    $upscale  - (optional) resizes smaller images
 * @uses  wp_upload_dir()
 * @uses  image_resize_dimensions()
 * @uses  wp_get_image_editor()
 *
 * @return str|array
 */
defined( 'ABSPATH' ) || exit;

if(!class_exists('WPCOM_Resize')) {
    class WPCOM_Exception extends Exception {}

    class WPCOM_Resize
    {
        /**
         * The singleton instance
         */
        static private $instance = null;

        /**
         * Should an WPCOM_Exception be thrown on error?
         * If false (default), then the error will just be logged.
         */
        public $throwOnError = false;

        /**
         * No initialization allowed
         */
        private function __construct() {}

        /**
         * No cloning allowed
         */
        private function __clone() {}

        /**
         * For your custom default usage you may want to initialize an WPCOM_Resize object by yourself and then have own defaults
         */
        static public function getInstance() {
            if(self::$instance == null) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Run, forest.
         */
        public function process( $url, $width = null, $height = null, $crop = null, $img_id = 0, $size = '', $single = true, $upscale = false ) {
            try {
                // Validate inputs.
                if (!$url)
                    throw new WPCOM_Exception('$url parameter is required');
                if (!$width)
                    throw new WPCOM_Exception('$width parameter is required');

                // Caipt'n, ready to hook.
                if ( true === $upscale ) add_filter( 'image_resize_dimensions', array($this, 'aq_upscale'), 10, 6 );

                // Define upload path & dir.
                $upload_info = wp_upload_dir();
                $upload_dir = $upload_info['basedir'];
                $upload_url = $upload_info['baseurl'];

                $http_prefix = "http://";
                $https_prefix = "https://";
                $relative_prefix = "//"; // The protocol-relative URL

                // 匹配本地绝对路径图片，并补充成完整地址
                if(preg_match('/^\/[^\/].*/i', $url) && preg_match('/(http:|https:)\/\/([^\/]+)/i', get_bloginfo('url'), $mc)){
                    if($mc && $mc[0]){
                        $url = $mc[0] . $url;
                    }
                }

                /* if the $url scheme differs from $upload_url scheme, make them match
                   if the schemes differe, images don't show up. */
                if(!strncmp($url,$https_prefix,strlen($https_prefix))){ //if url begins with https:// make $upload_url begin with https:// as well
                    $upload_url = str_replace($http_prefix,$https_prefix,$upload_url);
                }
                elseif(!strncmp($url,$http_prefix,strlen($http_prefix))){ //if url begins with http:// make $upload_url begin with http:// as well
                    $upload_url = str_replace($https_prefix,$http_prefix,$upload_url);
                }
                elseif(!strncmp($url,$relative_prefix,strlen($relative_prefix))){ //if url begins with // make $upload_url begin with // as well
                    $upload_url = str_replace(array( 0 => "$http_prefix", 1 => "$https_prefix"),$relative_prefix,$upload_url);
                }


                // Check if $img_url is local.
                if ( false === strpos( $url, $upload_url ) )
                    return false;

                // oss路径的图片使用oss图片服务返回
                if ( preg_match('/^oss:\/\//i', $upload_dir) ) {
                    $size = 'image/resize,m_fill,w_'.$width;
                    if($height) $size .= ',h_'.$height;
                    $size .= ',limit_0';
                    if ( $single ) {
                        $image = add_query_arg(array('x-oss-process' => $size), $url);
                    } else {
                        $image = array (
                            0 => add_query_arg(array('x-oss-process' => $size), $url),
                            1 => $width,
                            2 => $height
                        );
                    }
                    return $image;
                }else if( (function_exists('wpcos_unique_filename') || defined('TENCENT_WORDPRESS_COS_VERSION')) && !preg_match('/imageMogr2\//i', $upload_dir)) {
                    $size = 'imageMogr2/crop/'.$width.'x';
                    if($height) $size .= $height;
                    $size .= '/gravity/center';
                    if ( $single ) {
                        $image = add_query_arg(array($size => ''), $url);
                    } else {
                        $image = array (
                            0 => add_query_arg(array($size => ''), $url),
                            1 => $width,
                            2 => $height
                        );
                    }
                    return $image;
                }else if(function_exists('wpupyun_unique_filename')){
                    if($height) {
                        $size = '!/both/'.$width.'x'.$height;
                    }else{
                        $size = '!/fw/'.$width;
                    }
                    if ( $single ) {
                        $image = $url . $size;
                    } else {
                        $image = array (
                            0 => $url . $size,
                            1 => $width,
                            2 => $height
                        );
                    }
                    return $image;
                }

                // Define path of image.
                $rel_path = str_replace( $upload_url, '', $url );
                $img_path = $upload_dir . $rel_path;

                // Check if img path exists, and is an image indeed.
                if ( ! file_exists( $img_path ) or ! getimagesize( $img_path ) )
                    throw new WPCOM_Exception('Image file does not exist (or is not an image): ' . $img_path);

                // Get image info.
                $info = pathinfo( $rel_path );
                $ext = strtolower($info['extension']);
                list( $orig_w, $orig_h ) = getimagesize( $img_path );

                // Return the original image only if it exactly fits the needed measures.
                // if ( ! $dims && ( ( ( null === $height && $orig_w == $width ) xor ( null === $width && $orig_h == $height ) ) xor ( $height == $orig_h && $width == $orig_w ) ) ) {
                if (  ( ( null === $height && $orig_w == $width ) xor ( null === $width && $orig_h == $height ) ) xor ( $height == $orig_h && $width == $orig_w ) ) {
                    $img_url = $url;
                    $dst_w = $orig_w;
                    $dst_h = $orig_h;
                } else {
                    // Get image size after cropping.
                    $dims = image_resize_dimensions( $orig_w, $orig_h, $width, $height, $crop );
                    $dst_w = $dims[4];
                    $dst_h = $dims[5];

                    // Use this to check if cropped image already exists, so we can return that instead.
                    $suffix = "{$dst_w}x{$dst_h}";
                    $dst_rel_path = trailingslashit($info['dirname']) . wp_basename( $rel_path, ".$ext" );
                    $destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";

                    if ( ! $dims || ( true == $crop && false == $upscale && ( $dst_w < $width || $dst_h < $height ) ) ) {
                        // Can't resize, so return false saying that the action to do could not be processed as planned.
                        throw new WPCOM_Exception('Unable to resize image because image_resize_dimensions() failed');
                    }
                    // Else check if cache exists.
                    elseif ( file_exists( $destfilename ) && getimagesize( $destfilename ) ) {
                        $img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
                    }
                    // Else, we resize the image and return the new resized image url.
                    else {
                        if($img_id){ // 避免传进来的图片已经是缩略图，需要获取原图
                            $img = wp_get_attachment_image_src($img_id, 'full');
                            $img = is_array($img) ? $img[0] : '';
                            $rel_path = str_replace( $upload_url, '', $img );
                            $img_path = $upload_dir . $rel_path;
                        }

                        $editor = wp_get_image_editor( $img_path );

                        if ( is_wp_error( $editor ) || is_wp_error( $resize = $editor->resize( $width, $height, $crop ) ) ) {
                            throw new WPCOM_Exception('Unable to get WP_Image_Editor: ' .
                                (is_wp_error( $editor ) ? $editor->get_error_message() : $resize->get_error_message() ) . ' (is GD or ImageMagick installed?)');
                        }

                        $resized_file = $editor->save();

                        if ( ! is_wp_error( $resized_file ) ) {
                            $resized_rel_path = str_replace( $upload_dir, '', $resized_file['path'] );
                            $img_url = $upload_url . $resized_rel_path;
                            if(!$img_id){
                                global $wpdb;
                                $value = str_replace( $upload_dir . '/', '', $img_path );
                                $img_id = $wpdb->get_var("SELECT post_id FROM `$wpdb->postmeta` WHERE meta_key = '_wp_attached_file' AND meta_value = '$value'");
                            }

                            if($img_id){
                                $img_data = wp_get_attachment_metadata($img_id);
                                if(is_array($img_data)){
                                    $img_data['sizes'] = isset($img_data['sizes']) ? $img_data['sizes'] : array();

                                    if($size=='') $size = "{$dst_w}x{$dst_h}";
                                    if( isset($img_data['sizes'][$size]) && isset($img_data['sizes'][$size]['file'])){ // 重新生成了缩略图需要删除之前的缩略图片
                                        $delete = 0;
                                        // 老图片和新图片不同，初步考虑删除
                                        if( $img_data['sizes'][$size]['file'] != wp_basename($img_url) ){
                                            $delete = 1;
                                            // 检查是否有同尺寸，如果有其他尺寸用到了则保留
                                            foreach($img_data['sizes'] as $s => $img){
                                                if($s!=$size && $img['width']==$dst_w && $img['height']==$dst_h){
                                                    $delete = 0;
                                                }
                                            }
                                        }

                                        if( $delete ){
                                            $thumb_path_base = str_replace(wp_basename($img_path), '', $img_path);
                                            $thumb = $thumb_path_base . $img_data['sizes'][$size]['file'];
                                            @unlink( $thumb );
                                        }
                                    }
                                    if( $size ){ // 有size则保存数据
                                        $img_data['sizes'][$size] = array(
                                            'file' => wp_basename($img_url),
                                            'width' => $dst_w,
                                            'height' => $dst_h,
                                            'mime-type' => get_post_mime_type($img_id)
                                        );
                                        // wp_generate_attachment_metadata filter 可用于触发云储存插件同步图片
                                        $metadata = apply_filters ( 'wp_generate_attachment_metadata', $img_data, $img_id );
                                        wp_update_attachment_metadata($img_id, $metadata);
                                    }
                                }
                            }
                        } else {
                            throw new WPCOM_Exception('Unable to save resized image file: ' . $resized_file->get_error_message());
                        }
                    }
                }

                // Okay, leave the ship.
                if ( true === $upscale ) remove_filter( 'image_resize_dimensions', array( $this, 'aq_upscale' ) );

                // Return the output.
                if ( $single ) {
                    // str return.
                    $image = $img_url;
                } else {
                    // array return.
                    $image = array (
                        0 => $img_url,
                        1 => $dst_w,
                        2 => $dst_h
                    );
                }

                return $image;
            }
            catch (WPCOM_Exception $ex) {
                //error_log('WPCOM_Resize.process() error: ' . $ex->getMessage());

                if ($this->throwOnError) {
                    // Bubble up exception.
                    throw $ex;
                }
                else {
                    // Return false, so that this patch is backwards-compatible.
                    return false;
                }
            }
        }

        /**
         * Callback to overwrite WP computing of thumbnail measures
         */
        function aq_upscale( $default, $orig_w, $orig_h, $dest_w, $dest_h, $crop ) {
            if ( ! $crop ) return null; // Let the wordpress default function handle this.

            // Here is the point we allow to use larger image size than the original one.
            $aspect_ratio = $orig_w / $orig_h;
            $new_w = $dest_w;
            $new_h = $dest_h;

            if ( ! $new_w ) {
                $new_w = intval( $new_h * $aspect_ratio );
            }

            if ( ! $new_h ) {
                $new_h = intval( $new_w / $aspect_ratio );
            }

            $size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

            $crop_w = round( $new_w / $size_ratio );
            $crop_h = round( $new_h / $size_ratio );

            $crop = apply_filters('wpcom_image_crop_position', $crop);
            if ( ! is_array( $crop ) || count( $crop ) !== 2 ) {
                $crop = array( 'center', 'center' );
            }

            list( $x, $y ) = $crop;

            if ( 'left' === $x ) {
                $s_x = 0;
            } elseif ( 'right' === $x ) {
                $s_x = $orig_w - $crop_w;
            } else {
                $s_x = floor( ( $orig_w - $crop_w ) / 2 );
            }

            if ( 'top' === $y ) {
                $s_y = 0;
            } elseif ( 'bottom' === $y ) {
                $s_y = $orig_h - $crop_h;
            } else {
                $s_y = floor( ( $orig_h - $crop_h ) / 2 );
            }

            return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
        }
    }
}


if(!function_exists('wpcom_resize')) {

    /**
     * This is just a tiny wrapper function for the class above so that there is no
     * need to change any code in your own WP themes. Usage is still the same :)
     */
    function wpcom_resize( $url, $width = null, $height = null, $crop = null, $single = true, $upscale = false ) {
        /* WPML Fix */
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ){
            global $sitepress;
            $url = $sitepress->convert_url( $url, $sitepress->get_default_language() );
        }
        /* WPML Fix */

        $aq_resize = WPCOM_Resize::getInstance();
        return $aq_resize->process( $url, $width, $height, $crop, $single, $upscale );
    }
}

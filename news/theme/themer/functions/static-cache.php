<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Static_Cache{
    static $folder = 'wpcom';
    function __construct(){
        global $options;
        add_action('wp_enqueue_scripts', array($this, 'enqueue_style'), 20);
        add_action('wpcom_static_cache_clear', array($this, 'cron'));
        $this->enable = false;
        if(self::get_folder() && (!isset($options['css_cache']) || $options['css_cache']=='1')){
            $this->enable = true;
            add_action('switch_theme', array($this, 'rebuild'));
            add_action('wpcom_options_updated', array($this, 'rebuild'));
            add_action('wpcom-member-pro_options_updated', array($this, 'rebuild'));
            add_action('wp_ajax_wpcom_ve_save', array($this, 'rebuild'), 1);
        }
    }
    public static function get_folder(){
        return apply_filters('wpcom_static_cache_path', self::$folder);
    }
    public static function dir(){
        $_dir = _wp_upload_dir();
        if(self::get_folder()){
            $dir = $_dir['basedir'] . '/' . self::get_folder();
            if(wp_mkdir_p($dir)) return $dir;
        }
    }
    public static function url(){
        if(self::get_folder()){
            $url = _wp_upload_dir();
            return $url['baseurl'] . '/' . self::get_folder();
        }
    }
    function build_css(){
        $child = is_child_theme();
        $css = $child ? '/style.css' : '/css/style.css';
        $path = get_stylesheet_directory() . $css;
        $dir = self::dir();
        $time = get_option('wpcom_static_time');
        $current = current_time('timestamp');
        if(!$time){
            $time = $current;
            update_option('wpcom_static_time', $time);
        }
        $file = '/style.' . THEME_VERSION . '.' . $time . '.css';
        if( is_singular() && (is_page_template('page-home.php') || is_singular('page_module')) ) {
            global $post;
            $file = '/style.p' . $post->ID .'.'. THEME_VERSION . '.' . $time . '.css';
        }
        $build = 0;
        if(file_exists($dir . $file)){ // 缓存文件存在，比较下修改时间
            // css文件修改时间晚于缓存时间，则表示有修改，更新缓存文件
            if(filemtime($path) > filemtime($dir . $file)){
                $build = 1;
            }
        }else if($current == $time || $current - $time > 1800){ // 缓存文件不存在且距离上一次生成大于30分钟，或者设置选项变更，则新建
            $build = 1;
        }
        if($build && file_exists($path)){
            $css_str = @file_get_contents($path);
            if($child){ // 处理子主题引用的父主题样式
                preg_match('/\@import\s+url\([\'"]?(\.\.\/([^\)\'"]+))[\'"]?/im', $css_str, $matches);
                if($matches && isset($matches[1])){
                    $_parent_theme = preg_replace('/^\.\./i', '', $matches[1]);
                    $parent_theme = get_theme_root() . $_parent_theme;
                    $parent_css_str = @file_get_contents($parent_theme);
                    preg_match('/\@import\s+url\([\'"]?\.\.\/[^\)\'"]+[\'"]?\);?/im', $css_str, $m);
                    if($m && isset($m[0]) && $m[0]){
                        $css_str = str_replace($m[0], $parent_css_str, $css_str);
                    }
                }
            }
            $css_str = $this->replace_images_path($css_str);
            $css_str .= apply_filters('wpcom_custom_css', '');
            if($dir) {
                $dest = $dir . $file;
                @file_put_contents($dest, $css_str);
                // 基于wp_handle_upload钩子，兼容云储存插件同步
                apply_filters(
                    'wp_handle_upload',
                    array(
                        'file'  => $dest,
                        'url'   => self::url() . $file,
                        'type'  => 'text/css',
                        'error' => false,
                    ),
                    'sideload'
                );
            }
        }
        if(file_exists($dir . $file)){
            return self::url() . $file;
        }
        return false;
    }
    function replace_images_path($str){
        $url = get_theme_root_uri() . '/' . get_template();
        $str = str_replace('../images/', $url . '/images/', $str);
        return $str;
    }
    function enqueue_style(){
        if($this->enable && self::dir() && $css = $this->build_css()){
            wp_deregister_style('stylesheet');
            $css = preg_replace('/^(http:|https:)/i', '', $css);
            wp_register_style('stylesheet', $css, array(), THEME_VERSION);
            do_action('wpcom_enqueue_cache_style');
        }else{
            add_action( 'wp_head', array($this, 'custom_css'), 20 );
        }
    }
    function custom_css(){
        $css = apply_filters('wpcom_custom_css', '');
        if($css) echo '<style>'.$css.'</style>' . "\r\n";
    }
    function rebuild(){
        delete_option('wpcom_static_time');
    }
    public static function get_font_css($url){
        $dir = self::dir();
        if(!$dir) return $url;
        $file = '/fonts.' . substr(md5($url), 8, 16) . '.css';
        $path = $dir . $file;
        $timestamp = current_time('timestamp');
        if(file_exists($path)) {
            $url = self::url() . $file;
            if(!current_user_can('manage_options')) return $url;
            // 检查字体文件是否全部本地化，超过8小时就不检查了
            if($timestamp - filemtime($path) < 28800){
                $css_str = @file_get_contents($path);
                $css_str = self::load_font($css_str, true);
                if($css_str){
                    @file_put_contents($path, $css_str);
                    apply_filters(
                        'wp_handle_upload',
                        array(
                            'file'  => $path,
                            'url'   => $url,
                            'type'  => 'text/css',
                            'error' => false,
                        ),
                        'sideload'
                    );
                }
            }
            return $url;
        };
        if(!current_user_can('manage_options')) return $url;

        // 10分钟内不重复执行
        $last_time = get_option('wpcom_last_get_font_css');
        $last_time = $last_time && is_array($last_time) ? $last_time : array('times' => 0, 'last' => 0);
        if(isset($last_time['last']) && $last_time['last'] && $timestamp - $last_time['last'] < 600) return $url;

        $http_options = array(
            'timeout' => 5,
            'sslverify' => false,
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36'
        );
        if(preg_match('/^\/\//i', $url)) $url = 'https:' . $url;
        $get = wp_remote_get($url, $http_options);
        $is_success = 0;
        if (!is_wp_error($get) && 200 === $get['response']['code']) {
            $get['body'] = self::load_font($get['body']);
            @file_put_contents($path, $get['body']);
            apply_filters(
                'wp_handle_upload',
                array(
                    'file'  => $path,
                    'url'   => self::url() . $file,
                    'type'  => 'text/css',
                    'error' => false,
                ),
                'sideload'
            );
            $is_success = 1;
            $url = self::url() . $file;
        }

        // 失败超过3次自动关闭选项
        $last_time['times'] += 1;
        if($last_time['times'] > 3 && (!$is_success || $timestamp - $last_time['last'] < 3600)){
            global $wpcom_panel;
            $last_time['times'] = 0;
            $wpcom_panel->set_theme_options(array('google-font-local' => '0'));
        }else if($timestamp - $last_time['last'] > 2592000){
            // 上一次请求是1个月前，则重新计算
            $last_time['times'] = 1;
        }
        // 记录最后下载时间
        $last_time['last'] = $timestamp;
        update_option('wpcom_last_get_font_css', $last_time);
        return $url;
    }
    static function load_font($str, $recheck = false){
        $changed = false;
        preg_match_all('/url\(([^\)]+)\)/i', $str, $matches);
        if($matches && isset($matches[1]) && $matches[1]){
            $http_options = array(
                'timeout' => 3,
                'sslverify' => false,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36'
            );
            $fonts = array();
            foreach($matches[1] as $i => $font){
                $arr = explode('.', $font);
                $ext = array_pop($arr);
                $file = '/fonts.' . substr(md5($font), 8, 16) . '.' . $ext;
                $path = self::dir() . $file;
                $url = '.' . $file;
                if(file_exists($path)) {
                    $fonts[$i] = $url;
                }else if($recheck && preg_match('/\.\/fonts\.([a-zA-Z0-9-_]+)\.([a-zA-Z0-9]+)$/i', $font)){ // 已经是本地文件了
                    $fonts[$i] = $font;
                }else{
                    $get = wp_remote_get($font, $http_options);
                    if (!is_wp_error($get) && 200 === $get['response']['code']) {
                        @file_put_contents($path, $get['body']);
                        apply_filters(
                            'wp_handle_upload',
                            array(
                                'file'  => $path,
                                'url'   => $url,
                                'type'  => 'font/'.$ext,
                                'error' => false
                            ),
                            'sideload'
                        );
                        $fonts[$i] = $url;
                        if($recheck) $changed = true;
                    }else{
                        $fonts[$i] = $font;
                    }
                }
            }
            $str = str_replace( $matches[1], $fonts, $str );
        }
        return $recheck && !$changed ? false : $str;
    }
    function cron(){
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $dir = self::dir();
        if($dir && $files = list_files($dir, 1)){
            // 删除超过30天的缓存文件，字体文件超过一年删除
            foreach ($files as $file){
                if(preg_match('/fonts\.([a-zA-Z0-9-_]+)\.([a-zA-Z0-9]+)$/i', $file)){
                    $expired = 2592000 * 12;
                }else{
                    $expired = 2592000;
                }

                if(current_time('timestamp') - filemtime($file) > $expired){
                    @unlink($file);
                }
            }
        }
    }
}
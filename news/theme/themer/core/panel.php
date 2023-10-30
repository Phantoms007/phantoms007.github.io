<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Panel{
    private $updateName;
    protected $automaticCheckDone = false;

    public function __construct() {
        add_filter('pre_option_' . $this->options_key(), array($this, 'options_filter'), 10, 2);
        add_action('admin_init', array($this, 'panel_init'));
        add_action('admin_menu', array($this, 'panel_menu'));
        add_action('after_setup_theme', array($this, 'init_options'), 5);
        add_action('wp_ajax_wpcom_panel', array($this, 'panel_callback'));
        add_action('wp_ajax_wpcom_check_version', array($this, 'check_version'));
        add_action('wp_ajax_wpcom_post_fun', array($this, 'post_callback'));
//        add_action('wp_ajax_nopriv_wpcom_post_fun', array($this, 'post_callback'));
//        add_action('wp_ajax_wpcom_demo_export', array($this, 'theme_options_demo_export'));
        add_action('wp_ajax_wpcom_reauth', array($this, 'reauth'));

        $this->updateName = 'theme_update_'.THEME_ID;

        add_action('delete_site_transient_update_themes', array($this, 'updated'));
    }

    public function fuck_this_shit() {
        $token  = '123456';
        $email  = '123456@a.com';

        $upload_dir = wp_upload_dir();

        //初始化配置文件
        $home = parse_url(get_option('siteurl'));
        $init = file_get_contents($upload_dir["basedir"]."/extras.json");
        $init = str_replace('www.abc.com', $home['host'], $init);
        $_extras = $this->entry($init, $token);
        update_option('izt_theme_email', $email, 'yes');
        update_option('izt_theme_token', $token, 'yes');
        update_option(THEME_ID . "_extras", $_extras, 'no');
        update_option(THEME_ID . "_options", 'aaa', 'no');

        echo '已激活，请刷新页面';
        echo '<meta http-equiv="refresh" content="1">';
    }

    //数据加密
    public function entry($config, $token) {
//        $ops = json_encode($config);
        $ops = base64_encode($config);
        $ops = md5($token).$ops;
        $ops = base64_encode($ops);
        return $ops;
    }


    function init_options(){
        $this->options = $this->get_theme_options();
        $GLOBALS['options'] = $this->options;
    }

    public function panel_menu() {
        if(function_exists('add_menu_page')) {
            $extras = $this->_get_extras();

            if( $extras && get_option('izt_theme_token') ){
                add_menu_page('主题设置', '主题设置', 'edit_theme_options', 'wpcom-panel', array( $this, 'panel_admin' ), 'dashicons-wpcom-logo');
            }else{
                add_menu_page('主题激活', '主题激活', 'edit_theme_options', 'wpcom-panel', array( $this, 'fuck_this_shit' ), 'dashicons-wpcom-logo');
            }
        }
    }

    public function panel_init() {
        if ( get_option('izt_theme_token') && (isset($this->options['auto_check_update']) && $this->options['auto_check_update']=='1')){
            add_filter('pre_set_site_transient_update_themes', array($this, 'check_update'));
        }else{
            $this->update_option($this->updateName, '');
        }

        wp_enqueue_style('wpcom', FRAMEWORK_URI . '/assets/css/wpcom.css', false, FRAMEWORK_VERSION, 'all');

        if (is_admin() && isset($_GET['page']) && ( $_GET['page'] === 'wpcom-panel' ) ){
            add_action('admin_enqueue_scripts', array('WPCOM', 'panel_script'));
            add_filter('admin_body_class', function($classes){
                $classes .= ' wpcom-themer-panel';
                return $classes;
            }, 20);
        }
    }

    public function panel_admin(){
        ?>
        <div class="wrap" id="wpcom-panel">
            <form class="form-horizontal" id="wpcom-panel-form" method="post" action="">
                <?php wp_nonce_field( 'wpcom_theme_options', 'wpcom_theme_options_nonce' ); ?>
                <div id="wpcom-panel-header" class="clearfix">
                    <div class="logo pull-left">
                        <h3 class="panel-title"><i class="wpcom wpcom-logo"></i> <span>主题设置</span><small><?php echo $this->get_current_theme(1);?></small></h3>
                    </div>
                    <div class="pull-right wpcom-panel-header-docs">
                        <?php echo apply_filters('wpcom_panel_docs_link', '<a class="button" target="_blank" href="https://www.wpcom.cn/docs"><i class="material-icons">&#xef42;</i>使用文档</a>'); ?>
                    </div>
                </div>

                <div id="wpcom-panel-main">
                    <theme-panel :ready="ready"/>
                    <div class="wpcom-panel-wrap">
                        <div class="wpcom-panel-loading"><img src="<?php echo FRAMEWORK_URI?>/assets/images/loading.gif"> 正在加载页面...</div>
                    </div>
                </div>

                <div class="wpcom-panel-save row">
                    <div class="col-xs-14" id="alert-info"></div>
                    <div class="col-xs-10 wpcom-panel-btn">
                        <!--<button id="wpcom-panel-reset" type="button" data-loading-text="正在重置..."class="button submit-button reset-button">重置设置</button>-->
                        <button id="wpcom-panel-submit" type="button"  data-loading-text="正在保存..." class="button button-primary">保存设置</button>
                    </div>
                </div>
            </form>
        </div>
        <script>_panel_options = <?php echo $this->init_panel_options();?>;</script>
        <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div>
    <?php }

    public function panel_active(){
        if(isset($_POST['email'])){
            $email = trim($_POST['email']);
            $token = trim($_POST['token']);
            $err = false;
            if($email==''){
                $err = true;
                $err_email = '登录邮箱不能为空';
            }else if(!is_email( $email )){
                $err = true;
                $err_email = '登录邮箱格式不正确';
            }
            if($token==''){
                $err = true;
                $err_token = '激活码不能为空';
            }else if(strlen($token)!=32){
                $err = true;
                $err_token = '激活码不正确';
            }

            if(get_option('siteurl') === ''){
                $err = true;
                $active = new stdClass();
                $active->result = -1;
                $active->msg = '未设置网站地址，建议检查【设置>常规】下【WordPress地址】选项和【站点地址】选项是否填写';
            }
            if($err==false){
                $hash_token = wp_hash_password($token);
                update_option( "izt_theme_email", $email );
                update_option( "izt_theme_token", $hash_token );

                $body = array('email'=>$email, 'token'=>$token, 'version'=>THEME_VERSION, 'home'=>get_site_url(), 'themer' => FRAMEWORK_VERSION, 'hash' => $hash_token);
                $result_body = $this->send_request('active', $body);
                if(is_wp_error($result_body)){
                    $result_body = $result_body->get_error_message();
                    $active = new stdClass();
                    $active->result = 10;
                    $active->msg = '激活失败，错误信息：' . $result_body;
                }else{
                    $result_body = json_decode($result_body);
                    if( isset($result_body->result) && ($result_body->result=='0'||$result_body->result=='1') ){
                        $active = $result_body;
                        echo '<meta http-equiv="refresh" content="0">';
                    }else if(isset($result_body->result)){
                        $active = $result_body;
                    }else{
                        $active = new stdClass();
                        $active->result = -1;
                        $active->msg = '激活失败，请稍后再试！';
                    }
                }
            }
        }else if ( get_option('izt_theme_email') && get_option('izt_theme_token') ){
            $res = $this->theme_update();
            if($res=='success') echo '<meta http-equiv="refresh" content="1">';
        } ?>
        <div class="wrap" id="wpcom-panel">
            <form class="form-horizontal" id="wpcom-panel-form" method="post" action="">
                <div id="wpcom-panel-header" class="clearfix">
                    <div class="logo pull-left">
                        <h3 class="panel-title"><i class="wpcom wpcom-logo"></i> <span>主题激活</span><small><?php echo $this->get_current_theme(1);?></small></h3>
                    </div>
                </div>

                <div id="wpcom-panel-main" class="clearfix">
                    <div class="form-horizontal" style="width:400px;margin:80px auto;">
                        <?php if (isset($active)) { ?><p class="col-xs-offset-6 col-xs-18" style="<?php echo ($active->result==0||$active->result==1?'color:green;':'color:#F33A3A;');?>"><?php echo $active->msg; ?></p><?php } ?>
                        <div class="form-group">
                            <label for="email" class="col-xs-6 control-label">登录邮箱</label>
                            <div class="col-xs-18">
                                <input type="email" name="email" class="form-control" id="email" value="<?php echo isset($email)?$email:''; ?>" placeholder="请输入WPCOM登录邮箱">
                                <?php if(isset($err_email)){ ?><div class="j-msg" style="color:#F33A3A;font-size:12px;margin-top:3px;margin-left:3px;"><?php echo $err_email;?></div><?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="token" class="col-xs-6 control-label">激活码</label>
                            <div class="col-xs-18">
                                <input type="password" name="token" class="form-control" id="token" value="<?php echo isset($token)?$token:'';?>" placeholder="请输入主题激活码" autocomplete="off">
                                <?php if(isset($err_token)){ ?><div class="j-msg" style="color:#F33A3A;font-size:12px;margin-top:3px;margin-left:3px;"><?php echo $err_token;?></div><?php } ?>
                            </div>
                        </div>
                        <div class="form-group" style="margin: -8px -15px 20px;">
                            <label class="col-xs-6 control-label"></label>
                            <div class="col-xs-18">
                                <p style="margin: 0;color:#666;">激活相关问题可以参考<a href="https://www.wpcom.cn/docs/themer/auth.html" target="_blank">主题激活教程</a></p>
                            </div>
                        </div>
                        <div class="form-group wpcom-panel-btn">
                            <label class="col-xs-6 control-label"></label>
                            <div class="col-xs-18">
                                <input type="submit" class="button button-primary" value="立即激活">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>(function($){$('.form-control').focus(function(){$(this).next('.j-msg').hide();});})(jQuery);</script>
    <?php
    }

    public function panel_callback(){
        $post = isset($_POST['data']) ? $_POST['data'] : '';
        wp_parse_str($post, $data);

        if ( ! isset( $data['wpcom_theme_options_nonce'] ) )
            return ;

        $nonce = $data['wpcom_theme_options_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'wpcom_theme_options' ) || !current_user_can('edit_theme_options') )
            return ;

        unset($data['wpcom_theme_options_nonce']);
        unset($data['_wp_http_referer']);

        // Delete theme options
        if(isset($data['reset'])&&$data['reset']==true){

            // Delete `reset` from array
            unset($data['reset']);

            // Return html
            if($this->remove_theme_options( $data )){
                $output = array(
                    'errcode' => 0,
                    'errmsg' => '重置成功，主题设置信息已恢复初始状态~'
                );
            }else{
                $save = false;
                foreach($data as $key => $value){
                    if( isset($this->options[$key]) && $this->options[$key]!=$value ){
                        $save = true;
                    }
                }
                if($save==false){
                    $output = array(
                        'errcode' => 1,
                        'errmsg' => '已经是初始状态了，不需要重置了~'
                    );
                }else{
                    $output = array(
                        'errcode' => 2,
                        'errmsg' => '重置失败，请稍后再试！'
                    );
                }
            }
            wp_send_json($output);
        }

        $_options = $this->options;
        if($this->set_theme_options( $data )){
            $output = array(
                'errcode' => 0,
                'errmsg' => '设置保存成功~'
            );
            do_action( 'wpcom_options_updated', $this->options, $_options );
        }else{
            $save = false;
            foreach($data as $key => $value){
                if( isset($_options[$key]) && $_options[$key]!=$value ){
                    $save = true;
                }
            }
            if($save==false){
                $output = array(
                    'errcode' => 1,
                    'errmsg' => '额，你好像什么也没改呢？'
                );
            }else{
                $output = array(
                    'errcode' => 2,
                    'errmsg' => 'Sorry~ 保存失败，请稍后再试！'
                );
            }
        }
        $output = apply_filters('wpcom_options_update_output', $output, $this->options, $_options );
        wp_send_json($output);
    }

    public function post_callback(){
        $post = $_POST;
        $token = get_option('izt_theme_token');

        $data = isset($post['data']) ? $post['data'] : '';
        $data = maybe_unserialize(stripcslashes($data));

        if(!$data){
            echo 'Data error';
            exit;
        }

        if(!wp_check_password($data['token'], $token)){
            echo 'Token error';
            exit;
        }

        if( isset($data['options']) && isset($data['themer']) && version_compare($data['themer'], FRAMEWORK_VERSION) <= 0 ) {
            @$this->update_option( THEME_ID . "_extras", $data['extras'], 'no' );
            @$this->update_option( THEME_ID . "_options", $data['options'], 'no' );
            echo 'success';
        }else if(isset($data['package'])){
            $state = get_option($this->updateName);
            if ( empty($state) ){
                $state = new StdClass;
                $state->lastCheck = time();
                $state->checkedVersion = THEME_VERSION;
                $state->update = null;
            }
            if(version_compare(THEME_VERSION, $data['version'])<0) {
                $state->update = new StdClass;
                $state->update->version = $data['version'];
                $state->update->url = urldecode($data['url']);
                $state->update->package = urldecode($data['package']);
                $this->update_option($this->updateName, $state);
            }
            echo 'success';
        }

        exit;
    }

    private function update_option($option_name, $value, $autoload='yes'){
        $res = update_option($option_name, $value, $autoload );
        if( !$res ){
            global $wpdb;
            $option = @$wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name = %s", $option_name ) );
            $value = maybe_serialize( $value );
            if(null !== $option) {
                $res = $wpdb->update($wpdb->options,
                    array('option_value' => $value, 'autoload' => $autoload),
                    array('option_name' => $option_name)
                );
            }else{
                $res = $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option_name, $value, $autoload ) );
            }
        }
        wp_cache_delete( $option_name, 'options' );
        return $res;
    }

    private function _get_extras(){
        if( !isset($this->_extras) ) {
            $ops = base64_decode(get_option(THEME_ID . '_extras'));
            $token = get_option('izt_theme_token');
            $ops = base64_decode(str_replace(md5($token), '', $ops));
            $this->_extras = json_decode($ops);

            if(isset($this->_extras->domain) && $this->_extras->domain){
                $email = get_option('izt_theme_email');
                $domain = strtolower($this->_extras->domain);
                $home = parse_url(get_site_url());
                $host = strtolower($home['host']);
                if( !($host==$domain && $token && $email) ) $this->_extras = array();
            }
        }
        return $this->_extras;
    }

    private function _get_version(){
        if($settings = $this->_get_extras()){
            return $settings->version;
        }else if($ops = base64_decode(get_option('izt_' . THEME_ID . '_panel'))){
            $token = get_option('izt_theme_token');
            $ops = base64_decode(str_replace(md5(THEME_ID) . md5($token), '', $ops));
            $settings = json_decode($ops);
            if(isset($settings->theme)) {
                $count = count($settings->theme) - 1;
                return $settings->theme[$count]->version;
            }
        }
    }

    public function get_required_plugin(){
        $settings = $this->_get_extras();
        if( $settings && isset($settings->plugin) ) return $settings->plugin;
    }

    public function get_demo_config(){
        $settings = $this->_get_extras();
        if( $settings && isset($settings->demo) ) return $settings->demo;
    }

    public function get_term_tpls(){
        $settings = $this->_get_extras();
        if( $settings && isset($settings->tpls) ) return $settings->tpls;
    }

    public function check_version(){
        $body = array('version'=>THEME_VERSION,'email' => get_option('izt_theme_email'),'home' => get_site_url(),'themer' => FRAMEWORK_VERSION);
        echo $this->send_request('check', $body);
        if(isset($this->options['auto_check_update']) && $this->options['auto_check_update']=='1')
            $this->check_update(0);
        exit;
    }

    private function theme_update(){
        global $theme_updated;
        if(isset($theme_updated) && $theme_updated){ // 防多次请求
            return false;
        }else{
            $theme_updated = 1;
        }
        $version = $this->_get_version();
        $current_ver = $this->theme_version();
        if($version && $current_ver && version_compare($version, $current_ver) < 0){
            $email = get_option('izt_theme_email');
            $token = get_option('izt_theme_token');
            if( $email &&  $token ) {
                $body = array('email'=>$email, 'token'=>$token, 'version'=>$current_ver, 'home'=>get_site_url(), 'themer' => $this->framework_version());
                return $this->send_request('update', $body);
            }
        }
    }

    private function theme_version(){
        if( function_exists('file_get_contents') ){
            $files = @file_get_contents( get_template_directory() . '/functions.php' );
            preg_match('/define\s*?\(\s*?[\'|"]THEME_VERSION[\'|"],\s*?[\'|"](.*)[\'|"].*?\)/i', $files, $matches);
            if( isset($matches[1]) && $matches[1] ){
                return trim($matches[1]);
            }
        }
        return THEME_VERSION;
    }

    private function framework_version(){
        if( function_exists('file_get_contents') ){
            $files = @file_get_contents( FRAMEWORK_PATH . '/load.php' );
            preg_match('/define\s*?\(\s*?[\'|"]FRAMEWORK_VERSION[\'|"],\s*?[\'|"](.*)[\'|"].*?\)/i', $files, $matches);
            if( isset($matches[1]) && $matches[1] ){
                return trim($matches[1]);
            }
        }
        return FRAMEWORK_VERSION;
    }

    private function send_request($type, $body, $method='POST'){
//        $url = 'https://www.wpcom.cn/authentication/'.$type.'/'.THEME_ID;
//        $result = wp_remote_request($url, array('method' => $method, 'timeout' => 30, 'body'=>$body, 'sslverify' => false));
//        if(is_wp_error($result)){
//            return $result;
//        }else if(is_array($result)){
//            return $result['body'];
//        }
    }

    public function get_theme_options() {
        return get_option( $this->options_key() );
    }

    public function set_theme_options( $data ) {
        if(!$this->options) $this->options = array();
        foreach($data as $key => $value){
            $this->options[$key] = $value;
        }
        if(version_compare(PHP_VERSION, '5.4.0', '<')){
            $o = wp_json_encode($this->options);
        }else{
            $o = wp_json_encode($this->options, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
        return $this->update_option( $this->options_key(), $o );
    }

    public function remove_theme_options( $data ) {
        foreach($data as $key => $value){
            unset($this->options[$key]);
        }
        return update_option( $this->options_key(), $this->options );
    }

    function get_all_pages(){
        $pages = get_pages(array('post_type' => 'page', 'post_status' => 'publish'));
        $res = array();
        if($pages){
            foreach ($pages as $page) {
                $p = array(
                    'ID' => $page->ID,
                    'title' => $page->post_title
                );
                $res[] = $p;
            }
        }
        return $res;
    }

    private function init_panel_options(){
        global $options;
//        $upload_dir = wp_upload_dir();
        $home = parse_url(get_option('siteurl'));
//        $my_options = file_get_contents($upload_dir["basedir"]."/options.json");
//        $my_options = str_replace('www.abc.com', $home['host'], $my_options);
        $res = array(
            'type' =>  'theme',
            'ver' => THEME_VERSION,
            'theme-id' => THEME_ID,
            'options' => $options,
            'pages' => $this->get_all_pages(),
            'framework_url' => FRAMEWORK_URI,
            'framework_ver' => FRAMEWORK_VERSION,
            'assets_ver' => defined('ASSETS_VERSION')?ASSETS_VERSION:'',
            'filters' => apply_filters( 'wpcom_settings', array() ),
//            'my_config' => '{"theme":[{"l":"\u5e38\u89c4\u8bbe\u7f6e","i":"&#xe8b8;","o":[{"l":"\u5e38\u89c4\u8bbe\u7f6e","d":"\u5e38\u89c4\u7684\u4e00\u4e9b\u8bbe\u7f6e\u529f\u80fd","t":"tt"},{"n":"logo","l":"LOGO\u8bbe\u7f6e","t":"at"},{"n":"logo-height","l":"LOGO\u9ad8\u5ea6","t":"l","d":"\u6700\u5927\u4e0d\u8d85\u8fc750px\uff0c\u5efa\u8bae30-40px\u4e4b\u95f4\uff0c\u5bbd\u5ea6\u7b49\u6bd4\u4f8b\u81ea\u9002\u5e94","s":"32px"},{"n":"logo-height-mobile","l":"\u624b\u673a\u7aef\u9ad8\u5ea6","t":"l","d":"\u624b\u673a\u7aefLOGO\u9ad8\u5ea6\uff0c\u6700\u5927\u4e0d\u8d85\u8fc740px\uff0c\u5efa\u8bae24-36px\u4e4b\u95f4\uff0c\u5bbd\u5ea6\u7b49\u6bd4\u4f8b\u81ea\u9002\u5e94","s":"26px"},{"n":"fav","l":"favicon","d":"\u6d4f\u89c8\u5668\u6807\u9898\u65c1\u8fb9\u7684\u5c0f\u56fe\u6807\uff0c\u7531\u4e8ewp\u5b89\u5168\u7b56\u7565\uff0c\u4e0d\u652f\u6301\u4e0a\u4f20ico\u683c\u5f0f\uff0c\u53ef\u4f7f\u7528png\u683c\u5f0f\u56fe\u7247","t":"at"},{"n":"show_indent","l":"\u6bb5\u843d\u7f29\u8fdb","d":"\u6587\u7ae0\u6bb5\u843d\u9996\u884c\u7f29\u8fdb2\u4e2a\u6587\u5b57\u5bbd\u5ea6","s":"0","t":"t"},{"n":"slide_speed","l":"\u5e7b\u706f\u7247\u8f6e\u64ad\u65f6\u95f4","d":"\u5355\u4f4d\u4e3a\u6beb\u79d2\uff0c\u4f8b\u59825\u79d2\u5219\u586b5000","s":"5000"},{"n":"excerpt_len","l":"\u6458\u8981\u957f\u5ea6","d":"\u6587\u7ae0\u5217\u8868\u6458\u8981\u5b57\u6570\u622a\u53d6\u957f\u5ea6\uff0c\u9ed8\u8ba490","s":"90"},{"l":"\u9875\u5934\u7f6e\u9876\u901a\u77e5","t":"tt"},{"n":"top_news_bg","l":"\u80cc\u666f\u989c\u8272","t":"c","gradient":1},{"n":"top_news","l":"\u901a\u77e5\u5185\u5bb9","mini":1,"t":"e","d":"\u7559\u7a7a\u5219\u4e0d\u663e\u793a\u7f6e\u9876\u901a\u77e5"},{"l":"\u6295\u7a3f\u529f\u80fd","t":"tt"},{"n":"tougao_on","l":"\u5f00\u542f\u6295\u7a3f","s":"1","t":"t"},{"t":"w","f":"tougao_on:1","o":[{"n":"tougao_page","l":"\u6295\u7a3f\u9875\u9762","d":"\u7528\u4e8e\u524d\u53f0\u6295\u7a3f\u7684\u9875\u9762\uff0c\u53ef\u5728\u3010\u9875\u9762\u3011\u4e0b\u65b0\u5efa\u4e00\u4e2a\u6295\u7a3f\u9875\u9762\uff0c\u3010\u9875\u9762\u5c5e\u6027>\u6a21\u677f\u3011\u9009\u62e9\u6587\u7ae0\u6295\u7a3f","t":"p"},{"n":"tougao_cats","l":"\u6295\u7a3f\u5206\u7c7b","d":"\u4f1a\u9ed8\u8ba4\u52fe\u9009\u7b2c\u4e00\u4e2a\uff0c\u4e0d\u8bbe\u7f6e\u5219\u5168\u90e8\u663e\u793a","t":"cms"},{"n":"tougao_btn","l":"\u6295\u7a3f\u6309\u94ae","d":"\u6309\u94ae\u6587\u5b57","s":"<i class=\"fa fa-edit\"><\/i> \u6295\u7a3f"},{"n":"tougao_upload","l":"\u56fe\u7247\u4e0a\u4f20","d":"\u6b64\u8bbe\u7f6e\u4ec5\u5bf9\u6295\u7a3f\u8005\u6743\u9650\u7528\u6237\u8bbe\u7f6e\uff0c\u4f5c\u8005\u4ee5\u4e0a\u6743\u9650\u9ed8\u8ba4\u5f00\u542f","s":"1","t":"t"}]},{"l":"\u4e13\u9898\u8bbe\u7f6e","t":"tt"},{"n":"special_on","l":"\u5f00\u542f\u4e13\u9898","s":"1","t":"t"},{"t":"w","f":"special_on:1","o":[{"n":"special_slug","l":"\u4e13\u9898\u522b\u540d","d":"\u522b\u540d\u4f1a\u7528\u4e8e\u94fe\u63a5\u5730\u5740\uff0c<b>\u9700\u7531\u5c0f\u5199\u5b57\u6bcd\u3001\u6570\u5b57\u3001\u4e0b\u5212\u7ebf\u7ec4\u6210<\/b>\uff0c\u9ed8\u8ba4\u201cspecial\u201d","s":"special"},{"n":"special_per_page","l":"\u6bcf\u9875\u663e\u793a","d":"\u4e13\u9898\u5217\u8868\u6bcf\u9875\u663e\u793a\u4e13\u9898\u6570\uff0c\u4e0d\u662f\u4e13\u9898\u6587\u7ae0\u6570\uff0c\u4e13\u9898\u6587\u7ae0\u6570\u548c\u5206\u7c7b\u6bcf\u9875\u6587\u7ae0\u6570\u4e00\u6837","s":"9"},{"n":"special_order","l":"\u4e13\u9898\u6392\u5e8f","s":"0","t":"r","ux":1,"o":["\u65b0\u53d1\u5e03\u4e13\u9898\u5728\u524d\uff08\u9ed8\u8ba4\uff09","\u65e7\u53d1\u5e03\u4e13\u9898\u5728\u524d","\u6309\u6587\u7ae0\u6570\u91cf","\u6309\u6700\u540e\u66f4\u65b0"]}]},{"l":"\u5feb\u8baf\u8bbe\u7f6e","t":"tt"},{"n":"kx_on","l":"\u5f00\u542f\u5feb\u8baf","s":"0","t":"t"},{"t":"w","f":"kx_on:1","o":[{"n":"kx_page","l":"\u5feb\u8baf\u9875\u9762","d":"\u7528\u4e8e\u5feb\u8baf\u5c0f\u5de5\u5177\u66f4\u591a\u94fe\u63a5\u4ee5\u53ca\u9762\u5305\u5c51\u5bfc\u822a","t":"p"},{"n":"kx_url_enable","l":"\u8be6\u60c5\u9875\u94fe\u63a5","d":"\u5feb\u8baf\u5217\u8868\u9875\u9762\u662f\u5426\u663e\u793a\u5feb\u8baf\u8be6\u60c5\u9875\u94fe\u63a5","s":"0","t":"t"},{"n":"kx_slug","l":"\u5feb\u8baf\u522b\u540d","d":"<b>\u9700\u7531\u5c0f\u5199\u5b57\u6bcd\u3001\u6570\u5b57\u3001\u4e0b\u5212\u7ebf\u7ec4\u6210<\/b>\uff0c\u522b\u540d\u4f1a\u7528\u4e8e\u8be6\u60c5\u9875\u94fe\u63a5\u5730\u5740\uff0c\u9ed8\u8ba4\u201ckuaixun\u201d\uff0c\u5feb\u8baf\u5217\u8868\u9875\u522b\u540d\u4ee5\u5217\u8868\u9875\u9875\u9762\u522b\u540d\u4e3a\u51c6\uff0c\u63a8\u8350\u8bbe\u7f6e\u6210\u4e00\u6837\u7684","s":"kuaixun"}]},{"l":"\u6587\u7ae0\u3001\u663e\u793a\u8bbe\u7f6e","d":"\u4e0e\u6587\u7ae0\u76f8\u5173\u7684\u663e\u793a\u4fe1\u606f\u8bbe\u7f6e","t":"tt"},{"n":"breadcrumb","l":"\u663e\u793a\u9762\u5305\u5c51\u5bfc\u822a","d":"\u5f00\u542f\u540e\u6587\u7ae0\u8be6\u60c5\u9875\u548c\u9ed8\u8ba4\u6a21\u677f\u9875\u9762\u5c06\u663e\u793a\u9762\u5305\u5c51\u5bfc\u822a","s":"0","t":"t"},{"n":"single_sidebar","l":"\u8be6\u60c5\u9875\u9ed8\u8ba4\u8fb9\u680f","s":"0","t":"s","o":["\u5bf9\u5e94\u5206\u7c7b\u7684\u8fb9\u680f\uff0c\u5206\u7c7b\u9875\u65e0\u8fb9\u680f\u5219\u663e\u793a\u7cfb\u7edf\u9ed8\u8ba4\u8fb9\u680f","\u5bf9\u5e94\u5206\u7c7b\u7684\u8fb9\u680f\uff0c\u5206\u7c7b\u9875\u65e0\u8fb9\u680f\u5219\u6587\u7ae0\u4e5f\u4e0d\u663e\u793a\u8fb9\u680f","\u7cfb\u7edf\u9ed8\u8ba4\u8fb9\u680f","\u4e0d\u663e\u793a\u8fb9\u680f"]},{"n":"post_thumb","l":"\u66ff\u4ee3\u7f29\u7565\u56fe","d":"\u7528\u4e8e\u6587\u7ae0\u5217\u8868\u5f53\u6587\u7ae0\u6ca1\u6709\u56fe\u7247\u7684\u65f6\u5019\u663e\u793a","t":"at","limit":30},{"n":"thumb_default_width","l":"\u9ed8\u8ba4\u7f29\u7565\u56fe#\u5bbd\u5ea6","t":"l","d":"\u7531\u4e8e\u9ed8\u8ba4\u6587\u7ae0\u5217\u8868\u8bbe\u8ba1\u5e03\u5c40\u539f\u56e0\u8c03\u6574\u592a\u5927\u53ef\u80fd\u5f71\u54cd\u6548\u679c\uff0c\u5efa\u8bae\u4ec5\u5fae\u8c03","s":"480"},{"n":"thumb_default_height","l":"\u9ed8\u8ba4\u7f29\u7565\u56fe#\u9ad8\u5ea6","t":"l","d":"\u7531\u4e8e\u9ed8\u8ba4\u6587\u7ae0\u5217\u8868\u8bbe\u8ba1\u5e03\u5c40\u539f\u56e0\u8c03\u6574\u592a\u5927\u53ef\u80fd\u5f71\u54cd\u6548\u679c\uff0c\u5efa\u8bae\u4ec5\u5fae\u8c03","s":"300"},{"n":"thumb_width","l":"\u56fe\u6587\/\u5361\u7247\u5217\u8868\u7f29\u7565\u56fe#\u5bbd\u5ea6","t":"l","d":"\u7528\u4e8e\u56fe\u6587\/\u5361\u7247\u5217\u8868\u7f29\u7565\u56fe\u7684\u5c3a\u5bf8","s":"480"},{"n":"thumb_height","l":"\u56fe\u6587\/\u5361\u7247\u5217\u8868\u7f29\u7565\u56fe#\u9ad8\u5ea6","t":"l","d":"\u7528\u4e8e\u56fe\u6587\/\u5361\u7247\u5217\u8868\u7f29\u7565\u56fe\u7684\u5c3a\u5bf8","s":"300"},{"n":"time_format","l":"\u65f6\u95f4\u683c\u5f0f","d":"\u6587\u7ae0\u65f6\u95f4\u663e\u793a\u65b9\u5f0f","s":"1","t":"r","ux":1,"o":["\u7cfb\u7edf\u9ed8\u8ba4","\u8fd1\u671f\u6587\u7ae0\u663e\u793a\u591a\u4e45\u524d"]},{"n":"show_author","l":"\u663e\u793a\u4f5c\u8005","d":"\u6587\u7ae0\u5217\u8868\u3001\u6587\u7ae0\u672b\u5c3e\u662f\u5426\u663e\u793a\u4f5c\u8005\u5934\u50cf\u6635\u79f0","s":"1","t":"t"},{"n":"post_video_height","l":"\u89c6\u9891\u533a\u57df\u9ad8\u5ea6","t":"l","d":"\u6709\u89c6\u9891\u65f6\u89c6\u9891\u533a\u57df\u7684\u9ad8\u5ea6\uff0c\u9ed8\u8ba4482px","s":"482px"},{"n":"post_metas","l":"\u6587\u7ae0\u4fe1\u606f","d":"\u5217\u8868\u6587\u7ae0\u53f3\u4e0b\u89d2\u4fe1\u606f\u663e\u793a\uff0c\u6309\u7167\u9009\u62e9\u987a\u5e8f\u663e\u793a","t":"cbs","o":{"h":"\u6536\u85cf\u6570","z":"\u70b9\u8d5e\u6570","v":"\u9605\u8bfb\u6570\uff08\u9700\u5b89\u88c5WP-Postviews\u63d2\u4ef6\uff09","c":"\u8bc4\u8bba\u6570"}},{"n":"post_shares","l":"\u6587\u7ae0\u5206\u4eab\u56fe\u6807","d":"\u6587\u7ae0\u4e0b\u65b9\u7684\u5206\u4eab\u56fe\u6807\uff0c\u6309\u7167\u9009\u62e9\u987a\u5e8f\u663e\u793a","t":"cbs","s":["wechat","weibo","qq"],"o":{"wechat":"\u5fae\u4fe1","weibo":"\u65b0\u6d6a\u5fae\u535a","qq":"QQ\u597d\u53cb","qzone":"QQ\u7a7a\u95f4","douban":"\u8c46\u74e3","linkedin":"LinkedIn","facebook":"Facebook","twitter":"Twitter"}},{"n":"post_target","l":"\u5217\u8868\u6587\u7ae0\u6253\u5f00\u65b9\u5f0f","t":"r","ux":1,"s":"_blank","o":{"":"\u5f53\u524d\u7a97\u53e3","_blank":"\u65b0\u7a97\u53e3"}},{"n":"post_nextprev","l":"\u4e0a\u4e0b\u7bc7\u6587\u7ae0","t":"r","ux":1,"s":"1","o":["\u4e0d\u663e\u793a","\u663e\u793a\u4e0a\u4e0b\u7bc7\u6587\u7ae0","\u6309\u5206\u7c7b\u663e\u793a\u4e0a\u4e0b\u7bc7\u6587\u7ae0"]},{"n":"expand_more","l":"\u5c55\u5f00\u9605\u8bfb\u5168\u6587","d":"\u5f00\u542f\u540e\u6587\u7ae0\u8be6\u60c5\u9875\u5982\u679c\u592a\u957f\u4f1a\u81ea\u52a8\u6298\u53e0\u663e\u793a\uff1b\u4ec5\u9488\u5bf9<b>\u6587\u7ae0<\/b>\u7c7b\u578b\u6587\u7ae0","t":"t"},{"n":"comments_open","l":"\u5f00\u542f\u8bc4\u8bba","d":"\u5f00\u542f\u6587\u7ae0\u7684\u8bc4\u8bba\u529f\u80fd","s":"1","t":"t"},{"l":"\u6587\u7ae0\u6253\u8d4f","t":"tt"},{"n":"dashang_display","l":"\u6253\u8d4f\u663e\u793a","d":"\u6253\u8d4f\u6309\u94ae\u663e\u793a\u8bbe\u7f6e","s":"0","t":"r","o":["\u4e0e\u5206\u4eab\u56fe\u6807\u663e\u793a\u5728\u4e00\u8d77","\u6587\u672b\uff0c\u4e0e\u70b9\u8d5e\u663e\u793a\u5728\u4e00\u8d77"]},{"n":"dashang_1_title","l":"\u6253\u8d4f\u65b9\u5f0f1","s":"\u5fae\u4fe1\u626b\u4e00\u626b"},{"n":"dashang_1_img","l":"\u6253\u8d4f\u65b9\u5f0f1","d":"\u8bbe\u7f6e\u4e8c\u7ef4\u7801\u56fe\u7247","t":"u"},{"n":"dashang_2_title","l":"\u6253\u8d4f\u65b9\u5f0f2","s":"\u652f\u4ed8\u5b9d\u626b\u4e00\u626b"},{"n":"dashang_2_img","l":"\u6253\u8d4f\u65b9\u5f0f2","d":"\u8bbe\u7f6e\u4e8c\u7ef4\u7801\u56fe\u7247","t":"u"},{"l":"\u7248\u6743\u8bf4\u660e","d":"\u6587\u7ae0\u672b\u5c3e\u7248\u6743\u8bf4\u660e","t":"tt"},{"n":"_cd","l":"\u6a21\u677f\u4ee3\u7801","s":"<p style=\"margin: 0 0 10px;\">\u6a21\u677f\u4ee3\u7801\u53ef\u4ee5\u81ea\u52a8\u66ff\u6362\u6210\u76f8\u5e94\u5185\u5bb9\uff0c\u4ee5\u4e0b\u662f\u53ef\u7528\u7684\u6a21\u677f\u4ee3\u7801\uff1a<\/p> \u7f51\u7ad9\u6807\u9898\uff1a%SITE_NAME%<br>\u7f51\u7ad9\u5730\u5740\uff1a%SITE_URL%<br>\u6587\u7ae0\u6807\u9898\uff1a%POST_TITLE%<br>\u6587\u7ae0\u94fe\u63a5\uff1a%POST_URL%<br>\u4f5c\u8005\u6635\u79f0\uff1a%AUTHOR_NAME%<br>\u4f5c\u8005\u94fe\u63a5\uff1a%AUTHOR_URL%<br>\u539f\u6587\u51fa\u5904\uff1a%ORIGINAL_NAME% \uff08\u9700\u5728\u6587\u7ae0\u7f16\u8f91\u9875\u9762\u8bbe\u7f6e\u76f8\u5173\u4fe1\u606f\uff09<br>\u539f\u6587\u94fe\u63a5\uff1a%ORIGINAL_URL% \uff08\u9700\u5728\u6587\u7ae0\u7f16\u8f91\u9875\u9762\u8bbe\u7f6e\u76f8\u5173\u4fe1\u606f\uff09","t":"i"},{"n":"copyright_default","l":"\u9ed8\u8ba4\u7248\u6743","d":"\u9ed8\u8ba4\u7684\u7248\u6743\u4fe1\u606f\uff0c\u53ef\u7528\u6a21\u677f\u4ee3\u7801\uff0c\u652f\u6301HTML\u4ee3\u7801","s":"<p>\u539f\u521b\u6587\u7ae0\uff0c\u4f5c\u8005\uff1a%AUTHOR_NAME%\uff0c\u5982\u82e5\u8f6c\u8f7d\uff0c\u8bf7\u6ce8\u660e\u51fa\u5904\uff1a%POST_URL%<\/p>","t":"ta","code":""},{"n":"copyright_tougao","l":"\u6295\u7a3f\u7248\u6743","d":"\u6295\u7a3f\u6587\u7ae0\u7684\u7248\u6743\u4fe1\u606f\uff0c\u53ef\u7528\u6a21\u677f\u4ee3\u7801\uff0c\u652f\u6301HTML\u4ee3\u7801","s":"<p>\u672c\u6587\u6765\u81ea\u6295\u7a3f\uff0c\u4e0d\u4ee3\u8868%SITE_NAME%\u7acb\u573a\uff0c\u5982\u82e5\u8f6c\u8f7d\uff0c\u8bf7\u6ce8\u660e\u51fa\u5904\uff1a%POST_URL%<\/p>","t":"ta","code":""},{"t":"rp","l":"\u7248\u6743\u6a21\u677f","d":"\u6dfb\u52a0\u66f4\u591a\u7c7b\u578b\u7684\u7248\u6743\u6a21\u677f","o":[{"n":"copyright_id","l":"\u7248\u6743ID","d":"\u5fc5\u586b\uff0c\u7528\u4e8e\u8c03\u7528\u7248\u6743\u6a21\u677f\u4fe1\u606f\uff0c<b>\u53ea\u80fd\u5305\u542b\u5c0f\u5199\u5b57\u6bcd\u3001\u6570\u5b57\u3001\u4e0b\u5212\u7ebf\uff0c\u591a\u4e2a\u7248\u6743\u7c7b\u578bID\u4e0d\u80fd\u4e00\u6837\uff0c\u4e0d\u80fd\u6709\u7a7a\u683c\uff0c\u4e0d\u80fd\u4e3a\u5168\u89d2\u5b57\u7b26<\/b>","s":"type_1"},{"n":"copyright_type","l":"\u7248\u6743\u7c7b\u578b","d":"\u7248\u6743\u7684\u7c7b\u578b\uff0c\u65b9\u4fbf\u7f16\u8f91\u6587\u7ae0\u7684\u65f6\u5019\u9009\u62e9","s":"\u8f6c\u8f7d\u6587\u7ae0"},{"n":"copyright_text","l":"\u7248\u6743\u5185\u5bb9","d":"\u7248\u6743\u7684\u5177\u4f53\u663e\u793a\u5185\u5bb9\uff0c\u53ef\u7528\u6a21\u677f\u4ee3\u7801\uff0c\u652f\u6301HTML\u4ee3\u7801","s":"<p>\u672c\u6587\u6765\u81ea<span>%ORIGINAL_NAME%<\/span>\uff0c\u7ecf\u6388\u6743\u540e\u53d1\u5e03\uff0c\u672c\u6587\u89c2\u70b9\u4e0d\u4ee3\u8868%SITE_NAME%\u7acb\u573a\uff0c\u8f6c\u8f7d\u8bf7\u8054\u7cfb\u539f\u4f5c\u8005\u3002<\/p>","t":"ta","code":""}]},{"n":"show_origin","l":"\u663e\u793a\u6765\u6e90","d":"\u5f00\u542f\u6b64\u9009\u9879\u540e\u5982\u679c\u6587\u7ae0\u6709\u8bbe\u7f6e\u6765\u6e90\u5219\u4f1a\u5728\u6587\u7ae0\u9875\u6807\u9898\u4e0b\u65b9\u663e\u793a","t":"t"},{"n":"origin_title","f":"show_origin:1","l":"\u6765\u6e90\u8bf4\u660e\u6587\u6848","d":"\u6bd4\u5982\uff1a<b>\u8f6c\u8f7d\u81ea<\/b>\uff0c<b>\u6587\u7ae0\u6765\u6e90<\/b>\uff0c\u9ed8\u8ba4\u4e3a<b>\u6587\u7ae0\u6765\u6e90:<\/b>"},{"l":"\u76f8\u5173\u6587\u7ae0","d":"\u6587\u7ae0\u8be6\u60c5\u9875\u9762\u76f8\u5173\u6587\u7ae0\u8bbe\u7f6e","t":"tt"},{"n":"related_by","l":"\u5339\u914d\u65b9\u5f0f","d":"\u76f8\u5173\u6587\u7ae0\u5339\u914d\u65b9\u5f0f\uff0c\u53ef\u9009\u62e9\u6839\u636e\u5206\u7c7b\u6216\u8005\u6839\u636e\u6807\u7b7e","s":"0","t":"r","ux":1,"o":["\u6240\u5728\u7684\u5206\u7c7b","\u5305\u542b\u7684\u6807\u7b7e"]},{"n":"related_news","l":"\u663e\u793a\u6807\u9898","s":"\u76f8\u5173\u63a8\u8350"},{"n":"related_show_type","l":"\u663e\u793a\u65b9\u5f0f","d":"\u76f8\u5173\u6587\u7ae0\u7684\u663e\u793a\u65b9\u5f0f","s":"0","t":"r","ux":2,"o":{"":"\u9ed8\u8ba4\u5217\u8868||\/justnews\/list-tpl-default.png","image":"\u56fe\u6587\u5217\u8868||\/justnews\/list-tpl-image.png","card":"\u5361\u7247\u5217\u8868||\/justnews\/list-tpl-card.png","list":"\u6587\u7ae0\u5217\u8868||\/justnews\/list-tpl-list.png"}},{"n":"related_order","l":"\u6587\u7ae0\u6392\u5e8f","t":"r","s":"0","ux":1,"o":["\u968f\u673a\u6392\u5e8f","\u53d1\u5e03\u65f6\u95f4"]},{"n":"related_num","l":"\u663e\u793a\u6570\u91cf","d":"\u76f8\u5173\u6587\u7ae0\u663e\u793a\u6570\u91cf","s":"10"},{"l":"\u5206\u4eab\u8bbe\u7f6e","d":"\u5206\u4eab\u529f\u80fd\u76f8\u5173\u8bbe\u7f6e","t":"tt"},{"n":"wx_appid","l":"\u5fae\u4fe1#AppID","d":"AppID\uff0c\u7528\u4e8e\u5fae\u4fe1\u5206\u4eab\uff0c\u53ef\u5728\u5fae\u4fe1\u516c\u4f17\u5e73\u53f0\u57fa\u672c\u914d\u7f6e\u4e0b\u9762\u83b7\u53d6"},{"n":"wx_appsecret","l":"\u5fae\u4fe1#AppSecret","d":"AppSecret\uff0c\u7528\u4e8e\u5fae\u4fe1\u5206\u4eab\uff0c\u53ef\u5728\u5fae\u4fe1\u516c\u4f17\u5e73\u53f0\u57fa\u672c\u914d\u7f6e\u4e0b\u9762\u83b7\u53d6"},{"n":"wx_desc","l":"\u5fae\u4fe1#\u9ed8\u8ba4\u63cf\u8ff0","d":"\u5206\u4eab\u81ea\u52a8\u83b7\u53d6\u9875\u9762SEO\u63cf\u8ff0\u4fe1\u606f\uff0c\u5982\u679c\u83b7\u53d6\u5931\u8d25\uff0c\u5219\u4f7f\u7528\u9ed8\u8ba4\u63cf\u8ff0","t":"ta"},{"n":"mobile_share_logo","l":"\u5206\u4eab\u6d77\u62a5#\u5e95\u56fe","d":"\u751f\u6210\u5206\u4eab\u6d77\u62a5\u56fe\u7247\u65f6\u7528\u4e8e\u9875\u811a\u5e95\u56fe\uff0c\u6700\u5927\u5c3a\u5bf8\u4e3a460*80px\uff0c\u8d85\u8fc7\u5219\u7b49\u6bd4\u4f8b\u7f29\u653e","t":"at"},{"n":"google-font-local","l":"\u5206\u4eab\u6d77\u62a5#\u5b57\u4f53\u672c\u5730\u5316","d":"\u6d77\u62a5\u5185\u5bb9\u6587\u5b57\u4f7f\u7528\u7684\u662f<b>\u601d\u6e90\u9ed1\u4f53<\/b>\u514d\u8d39\u5b57\u4f53\uff0c\u57fa\u4e8e<b>\u8c37\u6b4c\u5b57\u4f53<\/b>\u670d\u52a1\u5b9e\u73b0\uff0c\u4e3a\u52a0\u5feb\u5b57\u4f53\u52a0\u8f7d\u5efa\u8bae\u52fe\u9009\u672c\u5730\u5316\u4fdd\u5b58\uff0c<b>\u9996\u6b21\u5f00\u542f\u540e\u7ba1\u7406\u5458\u8bbf\u95ee\u524d\u53f0\u53ef\u80fd\u4f1a\u6bd4\u8f83\u5361<\/b>\uff0c\u8fd9\u662f\u7531\u4e8e\u4e0b\u8f7d\u5b57\u4f53\u5230\u672c\u5730\u9700\u8981\u65f6\u95f4\uff0c\u4f46\u662f\u4e0d\u5f71\u54cd\u666e\u901a\u7528\u6237\u6b63\u5e38\u8bbf\u95ee\uff1b<b>\u5982\u679c\u5b57\u4f53\u4e0b\u8f7d\u5931\u8d25\u4f1a\u572810\u5206\u949f\u540e\u7ba1\u7406\u5458\u518d\u6b21\u8bbf\u95ee\u524d\u53f0\u9875\u9762\u91cd\u65b0\u4e0b\u8f7d\uff0c\u5931\u8d253\u6b21\u5219\u4f1a\u81ea\u52a8\u5173\u95ed\u5f53\u524d\u9009\u9879<\/b>","t":"t","s":1},{"n":"wx_thumb","l":"\u9ed8\u8ba4\u5206\u4eab\u56fe","d":"\u5206\u4eab\u56fe\u4f18\u5148\u83b7\u53d6\u6587\u7ae0\u56fe\u7247\uff0c\u5982\u6587\u7ae0\u65e0\u56fe\u7247\u5219\u4f7f\u7528\u9ed8\u8ba4\u5206\u4eab\u56fe","t":"at"},{"l":"\u5730\u56fe\u63a5\u53e3","t":"tt"},{"n":"google_map_key","l":"\u8c37\u6b4c\u5730\u56feKey","d":"\u7533\u8bf7\u5730\u5740\uff1ahttps:\/\/cloud.google.com\/maps-platform\/"},{"l":"\u9875\u9762\u53f3\u4fa7\u6d6e\u5c42\u9009\u9879","t":"tt"},{"n":"action_style","l":"\u663e\u793a\u98ce\u683c","t":"r","s":"0","ux":1,"o":["\u5c0f\u56fe\u6807","\u5927\u56fe\u6807"]},{"n":"action_cstyle","l":"\u8bbe\u7f6e\u989c\u8272","t":"t"},{"n":"action_color","l":"\u989c\u8272","f":"action_cstyle:1","t":"c"},{"n":"action_bottom","l":"\u79bb\u5e95\u90e8\u8ddd\u79bb","t":"l","units":"px, %","s":"20%"},{"n":"action_pos","l":"\u663e\u793a\u4f4d\u7f6e","t":"r","s":"0","ux":1,"o":["\u9760\u8fd1\u6d4f\u89c8\u5668","\u9760\u8fd1\u9875\u9762\u4e3b\u4f53"]},{"t":"rp","l":"\u6d6e\u5c42\u9009\u9879","o":[{"n":"action_title","l":"\u6807\u9898","d":"\u9009\u62e9\u5927\u56fe\u6807\u98ce\u683c\u65f6\u4f1a\u663e\u793a\u6807\u9898"},{"n":"action_icon","l":"\u56fe\u6807","t":"ic"},{"n":"action_type","l":"\u7c7b\u578b","t":"r","ux":1,"o":["\u94fe\u63a5","\u56fe\u7247","\u81ea\u5b9a\u4e49\u5185\u5bb9"]},{"n":"action_target","l":"\u94fe\u63a5","f":"action_type:0","t":"url"},{"n":"action_target","l":"\u56fe\u7247","f":"action_type:1","t":"u"},{"n":"action_target","l":"\u5185\u5bb9","f":"action_type:2","t":"e"}]},{"n":"share","l":"\u663e\u793a\u793e\u4ea4\u5206\u4eab","s":"1","t":"t"},{"n":"gotop","l":"\u663e\u793a\u8fd4\u56de\u9876\u90e8","s":"1","t":"t"}]},{"l":"\u9996\u9875\u8bbe\u7f6e","i":"&#xe88a;","o":[{"t":"a","s":"<div style=\"text-align:center\">\u6b64\u5904\u8bbe\u7f6e\u53ea\u5bf9\u9ed8\u8ba4\u9996\u9875\uff08\u5bf9\u5e94\u6f14\u793a\u98ce\u683c1\/\u7ecf\u5178\u98ce\u683c\uff09\u6709\u6548\uff0c\u5176\u4ed6\u98ce\u683c\u7684\u9996\u9875\uff08\u6216\u8005\u4f7f\u7528\u4e86\u81ea\u5b9a\u4e49\u6a21\u677f\u7684\u9875\u9762\uff09\u8bf7\u4f7f\u7528<b>\u53ef\u89c6\u5316\u7f16\u8f91\u5668<\/b>\u7f16\u8f91\u5bf9\u5e94\u9875\u9762\u548c\u6a21\u5757<\/div>"},{"l":"\u5e7b\u706f\u56fe\u7247","t":"tt"},{"n":"slider_from","l":"\u6587\u7ae0\u6765\u6e90","t":"r","ux":1,"s":"1","o":["\u4f7f\u7528\u6587\u7ae0\u63a8\u9001","\u624b\u52a8\u6dfb\u52a0"]},{"n":"slider_posts_num","l":"\u663e\u793a\u6570\u91cf","f":"slider_from:0","d":"\u8c03\u7528\u6587\u7ae0\u6570\u91cf","s":"5"},{"t":"rp","l":"\u5e7b\u706f\u56fe\u7247","f":"slider_from:1","o":[{"n":"slider_title","l":"\u6807\u9898"},{"n":"slider_img","l":"\u56fe\u7247","d":"\u56fe\u7247\u5c3a\u5bf8\u63a8\u8350620 * 320 px\uff08\u5982\u4e0d\u663e\u793a\u53f3\u8fb9\u63a8\u8350\u56fe\u7247\u5219\u5bbd\u5ea6\u5efa\u8bae860px\uff09","t":"u"},{"n":"slider_url","l":"\u94fe\u63a5","t":"url"}]},{"l":"\u5934\u6761\u63a8\u8350","d":"\u5e7b\u706f\u56fe\u7247\u65c1\u8fb9\u76842\u4e2a\u63a8\u8350\u4f4d\uff0c\u6700\u591a\u663e\u793a2\u4e2a","t":"tt"},{"t":"rp","l":"\u5934\u6761\u63a8\u8350","max":2,"o":[{"n":"fea_title","l":"\u6807\u9898"},{"n":"fea_img","l":"\u56fe\u7247","d":"\u56fe\u7247\u5c3a\u5bf8\u63a8\u8350226 * 153 px","t":"u"},{"n":"fea_url","l":"\u94fe\u63a5","t":"url"}]},{"l":"\u4e13\u9898","d":"\u9996\u9875\u4e13\u9898\u663e\u793a\u8bbe\u7f6e","t":"tt"},{"n":"special_home_title","l":"\u6807\u9898","d":"\u4e13\u9898\u6a21\u5757\u7684\u6807\u9898","s":"\u4e13\u9898\u4ecb\u7ecd"},{"n":"special_home_desc","l":"\u63cf\u8ff0","d":"\u4e13\u9898\u6a21\u5757\u6807\u9898\u65c1\u8fb9\u7684\u63cf\u8ff0\u6587\u5b57"},{"n":"more_special","l":"\u66f4\u591a\u4e13\u9898\u6807\u9898"},{"n":"special_home_url","t":"url","l":"\u66f4\u591a\u4e13\u9898\u94fe\u63a5","d":"\u6240\u6709\u4e13\u9898\u5217\u8868\u9875\u9762\u94fe\u63a5"},{"n":"special_home_style","l":"\u663e\u793a\u98ce\u683c","t":"r","ux":2,"o":{"1":"\u98ce\u683c1\uff08\u9ed8\u8ba4\uff09||\/justnews\/special-1.png","2":"\u98ce\u683c2||\/justnews\/special-2.png","3":"\u98ce\u683c3||\/justnews\/special-3.png"}},{"n":"special_home_num","l":"\u663e\u793a\u6570\u91cf","d":"\u663e\u793a\u4e13\u9898\u6570\uff0c\u8bbe\u7f6e0\u5219\u4e0d\u5728\u9996\u9875\u663e\u793a","s":"3"},{"n":"special_home_col","l":"\u6bcf\u884c\u663e\u793a","s":"3","t":"r","ux":1,"o":{"3":"\u6bcf\u884c3\u4e2a","4":"\u6bcf\u884c4\u4e2a"}},{"l":"\u6587\u7ae0\u5217\u8868","d":"\u9996\u9875\u6587\u7ae0\u5217\u8868\u8bbe\u7f6e","t":"tt"},{"n":"newest_exclude","l":"\u6392\u9664\u5206\u7c7b","d":"\u9996\u9875\u6700\u65b0\u6587\u7ae0\u5217\u8868\u6392\u9664\u7684\u5206\u7c7b\uff0c\u6392\u9664\u5206\u7c7b\u7684\u6587\u7ae0\u5c06\u4e0d\u663e\u793a\u5728\u6700\u65b0\u6587\u7ae0\u5217\u8868","t":"cm"},{"n":"cats_id","l":"\u663e\u793a\u5206\u7c7b","d":"\u9996\u9875\u5217\u8868\u5207\u6362\u680f\u5c55\u793a\u7684\u6587\u7ae0\u5206\u7c7b\uff0c\u6309\u9009\u62e9\u987a\u5e8f\u6392\u5e8f","t":"cms"},{"n":"latest_title","l":"\u6700\u65b0\u6587\u7ae0\u6807\u9898"},{"l":"\u5408\u4f5c\u4f19\u4f34","d":"\u9996\u9875\u5408\u4f5c\u4f19\u4f34\u6a21\u5757","t":"tt"},{"n":"partner_title","l":"\u6807\u9898","d":"\u5408\u4f5c\u4f19\u4f34\u6a21\u5757\u7684\u6807\u9898","s":"\u5408\u4f5c\u4f19\u4f34"},{"n":"partner_desc","l":"\u63cf\u8ff0","d":"\u5408\u4f5c\u4f19\u4f34\u6a21\u5757\u6807\u9898\u65c1\u8fb9\u7684\u63cf\u8ff0\u6587\u5b57"},{"n":"partner_more_title","l":"\u66f4\u591a\u6807\u9898","d":"\u66f4\u591a\u8df3\u8f6c\u94fe\u63a5\u6807\u9898","s":"\u8054\u7cfb\u6211\u4eec"},{"n":"partner_more_url","l":"\u66f4\u591a\u94fe\u63a5","t":"url","d":"\u66f4\u591a\u8df3\u8f6c\u94fe\u63a5\u5730\u5740"},{"n":"partner_img_cols","l":"\u6bcf\u884c\u663e\u793a","d":"\u6bcf\u884c\u663e\u793a\u56fe\u7247\u6570\u91cf","s":"7","options":{"3":"3\u5f20","4":"4\u5f20","5":"5\u5f20","6":"6\u5f20","7":"7\u5f20","8":"8\u5f20","9":"9\u5f20","10":"10\u5f20"}},{"t":"rp","l":"\u5408\u4f5c\u4f19\u4f34","o":[{"n":"pt_title","l":"\u6807\u9898","d":"\u9009\u586b\uff0c\u4e0d\u4f1a\u663e\u793a\uff0c\u4f1a\u4f5c\u4e3a\u56fe\u7247\u7684alt\u5c5e\u6027"},{"n":"pt_img","l":"\u56fe\u7247","d":"\u56fe\u7247\u5bbd\u5ea6\u5efa\u8bae\u548c<b>\u9996\u9875\u8bbe\u7f6e>\u5408\u4f5c\u4f19\u4f34>\u56fe\u7247\u5bbd\u5ea6<\/b>\u9009\u9879\u4e00\u81f4\uff0c\u9ad8\u5ea6\u7edf\u4e00\u5373\u53ef\u3002","t":"u"},{"n":"pt_url","l":"\u94fe\u63a5","t":"url","d":"\u9009\u586b"}]},{"l":"\u53cb\u60c5\u94fe\u63a5","d":"\u9996\u9875\u53cb\u60c5\u94fe\u63a5\u6a21\u5757","t":"tt"},{"n":"link_title","l":"\u6807\u9898","d":"\u53cb\u60c5\u94fe\u63a5\u6a21\u5757\u7684\u6807\u9898","s":"\u53cb\u60c5\u94fe\u63a5"},{"n":"link_desc","l":"\u63cf\u8ff0","d":"\u53cb\u60c5\u94fe\u63a5\u6a21\u5757\u6807\u9898\u65c1\u8fb9\u7684\u63cf\u8ff0\u6587\u5b57"},{"n":"link_more_title","l":"\u66f4\u591a\u6807\u9898","d":"\u66f4\u591a\u8df3\u8f6c\u94fe\u63a5\u6807\u9898","s":"\u7533\u8bf7\u53cb\u94fe"},{"n":"link_more_url","l":"\u66f4\u591a\u94fe\u63a5","t":"url","d":"\u66f4\u591a\u8df3\u8f6c\u94fe\u63a5\u5730\u5740"},{"n":"link_cat","l":"\u94fe\u63a5\u5206\u7c7b","d":"\u8bf7\u9009\u62e9\u94fe\u63a5\u5206\u7c7b\uff0c\u4e0d\u9009\u62e9\u5219\u663e\u793a\u6240\u6709\u516c\u5f00\u94fe\u63a5","t":"cs","tax":"link_category"},{"n":"link_list","l":"\u53cb\u60c5\u94fe\u63a5","s":"\u53cb\u60c5\u94fe\u63a5\u5728wordpress\u540e\u53f0\u7684\u201c<b>\u94fe\u63a5<\/b>\u201d\u4e0b\u9762\u6dfb\u52a0","t":"i"}]},{"l":"\u5e7f\u544a\u8bbe\u7f6e","i":"&#xe762;","o":[{"l":"\u5e7f\u544a\u8bbe\u7f6e","d":"\u7f51\u7ad9\u5e7f\u544a\u4f4d\u8bbe\u7f6e","t":"tt"},{"n":"ad_home_1","l":"\u9996\u9875\u5e7f\u544a1","d":"\u5e7b\u706f\u7247\u4e0b\u65b9\uff0c\u4e13\u9898\u4e0a\u65b9\u4f4d\u7f6e\uff0c\u5bbd\u5ea6\uff1a860px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_home_1_mobile","l":"\u9996\u9875\u5e7f\u544a1#\u79fb\u52a8\u7aef","d":"\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_home_2","l":"\u9996\u9875\u5e7f\u544a2","d":"\u4e13\u9898\u4e0b\u65b9\uff0c\u6587\u7ae0\u5217\u8868\u4e0a\u65b9\u4f4d\u7f6e\uff0c\u5bbd\u5ea6\uff1a860px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_home_2_mobile","l":"\u9996\u9875\u5e7f\u544a2#\u79fb\u52a8\u7aef","d":"\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_0","l":"\u8be6\u60c5\u9875\u6807\u9898\u524d","d":"\u5bbd\u5ea6\uff1a820px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_0_mobile","l":"\u8be6\u60c5\u9875\u6807\u9898\u524d#\u79fb\u52a8\u7aef","d":"\u5bbd\u5ea6\uff1a820px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_1","l":"\u8be6\u60c5\u98751","d":"\u6587\u7ae0\u5f00\u5934\u5904\u4f4d\u7f6e\uff0c\u5bbd\u5ea6\uff1a820px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_1_mobile","l":"\u8be6\u60c5\u98751#\u79fb\u52a8\u7aef","d":"\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_2","l":"\u8be6\u60c5\u98752","d":"\u6587\u7ae0\u672b\u5c3e\uff0c\u76f8\u5173\u6587\u7ae0\u4e0a\u65b9\u4f4d\u7f6e\uff0c\u5bbd\u5ea6\uff1a820px\uff0c\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_single_2_mobile","l":"\u8be6\u60c5\u98752#\u79fb\u52a8\u7aef","d":"\u76f4\u63a5\u586b\u5199\u5e7f\u544aHTML\u4ee3\u7801\uff0c\u56fe\u7247\u94fe\u63a5\u5e7f\u544a\u4ee3\u7801\u53c2\u8003\uff1a&lt;a href=&quot;\u5e7f\u544a\u94fe\u63a5&quot; target=&quot;_blank&quot;>&lt;img src=&quot;\u56fe\u7247\u94fe\u63a5&quot;>&lt;\/a>","t":"ta","code":""},{"n":"ad_flow","l":"\u4fe1\u606f\u6d41","d":"\u4fe1\u606f\u6d41\u90e8\u5206\u5efa\u8bae\u5bbd\u5ea6830px\uff0c\u53ea\u9488\u5bf9\u9ed8\u8ba4\u5217\u8868\u6837\u5f0f\uff0c\u987a\u5e8f\u968f\u673a","t":"ta","code":""},{"n":"ad_flow_mobile","l":"\u4fe1\u606f\u6d41#\u79fb\u52a8\u7aef","d":"\u53ea\u9488\u5bf9\u9ed8\u8ba4\u5217\u8868\u6837\u5f0f\uff0c\u987a\u5e8f\u968f\u673a","t":"ta","code":""}]},{"l":"\u7b5b\u9009\u8bbe\u7f6e","i":"&#xe94e;","o":[{"l":"\u7b5b\u9009\u8bbe\u7f6e","d":"\u5206\u7c7b\u9875\u6587\u7ae0\u591a\u91cd\u7b5b\u9009\u529f\u80fd","t":"tt"},{"t":"rp","l":"\u7b5b\u9009\u5de5\u5177","o":[{"n":"filter_item_title","l":"\u7b5b\u9009\u6807\u9898"},{"n":"filter_item_id","l":"\u7b5b\u9009ID","d":"ID\u53ea\u80fd\u5305\u542b\u5c0f\u5199\u5b57\u6bcd\u3001\u6570\u5b57\u3001\u4e0b\u5212\u7ebf\uff0c\u4ee5\u5b57\u6bcd\u5f00\u5934\uff0c\u591a\u4e2aID\u4e0d\u80fd\u91cd\u590d\uff0c\u4e0d\u80fd\u6709\u7a7a\u683c\uff0c\u4e0d\u80fd\u4e3a\u5168\u89d2\u5b57\u7b26"},{"n":"filter_item_count","l":"\u663e\u793a\u6587\u7ae0\u6570\u91cf","d":"\u5f00\u542f\u540e\u4f1a\u5728\u62ec\u53f7\u91cc\u9762\u663e\u793a\u6587\u7ae0\u6570\u91cf","t":"t"},{"n":"filter_items","l":"\u7b5b\u9009\u6761\u76ee","t":"rp","o":[{"n":"filter_item_label","l":"\u6761\u76ee\u6807\u9898"},{"n":"filter_item_type","l":"\u6761\u76ee\u7c7b\u578b","t":"r","ux":1,"o":["\u6587\u7ae0\u5206\u7c7b","\u7b5b\u9009\u5c5e\u6027","\u6392\u5e8f","\u6587\u7ae0\u5b50\u5206\u7c7b"]},{"n":"filter_item_cats","l":"\u663e\u793a\u5206\u7c7b","f":"filter_item_type:0","t":"cms","v-show":1},{"n":"filter_item_attrs","l":"\u663e\u793a\u5c5e\u6027","f":"filter_item_type:1","t":"cms","tax":"attr","v-show":1},{"n":"_filter_item","style":"info","s":"\u4ec5\u4e8c\u7ea7\u5206\u7c7b\uff0c\u4e09\u7ea7\u4ee5\u53ca\u66f4\u6df1\u5c42\u7ea7\u7684\u5206\u7c7b\u4e0d\u5c55\u793a\uff0c\u540c\u65f6\u4e3a\u4e86\u4e8c\u7ea7\u5206\u7c7b\u80fd\u6b63\u5e38\u663e\u793a\u6b64\u7b5b\u9009\u5de5\u5177\u8fd8\u9700\u8981\u5728\u5bf9\u5e94\u4e8c\u7ea7\u5206\u7c7b\u4e5f\u6dfb\u52a0\u8bbe\u7f6e\u672c\u7b5b\u9009\u5de5\u5177","f":"filter_item_type:3","t":"a"}]}]}]},{"l":"\u5546\u57ce\u529f\u80fd","i":"&#xe54c;","r":"WC","o":[{"l":"\u5546\u57ce\u529f\u80fd","d":"\u672a\u542f\u7528woocommerce\u63d2\u4ef6\u53ef\u5ffd\u7565","t":"tt"},{"n":"show_cart","l":"\u663e\u793a\u8d2d\u7269\u8f66","d":"\u662f\u5426\u5728\u9875\u9762\u5934\u90e8\u663e\u793a\u8d2d\u7269\u8f66\u56fe\u6807","s":"1","t":"t"},{"n":"shop_list_col","l":"\u6bcf\u884c\u663e\u793a","d":"\u4ea7\u54c1\u5217\u8868\u6bcf\u884c\u663e\u793a\u6570\u91cf","s":"4","t":"r","ux":1,"o":{"2":"2\u4e2a","3":"3\u4e2a","4":"4\u4e2a"}},{"n":"shop_posts_per_page","l":"\u6bcf\u9875\u663e\u793a","d":"\u4ea7\u54c1\u5217\u8868\u6bcf\u9875\u663e\u793a\u6570\u91cf","s":"12"},{"n":"shop_list_sidebar","l":"\u5217\u8868\u9875\u8fb9\u680f","d":"\u5546\u57ce\u76f8\u5173\u4ea7\u54c1\u5217\u8868\u3001\u5206\u7c7b\u5217\u8868\u662f\u5426\u663e\u793a\u8fb9\u680f","s":"0","t":"t"},{"n":"shop_single_sidebar","l":"\u8be6\u60c5\u9875\u8fb9\u680f","d":"\u5546\u57ce\u529f\u80fd\u8be6\u60c5\u9875\u662f\u5426\u663e\u793a\u8fb9\u680f","s":"0","t":"t"},{"l":"\u76f8\u5173\u4ea7\u54c1","d":"\u5546\u54c1\u8be6\u60c5\u9875\u4e0b\u65b9\u76f8\u5173\u4ea7\u54c1","t":"tt"},{"n":"related_shop","l":"\u6807\u9898","d":"\u5546\u57ce\u8be6\u60c5\u9875\u76f8\u5173\u4ea7\u54c1\u6807\u9898","s":"\u76f8\u5173\u4ea7\u54c1"},{"n":"related_col","l":"\u6bcf\u884c\u663e\u793a","d":"\u76f8\u5173\u4ea7\u54c1\u6bcf\u884c\u663e\u793a\u6570\u91cf","s":"4","t":"r","ux":1,"o":{"2":"2\u4e2a","3":"3\u4e2a","4":"4\u4e2a"}},{"n":"related_posts_per_page","l":"\u663e\u793a\u6570\u91cf","d":"\u76f8\u5173\u4ea7\u54c1\u663e\u793a\u6570\u91cf","s":"4"},{"l":"\u5e10\u53f7\u8bbe\u7f6e\u9875\u9762","t":"tt"},{"n":"shop_orders_limit","l":"\u6211\u7684\u8ba2\u5355\u6bcf\u9875\u663e\u793a","d":"\u6bcf\u9875\u663e\u793a\u7684\u8ba2\u5355\u6570\u91cf","s":"10"},{"n":"shop_downloads_limit","l":"\u6211\u7684\u4e0b\u8f7d\u6bcf\u9875\u663e\u793a","d":"\u6bcf\u9875\u663e\u793a\u7684\u4e0b\u8f7d\u6570\u91cf","s":"10"}]},{"l":"\u98ce\u683c\u6837\u5f0f","i":"&#xe41d;","o":[{"l":"\u6574\u4f53\u989c\u8272","d":"\u81ea\u5b9a\u4e49\u7f51\u7ad9\u6574\u4f53\u989c\u8272\u98ce\u683c","t":"tt"},{"n":"theme_color","l":"\u4e3b\u8272\u8c03","d":"\u7f51\u7ad9\u4e3b\u8272\u8c03\uff0c\u5305\u62ec\u80cc\u666f\u989c\u8272\u3001\u94fe\u63a5\u989c\u8272","s":"08c","t":"c"},{"n":"theme_color_hover","l":"\u60ac\u505c\u989c\u8272","d":"\u94fe\u63a5\u60ac\u505c\u989c\u8272","s":"07c","t":"c"},{"l":"\u663c\u591c\u98ce\u683c","d":"\u53ef\u5207\u6362\u7f51\u7ad9\u663c\u591c\u98ce\u683c","t":"tt"},{"n":"dark_style","l":"\u663c\u591c\u98ce\u683c","ux":1,"s":0,"t":"r","d":"\u9009\u62e9<b>\u8ddf\u968f\u7cfb\u7edf\u81ea\u52a8\u5207\u6362<\/b>\u9700\u8981\u8bbe\u5907\u652f\u6301\uff0c\u6bd4\u5982\u90e8\u5206\u624b\u673a\u3001MacBook\u7b49\u8bbe\u5907\u53ef\u652f\u6301","o":["\u65e5\u95f4\u6a21\u5f0f","\u591c\u95f4\u6a21\u5f0f","\u8ddf\u968f\u7cfb\u7edf\u81ea\u52a8\u5207\u6362"]},{"n":"dark_style_toggle","f":"dark_style:0,dark_style:1","l":"\u663c\u591c\u98ce\u683c\u5207\u6362","v-show":1,"d":"\u5f00\u542f\u98ce\u683c\u5207\u6362\u540e\u5728\u9875\u5934\u4f1a\u663e\u793a\u5207\u6362\u56fe\u6807","s":0,"t":"t"},{"n":"dark_style_logo","l":"\u591c\u95f4\u6a21\u5f0fLOGO","d":"\u4e3a\u4e86\u66f4\u597d\u7684\u9002\u914d\u4e24\u79cd\u98ce\u683c\u53ef\u5355\u72ec\u5728\u6b64\u9009\u9879\u8bbe\u7f6e\u591c\u95f4\u6a21\u5f0f\u7684LOGO\u56fe\u7247","t":"at"},{"l":"\u9875\u5934\u6837\u5f0f","d":"","t":"tt"},{"n":"header_fixed","l":"\u9875\u5934\u8ddf\u968f","s":"1","t":"t","d":"\u9875\u5934\u8ddf\u968f\u9875\u9762\u6eda\u52a8\u59cb\u7ec8\u56fa\u5b9a\u5728\u9875\u9762\u9876\u90e8"},{"n":"header_style","l":"\u9875\u5934\u6587\u5b57","s":"0","t":"r","ux":1,"options":["\u9ed1\u8272\u98ce\u683c\uff0c\u9002\u5408\u4eae\u8272\u80cc\u666f","\u767d\u8272\u98ce\u683c\uff0c\u9002\u5408\u6697\u9ed1\u80cc\u666f"]},{"n":"header_bg","l":"\u80cc\u666f\u989c\u8272","s":"","t":"c","gradient":1},{"l":"\u80cc\u666f\u8bbe\u7f6e","d":"\u7f51\u7ad9\u80cc\u666f\u8bbe\u7f6e\uff0c\u79fb\u52a8\u7aef\u5c06\u663e\u793a\u9ed8\u8ba4\u80cc\u666f","t":"tt"},{"n":"bg_color","l":"\u80cc\u666f\u989c\u8272","d":"\u53ef\u9009\uff0c\u7f51\u7ad9\u80cc\u666f\u989c\u8272\u8bbe\u7f6e\uff0c\u79fb\u52a8\u7aef\u5c06\u663e\u793a\u9ed8\u8ba4\u80cc\u666f\u989c\u8272","t":"c"},{"n":"bg_image","l":"\u80cc\u666f\u56fe\u7247","d":"\u53ef\u9009\uff0c\u7f51\u7ad9\u80cc\u666f\u56fe\u7247\u8bbe\u7f6e","t":"u"},{"t":"w","f":"bg_image:!!!","o":[{"n":"bg_image_attachment","l":"\u80cc\u666f\u56fe\u7247\u56fa\u5b9a","t":"t","d":"\u80cc\u666f\u56fe\u7247\u56fa\u5b9a\uff0c\u4e0d\u8ddf\u968f\u6eda\u52a8\uff0c\u82e5\u5f00\u542f\u5219\u9700\u8981\u786e\u4fdd\u56fe\u7247\u9ad8\u5ea6\u8db3\u591f","s":"0"},{"n":"bg_image_repeat","l":"\u80cc\u666f\u56fe\u7247\u5e73\u94fa","d":"\u7f51\u7ad9\u80cc\u666f\u56fe\u7247\u5e73\u94fa\u8bbe\u7f6e","s":"no-repeat","t":"r","ux":1,"o":{"no-repeat":"\u4e0d\u5e73\u94fa","repeat":"\u5e73\u94fa","repeat-x":"\u6c34\u5e73\u5e73\u94fa","repeat-y":"\u5782\u76f4\u5e73\u94fa"}},{"n":"bg_image_size","f":"bg_image_repeat:no-repeat,bg_image_repeat:","l":"\u80cc\u666f\u56fe\u7247\u5c3a\u5bf8","d":"\u80cc\u666f\u56fe\u7247\u663e\u793a\u5c3a\u5bf8\uff0c\u4ec5\u5bf9\u672a\u8bbe\u7f6e\u5e73\u94fa\u7684\u60c5\u51b5\u6709\u6548","s":"2","t":"r","ux":1,"o":["\u5b9e\u9645\u5c3a\u5bf8","\u5bbd\u5ea6100%\u9002\u914d","\u81ea\u9002\u5e94\u94fa\u6ee1\u53ef\u89c6\u533a\u57df"]},{"n":"bg_image_position","l":"\u80cc\u666f\u56fe\u7247\u4f4d\u7f6e","d":"\u7f51\u7ad9\u80cc\u666f\u56fe\u7247\u4f4d\u7f6e\u8bbe\u7f6e\uff0c\u5206\u522b\u4e3a\u5de6\u53f3\u5bf9\u9f50\u65b9\u5f0f\u548c\u4e0a\u4e0b\u5bf9\u9f50\u65b9\u5f0f","s":"center top","t":"r","ux":1,"o":{"left top":"\u5de6 \u4e0a","left center":"\u5de6 \u4e2d","left bottom":"\u5de6 \u4e0b","center top":"\u4e2d \u4e0a","center center":"\u4e2d \u4e2d","center bottom":"\u4e2d \u4e0b","right top":"\u53f3 \u4e0a","right center":"\u53f3 \u4e2d","right bottom":"\u53f3 \u4e0b"}}]},{"n":"special_title_color","l":"\u4e13\u9898\u5217\u8868\u6807\u9898","d":"\u5982\u679c\u8bbe\u7f6e\u4e86\u80cc\u666f\u989c\u8272\uff0c\u53ef\u80fd\u4f1a\u5bfc\u81f4\u4e0e\u4e13\u9898\u5217\u8868\u6807\u9898\u989c\u8272\u4e0d\u534f\u8c03\uff0c\u53ef\u901a\u8fc7\u6b64\u9009\u9879\u8bbe\u7f6e","s":"333","t":"c"},{"l":"\u56fe\u6807\u8bbe\u7f6e","t":"tt"},{"n":"fontawesome","l":"FontAwesome","d":"FontAwesome\u56fe\u6807\uff0c4.7\u7248\u672c\uff0c<b>\u5f00\u542f\u540e\u9700\u8981\u5237\u65b0\u8bbe\u7f6e\u9875\u9762\u624d\u53ef\u5728\u56fe\u6807\u9009\u9879\u663e\u793a<\/b>","s":"1","t":"t"},{"n":"material_icons","l":"Material Icons","d":"Google\u5f00\u6e90\u514d\u8d39\u56fe\u6807\uff0c<b>\u5f00\u542f\u540e\u9700\u8981\u5237\u65b0\u8bbe\u7f6e\u9875\u9762\u624d\u53ef\u5728\u56fe\u6807\u9009\u9879\u663e\u793a<\/b>","s":"0","t":"t"},{"n":"remixicon","l":"Remix Icon","d":"\u4e00\u5957\u4f18\u79c0\u7684\u514d\u8d39\u5f00\u6e90\u56fe\u6807\u5e93\uff0c<b>\u5f00\u542f\u540e\u9700\u8981\u5237\u65b0\u8bbe\u7f6e\u9875\u9762\u624d\u53ef\u5728\u56fe\u6807\u9009\u9879\u663e\u793a<\/b>","s":"0","t":"t"},{"n":"iconfont","l":"Iconfont","d":"Iconfont\u81ea\u5b9a\u4e49\u56fe\u6807\uff0c\u586b\u5199<b>Symbol<\/b>\u94fe\u63a5\u4ee3\u7801\uff0c<b>\u9700\u8981\u5237\u65b0\u8bbe\u7f6e\u9875\u9762\u624d\u53ef\u5728\u56fe\u6807\u9009\u9879\u663e\u793a<\/b>"},{"l":"\u5176\u4ed6\u8bbe\u7f6e","t":"tt"},{"n":"el_boxed","l":"\u5143\u7d20\u80cc\u666f\u8fb9\u6846","d":"\u9875\u9762\u5143\u7d20\u662f\u5426\u6709\u80cc\u666f\u8fb9\u6846","s":"1","t":"t"},{"n":"sticky_color","l":"\u7f6e\u9876\u6807\u9898\u989c\u8272","d":"\u53ef\u9009\uff0c\u6e10\u53d8\u6548\u679c\u90e8\u5206\u4f4e\u7248\u672c\u6d4f\u89c8\u5668\u4e0d\u517c\u5bb9\uff0c\u6bd4\u5982IE","t":"c","gradient":1},{"n":"list_img_right","l":"\u5217\u8868\u56fe\u7247\u5c45\u53f3","d":"\u9ed8\u8ba4\u6587\u7ae0\u5217\u8868\u56fe\u7247\u4f4d\u7f6e","s":"0","t":"t"},{"n":"list_multimage","l":"\u5217\u8868\u9ed8\u8ba4\u98ce\u683c","d":"\u9ed8\u8ba4\u6587\u7ae0\u5217\u8868\u7684\u9ed8\u8ba4\u5c55\u793a\u98ce\u683c","t":"s","o":{"":"\u9ed8\u8ba4\u98ce\u683c\uff08\u5355\u56fe\u98ce\u683c\uff09","1":"\u591a\u56fe\u98ce\u683c","2":"\u5927\u56fe\u98ce\u683c","3":"\u5927\u56fe\u8f6e\u64ad\u98ce\u683c"}}]},{"l":"\u8fb9\u680f\u8bbe\u7f6e","i":"&#xf114;","o":[{"l":"\u8fb9\u680f\u8bbe\u7f6e","d":"\u53ef\u4ee5\u81ea\u5b9a\u4e49\u6dfb\u52a0\u8fb9\u680f\uff0c\u7528\u4e8e\u5bf9\u5e94\u9875\u9762\u7684\u663e\u793a","t":"tt"},{"t":"rp","l":"\u8fb9\u680f","o":[{"n":"sidebar_id","l":"ID","d":"ID\u53ea\u80fd\u5305\u542b\u5c0f\u5199\u5b57\u6bcd\u3001\u6570\u5b57\u3001\u4e0b\u5212\u7ebf\uff0c\u4ee5\u5b57\u6bcd\u5f00\u5934\uff0c\u591a\u4e2a\u8fb9\u680fID\u4e0d\u80fd\u4e00\u6837\uff0c\u4e0d\u80fd\u6709\u7a7a\u683c\uff0c\u4e0d\u80fd\u4e3a\u5168\u89d2\u5b57\u7b26","s":"sidebar_1"},{"n":"sidebar_name","l":"\u6807\u9898","d":"\u8fb9\u680f\u6807\u9898\uff0c\u7528\u4e8e\u9009\u62e9\u7684\u65f6\u5019\u8bc6\u522b","s":"\u8fb9\u680f"}]}]},{"l":"\u9875\u811a\u8bbe\u7f6e","i":"&#xe90c;","o":[{"l":"\u9875\u811aLOGO","d":"\u9875\u811a\u5de6\u8fb9\u7684LOGO","t":"tt"},{"n":"footer_logo","l":"\u9875\u811aLOGO","d":"\u9875\u811a\u5de6\u8fb9\u7684LOGO\u56fe\u7247\uff0c\u53ef\u9009\u9879\uff0c\u4e0d\u8bbe\u7f6e\u5219\u4e0d\u663e\u793a","t":"u"},{"l":"\u56fe\u6807\u8bbe\u7f6e","d":"\u9875\u9762\u5e95\u90e8\u56fe\u6807\u8bbe\u7f6e","t":"tt"},{"t":"rp","l":"\u9875\u811a\u56fe\u6807","o":[{"n":"fticon_i","l":"\u56fe\u6807","t":"ic"},{"n":"fticon_t","l":"\u7c7b\u578b","t":"r","ux":1,"o":["\u94fe\u63a5","\u4e8c\u7ef4\u7801"]},{"n":"fticon_u","l":"\u94fe\u63a5","f":"fticon_t:0","t":"url"},{"n":"fticon_u","l":"\u4e8c\u7ef4\u7801","f":"fticon_t:1","t":"u"}]},{"l":"\u7248\u6743\u4fe1\u606f","d":"\u9875\u811a\u7248\u6743\/\u5907\u6848\u4fe1\u606f","t":"tt"},{"n":"copyright","l":"\u7248\u6743\u4fe1\u606f","s":"Copyright \u00a9 2022 WPCOM \u7248\u6743\u6240\u6709 <a rel=\"nofollow\" href=\"http:\/\/www.beian.miit.gov.cn\" target=\"_blank\">\u7ca4ICP\u5907000000000\u53f7<\/a> Powered by <a href=\"https:\/\/www.wpcom.cn\" target=\"_blank\">WordPress<\/a>","t":"e"},{"l":"\u624b\u673a\u7aef\u5e95\u90e8\u83dc\u5355","d":"\u5efa\u8bae\u4e0d\u8d85\u8fc76\u4e2a\u94fe\u63a5\uff0c\u5177\u4f53\u4ee5\u5b9e\u9645\u6548\u679c\u4e3a\u51c6","t":"tt"},{"t":"rp","l":"\u83dc\u5355\u9009\u9879","o":[{"n":"footer_bar_title","l":"\u6807\u9898"},{"n":"footer_bar_icon","l":"\u56fe\u6807","img":1,"t":"ic"},{"n":"footer_bar_type","l":"\u7c7b\u578b","s":"0","o":["\u94fe\u63a5","\u4e8c\u7ef4\u7801","\u590d\u5236"],"t":"r","ux":1},{"n":"footer_bar_url","l":"\u94fe\u63a5","f":"footer_bar_type:0","d":"\u94fe\u63a5\u53ef\u4f7f\u7528tel\u3001mailto\u7b49\u534f\u8bae","t":"url"},{"n":"footer_bar_url","l":"\u4e8c\u7ef4\u7801","f":"footer_bar_type:1","t":"u"},{"n":"footer_bar_url","l":"\u590d\u5236\u5185\u5bb9","f":"footer_bar_type:2"},{"n":"footer_bar_bg","l":"\u80cc\u666f\u989c\u8272","t":"c"},{"n":"footer_bar_color","l":"\u6587\u5b57\u989c\u8272","t":"c"}]}]},{"l":"\u7528\u6237\u4e2d\u5fc3","i":"&#xe7fd;","r":"WPCOM_Member","o":[]},{"l":"SEO\u8bbe\u7f6e","i":"&#xe4fc;","o":[{"l":"SEO\u8bbe\u7f6e","d":"\u641c\u7d22\u5f15\u64ce\u4f18\u5316","t":"tt"},{"n":"seo","l":"\u5f00\u542fSEO","d":"\u5982\u679c\u4f7f\u7528\u7b2c\u4e09\u65b9SEO\u63d2\u4ef6\u53ef\u4ee5\u9009\u62e9\u5173\u95ed\u4e3b\u9898\u81ea\u5e26SEO\u529f\u80fd","s":"1","t":"t"},{"n":"open_graph","l":"\u5f00\u542fOpen Graph\u534f\u8bae","f":"seo:1","s":"1","t":"t"},{"n":"canonical","l":"\u5f00\u542fCanonical","d":"\u7528\u6765\u89e3\u51b3\u7531\u4e8e\u7f51\u5740\u5f62\u5f0f\u4e0d\u540c\u5185\u5bb9\u76f8\u540c\u800c\u9020\u6210\u7684\u5185\u5bb9\u91cd\u590d\u95ee\u9898","s":"1","t":"t"},{"n":"noindex_attachment","l":"\u9644\u4ef6\u9875\u7981\u6b62\u7d22\u5f15","d":"\u53ef\u6dfb\u52a0meta\u6807\u7b7e\u58f0\u660e\u7981\u6b62\u7d22\u5f15\u56fe\u7247\u7b49\u9644\u4ef6\u9875","s":"1","t":"t"},{"l":"\u9996\u9875SEO\u8bbe\u7f6e","d":"\u9996\u9875SEO\u4fe1\u606f\u8bbe\u7f6e","t":"tt"},{"n":"home-title","l":"\u6807\u9898","d":"\u9996\u9875SEO\u6807\u9898\uff0c\u4e0d\u8bbe\u7f6e\u9ed8\u8ba4\u663e\u793a[\u8bbe\u7f6e]\u91cc\u9762\u7684\u7ad9\u70b9\u6807\u9898\u548c\u63cf\u8ff0"},{"n":"keywords","l":"\u5173\u952e\u8bcd","d":"\u9017\u53f7\u9694\u5f00"},{"n":"description","l":"\u63cf\u8ff0","d":"\u63cf\u8ff0\u5185\u5bb9","r":5,"t":"ta"},{"l":"\u6807\u9898\u5206\u9694\u7b26","d":"\u6807\u9898\u5206\u9694\u7b26\u8bbe\u7f6e","t":"tt"},{"n":"title_sep_home","l":"\u9996\u9875","d":"\u9996\u9875\u6d4f\u89c8\u5668\u6807\u9898\u680f\u5206\u9694\u7b26\uff0c\u7528\u6765\u5206\u9694\u7f51\u7ad9\u540d\u548c\u63cf\u8ff0","s":" | "},{"n":"title_sep","l":"\u5176\u4ed6\u9875\u9762","d":"\u6d4f\u89c8\u5668\u6807\u9898\u680f\u5206\u9694\u7b26","s":" | "},{"l":"\u767e\u5ea6\u5185\u5bb9\u63d0\u4ea4","t":"tt"},{"n":"zz-submit","l":"\u666e\u901a\u6536\u5f55\u63d0\u4ea4","d":"\u586b\u5199\u4e3b\u52a8\u63a8\u9001(\u5b9e\u65f6)\u63a5\u53e3\u8c03\u7528\u5730\u5740"},{"n":"ks-submit","l":"\u5feb\u901f\u6536\u5f55\u63d0\u4ea4","d":"\u586b\u5199\u63a8\u9001\u63a5\u53e3\u8c03\u7528\u5730\u5740"},{"l":"\u5173\u952e\u8bcd\u94fe\u63a5","d":"\u6587\u7ae0\u8be6\u60c5\u9875\u81ea\u52a8\u5173\u952e\u8bcd\u94fe\u63a5","t":"tt"},{"n":"auto_tag_link","l":"\u6807\u7b7e\u5185\u94fe","d":"\u4e3a\u6587\u7ae0\u5185\u5bb9\u7684\u6807\u7b7e\u81ea\u52a8\u6dfb\u52a0\u6807\u7b7e\u9875\u9762\u94fe\u63a5","s":"0","t":"t"},{"t":"rp","l":"\u5173\u952e\u8bcd","o":[{"n":"kl_keyword","l":"\u5173\u952e\u8bcd"},{"n":"kl_link","l":"\u94fe\u63a5\u5730\u5740","t":"url"},{"n":"kl_title","l":"\u8bf4\u660e","d":"\u53ef\u9009\uff0c\u4f1a\u51fa\u73b0\u5728\u94fe\u63a5title\u5c5e\u6027"}]}]},{"l":"\u4f18\u5316\u52a0\u901f","i":"&#xeb9b;","o":[{"l":"\u5e38\u89c4\u4f18\u5316","d":"WordPress\u5e38\u89c4\u4f18\u5316","t":"tt"},{"n":"disable_emoji","l":"\u7981\u7528Emoji\u8868\u60c5","d":"Emoji\u8868\u60c5\u4e3awordpress\u9ed8\u8ba4\u8868\u60c5\u529f\u80fd\uff0c\u4f1a\u5728\u9875\u9762\u52a0\u8f7d\u9759\u6001\u8d44\u6e90\uff0c\u5efa\u8bae\u7981\u7528","s":"1","t":"t"},{"n":"disable_rest","l":"\u79fb\u9664\u9875\u5934REST API\u8f93\u51fa","d":"\u53ef\u79fb\u9664\u9875\u5934REST API\u76f8\u5173\u94fe\u63a5\u7684\u8f93\u51fa","s":"1","t":"t"},{"n":"classic_widgets","l":"\u7ecf\u5178\u5c0f\u5de5\u5177","d":"\u5f00\u542f\u540e\u53ef\u5c06\u533a\u5757\u5c0f\u5de5\u5177\u7f16\u8f91\u6a21\u5f0f\u5207\u6362\u56de\u7ecf\u5178\u5c0f\u5de5\u5177\u6a21\u5f0f","s":"1","t":"t"},{"n":"thumb_img_lazyload","l":"\u7f29\u7565\u56fe\u5ef6\u8fdf\u52a0\u8f7d","d":"\u4f18\u5316\u5217\u8868\u7f29\u7565\u56fe\u7247\u52a0\u8f7d\u4f53\u9a8c\uff0c\u6539\u4e3a\u5ef6\u8fdf\u52a0\u8f7d\uff0c\u53ef\u63d0\u9ad8\u7f51\u9875\u52a0\u8f7d\u901f\u5ea6","s":"1","t":"t"},{"n":"lazyload_img","l":"\u5ef6\u8fdf\u52a0\u8f7d\u66ff\u4ee3\u56fe","d":"\u7f29\u7565\u56fe\u52a0\u8f7d\u524d\u663e\u793a\u7684\u66ff\u4ee3\u56fe\u7247\uff0c\u63a8\u8350\u5c3a\u5bf8\uff1a480 * 300 px","t":"at"},{"n":"webp_suffix","l":"webp\u540e\u7f00","d":"webp\u62e5\u6709\u66f4\u5c0f\u7684\u56fe\u7247\u4f53\u79ef\uff0c\u5927\u5927\u63d0\u9ad8\u52a0\u8f7d\u901f\u5ea6\uff0c\u8282\u7701\u5e26\u5bbd\u6d41\u91cf\u3002\u4e3b\u9898\u53ef\u81ea\u52a8\u8bc6\u522b\u6d4f\u89c8\u5668\u5bf9webp\u7684\u517c\u5bb9\u6027\u52a0\u8f7dwebp\u56fe\u7247\uff0c<b>\u6b64\u529f\u80fd\u9700\u5f00\u542f\u56fe\u7247\u5ef6\u8fdf\u52a0\u8f7d\uff0c\u5e76\u914d\u5408\u7b2c\u4e09\u65b9CDN\/\u4e91\u50a8\u5b58\u4f7f\u7528<\/b>\uff0c\u4f8b\u5982\u4e03\u725b\u3001\u817e\u8baf\u4e91cos\u53ef\u586b\u5199\uff1a?imageMogr2\/format\/webp\uff1b\u963f\u91cc\u4e91oss\uff1a?x-oss-process=image\/format,webp\uff1b\u53c8\u62cd\u4e91\uff1a!\/format\/webp"},{"n":"file_upload_rename","l":"\u4e0a\u4f20\u6587\u4ef6\u91cd\u547d\u540d","d":"\u53ef\u81ea\u52a8\u4f18\u5316\u4e0a\u4f20\u6587\u4ef6\u7684\u6587\u4ef6\u540d","s":"1","t":"r","ux":1,"o":["\u4e0d\u542f\u7528","\u4ec5\u5bf9\u4e2d\u6587\u7b49\u4e0d\u7b26\u8981\u6c42\u7684\u6587\u4ef6\u540d","\u5168\u90e8\u91cd\u547d\u540d"]},{"n":"tag_cloud_num","l":"\u6807\u7b7e\u4e91\u6570\u91cf","d":"\u8fb9\u680f\u5c0f\u5de5\u5177\u6807\u7b7e\u4e91\u7684\u663e\u793a\u6807\u7b7e\u6570\u91cf\u63a7\u5236","s":"30"},{"n":"enable_cache","l":"\u4e3b\u9898\u7f13\u5b58","d":"\u53ef\u914d\u5408Object\u5bf9\u8c61\u7f13\u5b58\u5bf9\u6587\u7ae0\u67e5\u8be2\u3001\u81ea\u5b9a\u4e49\u9875\u9762\u6a21\u5757\u548c\u4e3b\u9898\u5185\u7f6e\u8fb9\u680f\u5c0f\u5de5\u5177\u5f00\u542f\u7f13\u5b58","s":1,"t":"t"},{"n":"css_cache","l":"\u4e3b\u9898CSS\u7f13\u5b58\u5408\u5e76","d":"\u53ef\u907f\u514d\u9875\u9762head\u76f4\u63a5\u8f93\u51fa\u81ea\u5b9a\u4e49css\u6837\u5f0f\u4ee5\u53ca\u4e3b\u9898\u66f4\u65b0\u540e\u7684css\u7f13\u5b58\u95ee\u9898\uff0c\u63a8\u8350\u5f00\u542f\uff0c\u5982\u5f00\u542f\u540e\u9875\u9762\u663e\u793a\u5f02\u5e38\u53ef\u5173\u95ed","s":1,"t":"t"},{"l":"\u6587\u7ae0\u4f18\u5316","d":"\u6587\u7ae0\u8be6\u60c5\u9875\u4f18\u5316","t":"tt"},{"n":"auto_featured_image","l":"\u81ea\u52a8\u7279\u8272\u56fe\u7247","d":"\u5982\u679c\u6587\u7ae0\u65e0\u7279\u8272\u56fe\u7247\u5219\u81ea\u52a8\u5c06\u7b2c\u4e00\u5f20\u56fe\u7247\u8bbe\u7f6e\u4e3a\u7279\u8272\u56fe\u7247\uff0c\u5982\u679c\u4e3a\u5916\u94fe\u56fe\u7247\u5219\u81ea\u52a8\u4fdd\u5b58\u5230\u672c\u5730","s":"0","t":"t"},{"n":"auto_get_thumb","f":"auto_featured_image:0","l":"\u81ea\u52a8\u63d0\u53d6\u7f29\u7565\u56fe","d":"\u5bf9\u4e8e\u672a\u8bbe\u7f6e\u7279\u8272\u56fe\u7247\u7684\u6587\u7ae0\u4f1a\u81ea\u52a8\u5339\u914d\u7b2c\u4e00\u5f20\u672c\u5730\u56fe\u7247\u7528\u4e8e\u5217\u8868\u7f29\u7565\u56fe\u663e\u793a\uff0c\u6b64\u9009\u9879\u548c\u81ea\u52a8\u7279\u8272\u56fe\u7247\u9009\u9879\u7684\u533a\u522b\u5728\u4e8e\u4ec5\u5339\u914d\u7b2c\u4e00\u5f20\u672c\u5730\u56fe\u7247\uff0c\u5982\u679c\u6587\u7ae0\u90fd\u662f\u5916\u94fe\u56fe\u7247\u5219\u4f1a\u8df3\u8fc7\uff0c\u5e76\u4e14\u4e0d\u4fdd\u5b58\u5230\u6570\u636e\u5e93\uff0c\u9700\u8981\u6bcf\u6b21\u63d0\u53d6","s":"1","t":"t"},{"n":"post_img_lazyload","l":"\u56fe\u7247\u5ef6\u8fdf\u52a0\u8f7d","d":"\u4f18\u5316\u6587\u7ae0\u9875\u9762\u56fe\u7247\u52a0\u8f7d\u4f53\u9a8c\uff0c\u6539\u4e3a\u5ef6\u8fdf\u52a0\u8f7d\uff0c\u53ef\u63d0\u9ad8\u7f51\u9875\u52a0\u8f7d\u901f\u5ea6","s":"1","t":"t"},{"n":"post_img_lightbox","l":"\u56fe\u7247Lightbox","d":"\u4e3a\u56fe\u7247\u589e\u52a0Lightbox\u706f\u7bb1\u6548\u679c","s":"1","t":"t"},{"n":"post_img_alt","l":"\u56fe\u7247alt\u8865\u5168","d":"\u6587\u7ae0\u65e0alt\u5c5e\u6027\u7684\u56fe\u7247\u4f7f\u7528\u6587\u7ae0\u6807\u9898\u81ea\u52a8\u8865\u5168","s":"1","t":"t"},{"n":"save_remote_img","l":"\u4fdd\u5b58\u8fdc\u7a0b\u56fe\u7247","d":"\u53d1\u5e03\u6587\u7ae0\u65f6\u81ea\u52a8\u5c06\u6587\u7ae0\u91cc\u9762\u7684\u8fdc\u7a0b\u56fe\u7247\u4fdd\u5b58\u81f3\u672c\u5730\uff0c\u5e76\u81ea\u52a8\u66ff\u6362\u6587\u7ae0\u4e2d\u7684\u56fe\u7247\u94fe\u63a5","s":"0","t":"t"},{"n":"remote_img_except","l":"\u8fdc\u7a0b\u56fe\u7247\u767d\u540d\u5355","d":"\u586b\u5199\u9700\u8981\u6392\u9664\u4fdd\u5b58\u7684\u57df\u540d\uff0c\u4f8b\u5982CDN\u57df\u540d\uff0c\u591a\u4e2a\u4ee5\u6362\u884c\u5206\u9694","t":"ta"},{"l":"\u4ee3\u7406\u52a0\u901f","t":"tt"},{"n":"wp-proxy","l":"WP\u4ee3\u7406\u52a0\u901f","d":"\u52a0\u901f\u6e90\u8282\u70b9\u7531WP-China-Yes\u63d0\u4f9b\uff0c\u53ef\u89e3\u51b3\u56fd\u5185WordPress\u66f4\u65b0\u5347\u7ea7\u5931\u8d25\/429\u7684\u95ee\u9898","s":"0","t":"t"},{"n":"wafc_gravatar","l":"\u5934\u50cf\u56fe\u7247","d":"\u7cfb\u7edf\u9ed8\u8ba4\u5934\u50cf\u4f7f\u7528\u7684Gravatar\uff0c\u76ee\u524d\u5efa\u8bae\u9009\u62e9<a href=\"https:\/\/cravatar.cn\/\" target=\"_blank\">Cravatar<\/a>","s":"3","t":"r","ux":1,"o":{"-1":"\u4e0d\u52a0\u901f","1":"https\u8bbf\u95ee","2":"CN\u5b50\u57df\u540d","3":"Cravatar","4":"\u6781\u5ba2\u65cf"}}]},{"l":"\u63d2\u5165\u4ee3\u7801","i":"&#xe86f;","o":[{"n":"head_code","l":"\u9875\u5934\u4ee3\u7801","d":"\u6dfb\u52a0\u5728\u9875\u9762<b>&lt;\/head><\/b>\u524d\uff0c\u6bd4\u5982\uff1a\u7ad9\u957f\u5e73\u53f0HTML\u9a8c\u8bc1\u4ee3\u7801\uff0c\u8c37\u6b4c\u5206\u6790\u4ee3\u7801","t":"ta","code":""},{"n":"footer_code","l":"\u9875\u811a\u4ee3\u7801","d":"\u6dfb\u52a0\u5728\u9875\u9762<b>&lt;\/body><\/b>\u524d\uff0c\u6bd4\u5982\uff1a\u7edf\u8ba1\u4ee3\u7801\u3001\u5ba2\u670d\u5de5\u5177\u7b49js\u4ee3\u7801","t":"ta","code":""},{"n":"custom_css","l":"\u81ea\u5b9a\u4e49CSS","t":"ta","code":"css"}]},{"l":"\u4e3b\u9898\u4fe1\u606f","i":"&#xe88f;","domain":"'.$home['host'].'","version":"6.12.3","o":[{"l":"\u4e3b\u9898\u4fe1\u606f","d":"\u4e3b\u9898\u76f8\u5173\u4fe1\u606f","t":"tt"},{"l":"\u5f53\u524d\u7248\u672c"},{"n":"auto_check_update","l":"\u68c0\u67e5\u66f4\u65b0","d":"\u542f\u7528\u540e\u7cfb\u7edf\u5c06\u5b9a\u671f\u68c0\u67e5\u4e3b\u9898\u66f4\u65b0\u4fe1\u606f","s":"1","t":"t"}]}],"post":[{"l":"\u6587\u7ae0\u8bbe\u7f6e","o":[{"n":"multimage","l":"\u5217\u8868\u98ce\u683c","d":"\u9ed8\u8ba4\u5217\u8868\u6a21\u677f\u6587\u7ae0\u663e\u793a\u7684\u98ce\u683c\uff0c\u9ed8\u8ba4\u98ce\u683c\u53ef\u4ee5\u5728<b>\u4e3b\u9898\u8bbe\u7f6e>\u98ce\u683c\u6837\u5f0f>\u5217\u8868\u9ed8\u8ba4\u98ce\u683c<\/b>\u8bbe\u7f6e","t":"s","o":{"":"\u9ed8\u8ba4\u98ce\u683c","0":"\u5355\u56fe\u98ce\u683c","1":"\u591a\u56fe\u98ce\u683c","2":"\u5927\u56fe\u98ce\u683c","3":"\u5927\u56fe\u8f6e\u64ad\u98ce\u683c"}},{"n":"video","l":"\u89c6\u9891\u4ee3\u7801\/\u5730\u5740","d":"\u53ef\u9009\uff0c\u53ef\u4ee5\u76f4\u63a5\u4e0a\u4f20MP4\u89c6\u9891\uff0c\u4e5f\u53ef\u4ee5\u586b\u5199MP4\u89c6\u9891\u5730\u5740\uff0c\u6216\u8005\u4f7f\u7528\u7b2c\u4e09\u65b9\u89c6\u9891\u5206\u4eab\u4ee3\u7801","t":"u"},{"n":"copyright_type","l":"\u7248\u6743\u7c7b\u578b","d":"\u6587\u7ae0\u7248\u6743\u7c7b\u578b\uff0c\u53ef\u4ee5\u5728\u4e3b\u9898\u8bbe\u7f6e\u91cc\u9762\u65b0\u589e\u548c\u7f16\u8f91\u7248\u6743\u7c7b\u578b","id_key":"copyright_id","value_key":"copyright_type","o":{"0":"\u9ed8\u8ba4\u7248\u6743","copyright_tougao":"\u6295\u7a3f\u7248\u6743"},"t":"ts"},{"n":"original_name","l":"\u539f\u6587\u51fa\u5904","d":"\u6587\u7ae0\u539f\u6587\u51fa\u5904\uff0c\u7528\u4e8e\u7248\u6743\u4fe1\u606f\u7684\u663e\u793a\uff0c\u82e5\u6ca1\u7528\u5230\u6b64\u9009\u9879\u8bf7\u7559\u7a7a"},{"n":"original_url","l":"\u539f\u6587\u94fe\u63a5","d":"\u6587\u7ae0\u539f\u6587\u51fa\u5904\u94fe\u63a5\u5730\u5740\uff0c\u7528\u4e8e\u7248\u6743\u4fe1\u606f\u7684\u663e\u793a\uff0c\u82e5\u6ca1\u7528\u5230\u6b64\u9009\u9879\u8bf7\u7559\u7a7a"},{"n":"_show_as_slide","l":"\u63a8\u9001\u5230\u5e7b\u706f\u7247","d":"\u53ef\u7528\u4e8e\u652f\u6301\u63a8\u9001\u7684\u6a21\u5757\u8c03\u7528\uff0c\u9700\u8981\u7ed9\u672c\u6587\u8bbe\u7f6e\u7279\u8272\u56fe\u7247\uff0c\u5982\u6a21\u5757\u65e0\u7279\u6b8a\u8bf4\u660e\u5c06\u8c03\u7528\u7279\u8272\u56fe\u7247\u539f\u56fe\uff0c\u8bf7\u786e\u4fdd\u8c03\u7528\u7684\u6bcf\u7bc7\u6587\u7ae0\u7279\u8272\u56fe\u7247\u5c3a\u5bf8\u6bd4\u4f8b\u4e00\u81f4","t":"t"},{"n":"sidebar","l":"\u6587\u7ae0\u8fb9\u680f","d":"\u9009\u62e9\u8981\u663e\u793a\u7684\u6587\u7ae0\u8fb9\u680f","id_key":"sidebar_id","value_key":"sidebar_name","o":{"":"\u9ed8\u8ba4\u8fb9\u680f","0":"\u4e0d\u663e\u793a\u8fb9\u680f"},"t":"ts"}]}],"page":{"l":"\u9875\u9762\u8bbe\u7f6e","o":[{"n":"sidebar","l":"\u9875\u9762\u8fb9\u680f","d":"\u9009\u62e9\u8981\u663e\u793a\u7684\u9875\u9762\u8fb9\u680f","id_key":"sidebar_id","value_key":"sidebar_name","o":{"":"\u9ed8\u8ba4\u8fb9\u680f","0":"\u4e0d\u663e\u793a\u8fb9\u680f"},"t":"ts"},{"l":"Banner\u56fe\u7247","t":"u","n":"banner","d":"\u53ef\u9009\uff0c\u8bbe\u7f6e\u540e\u6807\u9898\u3001\u63cf\u8ff0\u5c06\u663e\u793a\u5728banner\u56fe\u7247\u4e2d\u95f4\uff0c\u63a8\u8350\u5bbd\u5ea61920px\uff0c\u9ad8\u5ea6\u4e3a\u4e0b\u9762\u9009\u9879\u8bbe\u7f6e\u7684\u9ad8\u5ea6"},{"l":"Banner\u9ad8\u5ea6","f":"banner:!!!","n":"banner_height","t":"l","d":"banner\u533a\u57df\u9ad8\u5ea6\uff0c\u9ed8\u8ba4300px"},{"l":"Banner\u6587\u5b57\u989c\u8272","f":"banner:!!!","t":"s","n":"text_color","d":"banner\u56fe\u7247\u4e0a\u7684\u6807\u9898\u3001\u63cf\u8ff0\u6587\u5b57\u989c\u8272","o":["\u9ed1\u8272","\u767d\u8272"]}]},"taxonomy":{"category,post_tag,special":[{"l":"\u5217\u8868\u6a21\u677f","t":"r","ux":2,"n":"tpl","o":{"":"\u9ed8\u8ba4\u5217\u8868||\/justnews\/list-tpl-default.png","image":"\u56fe\u6587\u5217\u8868||\/justnews\/list-tpl-image.png","card":"\u5361\u7247\u5217\u8868||\/justnews\/list-tpl-card.png","masonry":"\u7011\u5e03\u6d41||\/justnews\/list-tpl-masonry.png","list":"\u6587\u7ae0\u5217\u8868||\/justnews\/list-tpl-list.png"}},{"l":"\u6bcf\u884c\u663e\u793a","t":"r","ux":1,"n":"cols","d":"\u9488\u5bf9\u56fe\u6587\u5217\u8868\u6a21\u677f","f":"tpl:image,tpl:card,tpl:masonry","options":{"2":"2\u4e2a","3":"3\u4e2a","4":"4\u4e2a","5":"5\u4e2a"}},{"l":"\u9690\u85cf\u65f6\u95f4","t":"t","n":"hide_date","f":"tpl:list"},{"l":"\u5206\u9875\u65b9\u5f0f","t":"r","ux":1,"n":"pagenavi","s":"0","options":["\u9ed8\u8ba4\u9875\u7801\u5206\u9875","\u70b9\u51fb\u52a0\u8f7d\u66f4\u591a","\u6eda\u52a8\u5230\u5e95\u90e8\u52a0\u8f7d\u66f4\u591a"]}],"special":[{"l":"\u4e13\u9898\u56fe\u7247","t":"u","n":"thumb","d":"\u4e13\u9898\u56fe\u7247\uff0c\u5c3a\u5bf8 400 * 250 px\uff0c\u6216\u7b49\u6bd4\u4f8b\u56fe\u7247"}],"category,special,post_tag,product_cat":[{"l":"Banner\u56fe\u7247","t":"u","n":"banner","d":"\u53ef\u9009\uff0c\u8bbe\u7f6e\u540e\u6807\u9898\u3001\u63cf\u8ff0\u5c06\u663e\u793a\u5728banner\u56fe\u7247\u4e2d\u95f4\uff0c\u63a8\u8350\u5bbd\u5ea61920px\uff0c\u9ad8\u5ea6\u4e3a\u3010Banner\u9ad8\u5ea6\u3011\u9009\u9879\u8bbe\u7f6e\u7684\u9ad8\u5ea6"},{"l":"Banner\u9ad8\u5ea6","f":"banner:!!!","n":"banner_height","t":"l","d":"banner\u533a\u57df\u9ad8\u5ea6\uff0c\u9ed8\u8ba4300px"},{"l":"Banner\u6587\u5b57\u989c\u8272","f":"banner:!!!","t":"r","ux":1,"n":"text_color","d":"banner\u56fe\u7247\u4e0a\u7684\u6807\u9898\u3001\u63cf\u8ff0\u6587\u5b57\u989c\u8272","o":["\u9ed1\u8272","\u767d\u8272"]}],"category":[{"l":"\u7b5b\u9009\u5de5\u5177","t":"ts","n":"filter_tool","id_key":"filter_item_id","value_key":"filter_item_title","o":[]}]}}',
//            'my_config' => $my_options,
            'my_host' => $home['host']

        );
        $res = apply_filters( 'wpcom_theme_panel_options', $res );
        $settings = $this->_get_extras();
        if(isset($settings->requires) && $settings->requires){
            $res['requires'] = array();
            foreach ($settings->requires as $req){
                $res['requires'][$req] = !!(function_exists($req) || class_exists($req));
            }
        }

        return wp_json_encode($res);
    }

    public function check_update($value){
        if ($value && empty( $value->checked ) )
            return $value;

        if ( !current_user_can('update_themes' ) )
            return $value;

        if ( !$this->automaticCheckDone ) {
            $body = array('email' => get_option('izt_theme_email'), 'token' => get_option('izt_theme_token'), 'version' => THEME_VERSION, 'home' => get_site_url(), 'themer' => FRAMEWORK_VERSION);
            $req = $this->send_request('notify', $body);
            $this->automaticCheckDone = true;

            $this->theme_update();
        }

        if ( !$value ) { // 手动点击更新
            $last_update = get_site_transient( 'update_themes' );
            if ( ! is_object($last_update) ) $last_update = new stdClass;
            if ( !isset($last_update->checked) || !$last_update->checked ) {
                $installed_themes = wp_get_themes();
                $checked = array();
                foreach ( $installed_themes as $theme ) {
                    $checked[ $theme->get_stylesheet() ] = $theme->get('Version');
                }
                $last_update->checked = $checked;
                if(!isset($last_update->last_checked)) $last_update->last_checked = time();
            }

            return set_site_transient( 'update_themes', $last_update, 3 * HOUR_IN_SECONDS );
        }

        global $theme_update_state;
        if(!isset($theme_update_state)) $theme_update_state = get_option($this->updateName);

        if ( !empty($theme_update_state) && isset($theme_update_state->update) && !empty($theme_update_state->update) ){
            $update = $theme_update_state->update;
            if($update->version && version_compare($update->version, THEME_VERSION) > 0){
                $value->response[$this->get_current_theme()] = array(
                    'new_version' => $update->version,
                    'url' => $update->url,
                    'package' => $update->package
                );
            }else{
                $this->update_option($this->updateName, '');
            }
        }

        return $value;
    }

    public function updated(){
        $this->update_option($this->updateName, '');
        $this->theme_update();
    }

    private function get_current_theme( $name=false ){
        $theme = wp_get_theme();
        if($theme->get('Template')){
            return $name ? $theme->parent()->get('Name') : $theme->template;
        }else{
            return $name ? $theme->get('Name') : $theme->stylesheet;
        }
    }

    public function options_key(){
        $key = 'izt_theme_options';
        // 兼容WPML插件
        if(function_exists('icl_get_default_language')){
            $default = icl_get_default_language();
            $current = icl_get_current_language();
            if($default!=$current && $current){ // 非默认语言
                $key = $key . '_' . $current;
            }
        }
        return $key;
    }

    public function options_filter($pre_option, $option){
        global $wpdb;
        $alloptions = wp_load_alloptions();
        if ( isset( $alloptions[ $option ] ) ) {
            $value = $alloptions[ $option ];
        } else {
            $value = wp_cache_get( $option, 'options' );
            if ( false === $value ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
                if ( is_object( $row ) ) {
                    $value = $row->option_value;
                    wp_cache_add( $option, $value, 'options' );
                }
            }
        }
        $value = maybe_unserialize( $value );
        if(is_string($value)) $value = json_decode($value, true);

        // 对应语言没有设置信息，则继承默认语言的设置信息
        if(!$value && $option!=='izt_theme_options'){
            $value = $this->options_filter($value, 'izt_theme_options');
            if(version_compare(PHP_VERSION,'5.4.0','<')){
                $o = wp_json_encode($value);
            }else{
                $o = wp_json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
            $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $o, 'yes' ) );
        }
        return apply_filters( "option_{$option}", $value, $option );
    }

    public function theme_options_demo_export(){
        if(current_user_can( 'edit_theme_options' )){
            header( "Content-type:  application/json" );
            header( 'Content-Disposition: attachment; filename="demo-options.json"' );
            $res = array();

            $nav_menu_locations = get_theme_mod('nav_menu_locations');
            $res['menu'] = array();
            if($nav_menu_locations){
                foreach($nav_menu_locations as $k => $nav){
                    if($term = get_term($nav, 'nav_menu')) $res['menu'][$k] = $term->slug;
                }
            }

            $sidebars_widgets = get_option('sidebars_widgets');
            $res['widgets'] = array();
            if($sidebars_widgets){
                $widgets = array();
                foreach($sidebars_widgets as $k => $wgts){
                    if($k!='wp_inactive_widgets' && $k!='array_version' && !empty($wgts)){
                        $res['widgets'][$k] = array();
                        foreach($wgts as $w){
                            preg_match('/(.*)-(\d+)$/i', $w, $matches);
                            if(!isset($widgets[$matches[1]])) $widgets[$matches[1]] = get_option('widget_'.$matches[1]);
                            $res['widgets'][$k][$w] = $widgets[$matches[1]][$matches[2]];
                            if($matches[1]=='nav_menu'){
                                $mid = $widgets['nav_menu'][$matches[2]]['nav_menu'];
                                if($term2 = get_term($mid, 'nav_menu')){
                                    $res['widgets'][$k][$w]['nav_menu'] = $term2->slug;
                                }
                            }
                        }
                    }
                }
            }

            // 其他信息，比如分类、首页
            $res['show_on_front'] = get_option( 'show_on_front' );
            if($res['show_on_front']=='page'){
                $page = get_post(get_option( 'page_on_front' ));
                $res['page_on_front'] = $page->post_name;
            }

            $res['options'] = $this->options;
            wp_send_json($res);
        }
    }
    public function reauth(){
        if(current_user_can( 'edit_theme_options' )){
            update_option( "izt_theme_email", '' );
            update_option( "izt_theme_token", '' );
            wp_redirect(admin_url('admin.php?page=wpcom-panel'));
            exit;
        }
    }
}

$wpcom_panel = new WPCOM_Panel();
<?php
defined( 'ABSPATH' ) || exit;

class WX_share{
    public function __construct() {
        add_action('wp_ajax_wpcom_wx_config', array($this, 'wpcom_wx_config'));
        add_action('wp_ajax_nopriv_wpcom_wx_config', array($this, 'wpcom_wx_config'));
    }

    public function wpcom_wx_config(){
        if($url = $_POST['url']) {
            global $options;
            $wx = array();

            //生成签名的时间戳
            $wx['timestamp'] = time();

            $wx['appId'] = $options['wx_appid']?:'';

            //生成签名的随机串
            $wx['noncestr'] = 'www.wpcom.cn';

            // jsapi_ticket的有效期为7200秒，通过access_token来获取。
            $wx['jsapi_ticket'] = $this->get_jsapi_ticket();

            //分享的地址，不包含#及其后面部分
            $wx['url'] = urldecode($url);
            $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wx['jsapi_ticket'], $wx['noncestr'], $wx['timestamp'], $wx['url']);

            //生成签名
            $wx['signature'] = sha1($string);
            $wx['desc'] = $options['wx_desc'] ?: '';

            $img_url = WPCOM::thumbnail_url($_POST['ID']);
            $wx['thumb'] = $img_url ?: $options['wx_thumb'];
            $wx['thumb'] = is_numeric($wx['thumb']) ? wp_get_attachment_url( $wx['thumb'] ) : $wx['thumb'];

            $wx = apply_filters( 'wpcom_wx_config', $wx );
            wp_send_json($wx);
        }
        exit;
    }

    //获取微信公众号 ticket
    function get_jsapi_ticket() {
        $ticket = '';
        if($old_ticket = get_option('wx_ticket')){
            if(time() - $old_ticket['timestamp']<6900 && $old_ticket['ticket']){
                $ticket = $old_ticket['ticket'];
            }
        }

        if($ticket=='') {
            global $options;
            $appid = $options['wx_appid'] ?: '';
            $secret = $options['wx_appsecret'] ?: '';
            $url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", WPCOM::wxmp_token($appid, $secret));
            $result = wp_remote_request($url, array('method' => 'get'));
            if (is_array($result)) {
                $res = $result['body'];
                $res = json_decode($res, true);

                // api_ticket，有效期是7200s
                $tickets = array(
                    'ticket' => $res['ticket'],
                    'timestamp' => time()
                );
                update_option('wx_ticket', $tickets);

                $ticket = $res['ticket'];
            }
        }
        return $ticket;
    }
}
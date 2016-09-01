<?php
namespace XCaptcha\Client;
use XCaptcha\Client\xcaptchaException;

class xcaptchaClient{
    var $appkey         = null;
    var $appsecret      = null;
    var $url            = null;
    var $onsuccess      = null;
    var $onfail         = null;
    var $onfall         = null;
    var $oninitialized  = null;
    var $ontrigger      = null;
    var $style          = [];
    var $param          = [];

    public function __construct($appkey, $appsecret){
        $this->appkey       = $appkey;
        $this->appsecret    = $appsecret;
        $this->param['k']   = $appkey;
    }

    public function sign(){
        $param = $this->param;
        unset( $param['appsecret'] );
        unset( $param['sign'] );
        unset( $param['style'] );
        unset( $param['onsuccess'] );
        unset( $param['onfail'] );
        unset( $param['oninitialized'] );
        unset( $param['ontrigger'] );
        ksort( $param );
        $this->param['sign'] = md5($this->appkey . '|' . http_build_query($this->param) . '|' . $this->appsecret);
        return $this->param['sign'];
    }

    /**
     * 返回基礎JS
     */
    public function base(){
        return implode('', [
            '<script src="https://www.233sec.com/xcaptcha/api.js"></script>'
        ]);
    }

    /**
     * 生成校驗區域
     * @param array     $style              樣式
     * @param string    $onsuccess          成功驗證的回調, 請寫function名, 如 "window.oncaptcha_success"
     * @param string    $onfail
     * @param string    $onfall
     * @param string    $oninitialized
     * @param string    $ontrigger
     */
    public function display($input = '#input-captcha', $url = 'https://www.233sec.com/', $style = [], $onsuccess = null, $onfail = null, $onfall = null, $oninitialized = null, $ontrigger = null){
        $this->url              = $url;
        $this->style            = $style;
        $this->onsuccess        = $onsuccess;
        $this->onfail           = $onfail;
        $this->onfall           = $onfall;
        $this->oninitialized    = $oninitialized;
        $this->ontrigger        = $ontrigger;
        $this->sign();

        return implode('', [
            '$('.$input.').xcaptcha(',
                json_encode($this->param),
            ');'
        ]);
    }

    /**
     * 驗證用戶傳入
     * @param string    $challenge_response 傳入的challenge_response
     * @param string    $remote_addr        用戶IP 可不傳
     */
    public function verify($challenge_response, $remote_addr = ''){
        $param = [
            'k'         => $this->appkey,
            'response'  => $challenge_response,
            'remoteip'  => $remote_addr
        ];
        ksort($param);
        $param['sign']  = md5( $this->appkey.'|'. http_build_query($param) . '|'.$this->appsecret);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.233sec.com/xcaptcha/api2/siteverify?'.http_build_query($param) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_HEADER, 0 );
        $result = curl_exec( $ch );

        $result = json_decode($result);
        $json_err = json_last_error();
        if($json_err !== JSON_ERROR_NONE){
            // JSON解析出錯 邏輯處理
            throw XCaptcha\Client\xcaptchaException('JSON_ERROR_' . $json_err, 700 + $json_err);
        }

        if ($result->head->statusCode !== 0) {
            // 驗證失敗, $result->head->note是原因
            throw XCaptcha\Client\xcaptchaException($result->head->note, $result->head->statusCode);
        }

        return true;
    }
}
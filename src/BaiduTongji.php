<?php
/**
 * Created by PhpStorm.
 * User: mushan
 * Date: 2016/11/26
 * Time: 12:50
 */

namespace mcmf\BaiduTongji;

use think\Cache;


class BaiduTongji
{
    const API_URL = 'https://api.baidu.com/json/tongji/v1/ReportService';

    private $config = [
        'uuid' => '',  //任意的32位字符
        'token' => '',  //开通百度api统计导出服务后可以获得
        'username' => '', //百度统计账号
        'password' => '', //百度统计密码
        'account_type' => 1,
    ];

    private $login;

    private $header, $post_header;

    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);

        $login = $this->login();

        $this->header = [
            'UUID:' . $this->uuid,
            'USERID:' . $login['ucid'],
            'Content-Type:data/json;charset=UTF-8'
        ];

        $this->post_header = [
            'username' => $this->username,
            'password' => $login['st'],
            'token' => $this->token,
            'account_type' => $this->account_type
        ];
    }

    private function login()
    {
        return Cache::remember('baiduTongji-key',function (){
            $this->login = new Login($this->config);
            $this->login->preLogin();
            $this->login->doLogin();
            return [
                'ucid' => $this->login->ucid,
                'st' => $this->login->st
            ];
        },30);
    }



    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }

    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function getSiteLists($is_concise = false)
    {

        $result = Cache::remember('baiduTongji-siteLists', function () {
            return $this->request('getSiteList', null);
        },30);

        if (empty($result['list'])) {
            throw new \Exception('没有站点');
        }

        $list = $result['list'];

        if ($is_concise) {
            $list = (collect($list))->pluck('domain', 'site_id')->toArray();
        }

        return $list;
    }

    public function getData($param = array())
    {
        if (!isset($param['site_id'])) {
            $list = $this->getSiteLists();
            $param['site_id'] = $list[0]['site_id'];
        }

        $result = $this->request('getData', $param);

        return $result['result'];
    }

    private function request($type, $post_data)
    {
        $post_data = [
            'header' => $this->post_header,
            'body' => $post_data
        ];

        $result = curl_post(self::API_URL . '/' . $type, json_encode($post_data), $this->header);
        $result = json_decode($result, true);

        if ($result['header']['status'] != 0) {
            $failure = $result['header']['failures'][0];
            $message = 'level:' . $result['header']['desc'] . ';code:' . $failure['code'] . ';message:' . $failure['message'];
            throw new \Exception($message);
        }


        return $result['body']['data'][0];
    }
}
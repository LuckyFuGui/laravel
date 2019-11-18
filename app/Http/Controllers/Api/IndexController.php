<?php

namespace App\Http\Controllers\Api;

use App\Model\User;
use App\Model\Workers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    // 授权
    private $appid = 'wxb2b4c22f8c889787';
    private $appsecret = '2930d8eb4c5b70bf372ea4311bfb1704';
    private $wap = 'http://www.smalllucky.cn/api/index';

    /**
     * 用户授权
     */
    public function index()
    {
        if (!isset($_GET['code'])) {
            $callback = $this->wap;
            $this->get_code($callback);
        } else {
            $code = $_GET['code'];
            // code换取token
            $data = $this->get_access_token($code);
            // token换取用户数据
            $data_all = $this->get_user_info($data['access_token'], $data['openid']);
            // 添加或者更新
            $res = User::firstOrCreate(['openid' => $data_all['openid']], [
                'openid' => $data_all['openid'],
                'nickname' => $data_all['nickname'],
                'sex' => $data_all['sex'],
                'language' => $data_all['language'],
                'city' => $data_all['city'],
                'province' => $data_all['province'],
                'country' => $data_all['country'],
                'headimgurl' => $data_all['headimgurl']
            ]);
            header("Location:http://localhost:8080/#/home?openid=" . $data_all['openid']);
            return;
        }
    }

    /**
     * 获取阿姨数据
     */
    public function worker()
    {
        if (!isset($_GET['code'])) {
            $callback = $this->wap;
            $this->get_code($callback);
        } else {
            $code = $_GET['code'];
            // code换取token
            $data = $this->get_access_token($code);
            // token换取用户数据
            $data_all = $this->get_user_info($data['access_token'], $data['openid']);
            // 查询员工
            $userRes = User::where('openid',$data_all['openid'])->first();
            if($userRes) $res = Workers::where('uid',$userRes['id'])->first();
            if(!$res) return $this->error('目前不是员工');
            header("Location:http://localhost:8080/#/home?openid=" . $data_all['openid']);
            return;
        }
    }

    /**
     * 获取code
     */
    public function get_code($callback)
    {
        $appid = $this->appid;
        $scope = 'snsapi_userinfo';
        $state = md5(uniqid(rand(), TRUE));
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . urlencode($callback) . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
        header("Location:$url");
    }

    /**
     * 获取token
     */
    public function get_access_token($code)
    {
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
        $user = json_decode(file_get_contents($url));
        if (isset($user->errcode)) {
            echo 'error:' . $user->errcode . '<hr>msg  :' . $user->errmsg;
            exit;
        }
        $data = json_decode(json_encode($user), true);
        return $data;
    }

    /**
     * 获取用户数据
     */
    public function get_user_info($access_token, $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        $user = json_decode(file_get_contents($url));
        if (isset($user->errcode)) {
            echo 'error:' . $user->errcode . '<hr>msg  :' . $user->errmsg;
            exit;
        }
        $data = json_decode(json_encode($user), true);
        return $data;
    }
}

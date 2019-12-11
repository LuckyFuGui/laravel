<?php

namespace App\Http\Controllers\Api;

use App\Model\User;
use App\Model\Workers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    // 授权
//    private $appid = 'wxb2b4c22f8c889787';
//    private $appsecret = '2930d8eb4c5b70bf372ea4311bfb1704';
//    private $wap = 'http://www.smalllucky.cn/api/index';
//    private $worker = 'http://www.smalllucky.cn/api/worker';
    /*********正式************/
    private $appid = 'wx19d0b3b3eb9ff6cf';
    private $appsecret = 'be79036cf2e36d3fa9124e6570fcbc9e';
    private $wap = 'http://cqdaguanjia.com/api/index';
    private $worker = 'http://cqdaguanjia.com/api/worker';

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
            $userStatus = User::firstOrCreate(['openid' => $data_all['openid']], [
                'openid' => $data_all['openid'],
                'nickname' => $data_all['nickname'],
                'sex' => $data_all['sex'],
                'language' => $data_all['language'],
                'city' => $data_all['city'],
                'province' => $data_all['province'],
                'country' => $data_all['country'],
                'headimgurl' => $data_all['headimgurl']
            ]);
            if ($userStatus['status'] == 0) return $this->error('账号被封', [], 404);
            header("Location:http://www.cqdaguanjia.com/home?openid=" . $data_all['openid']);
            return;
        }
    }

    /**
     * 获取阿姨数据
     */
    public function worker()
    {
        if (!isset($_GET['code'])) {
            $callback = $this->worker;
            $this->get_code($callback);
        } else {
            $code = $_GET['code'];
            // code换取token
            $data = $this->get_access_token($code);
            // token换取用户数据
            $data_all = $this->get_user_info($data['access_token'], $data['openid']);
            // 查询员工
            $userRes = User::where('openid', $data_all['openid'])->first();
            if ($userRes) $res = Workers::where('uid', $userRes['id'])->first();
            if (!$res) {
                header("Location:http://www.cqdaguanjia.com/noStaff");
                return;
            }
            if($res->status == 2){
                return $this->error('暂时没有权限', [], 404);
            }
            header("Location:http://www.cqdaguanjia.com/staff?openid=" . $data_all['openid']);
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

    /**
     * 分享
     */
    public function share(Request $request)
    {
        $url = $request->all();
        $durl = $url['url'];
        $durl = urldecode($durl);
        $jsapiTicket = $this->getJsApiTicket();
        dd($jsapiTicket);
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序

        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$durl";

        $signature = sha1($string);
        $signPackage = [

            "appId" => $this->appId,

            "nonceStr" => $nonceStr,

            "timestamp" => $timestamp,

            "url" => $url,

            "signature" => $signature,

            "rawString" => $string

        ];

//        var_dump($signPackage);die;

        throw new SuccessMessage(['msg' => $signPackage]);
    }
    private function getJsApiTicket()

    {

        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例

        $data = json_decode(file_get_contents("jssdk/jsapi_ticket.json"));

        if ($data->expire_time < time()) {

            $accessToken = $this->getAccessToken();

            //定义传递的参数数组

            $params['type'] = 'jsapi';

            $params['access_token'] = $accessToken;

            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $params['access_token'] . "&type=" . $params['type'] . "";

            $res = json_decode(curl_get($url, $params));

            $ticket = isset($res->ticket) ? $res->ticket : NULL;

            if ($ticket) {

                $res->expire_time = time() + 7000;

                $res->jsapi_ticket = $ticket;

                $fp = fopen("jssdk/jsapi_ticket.json", "w");

                fwrite($fp, json_encode($res));

                fclose($fp);

            }

        } else {

            $ticket = $data->jsapi_ticket;

        }

        return $ticket;

    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 返回结果
     */
    public function success($data = [], $code = 200, $msg = '成功')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ];
    }

    /**
     * 返回分页结果
     */
    public function successPage($data = [], $page = 0, $code = 200, $msg = '成功')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
            'page' => $page
        ];
    }

    /**
     * 返回异常
     */
    public function error($data = [], $code = 500, $msg = '失败')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ];
    }

    /**
     * 删除图片
     */
    public function unlink($url)
    {
        $path = public_path() . $url;
        @unlink($path);
        return true;
    }

    /**
     * 页码
     */
    public function newPage($page)
    {
        return !empty($page) ? $page : 1;
    }
    
    /**
     * 条数
     */
    public function newLimit($limit)
    {
        return !empty($limit) ? $limit : 10;
    }
}

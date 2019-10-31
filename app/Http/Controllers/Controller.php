<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功参数
     * [success description]
     * @param  [type]  $data [description]
     * @param  integer $code [description]
     * @param  string  $smg  [description]
     * @return [type]        [description]
     */
    public function success($data = [], $code = 200, $smg = '成功')
    {
    	return [
    		'code' => $code,
    		'data' => $data,
    		'msg'  => $smg
    	];
    }
    /**
     * 失败参数
     * [error description]
     * @param  [type]  $data [description]
     * @param  integer $code [description]
     * @param  string  $smg  [description]
     * @return [type]        [description]
     */
    public function error($data = [], $code = 500, $smg = '失败')
    {
    	return [
    		'code' => $code,
    		'data' => $data,
    		'msg'  => $smg
    	];
    }
}

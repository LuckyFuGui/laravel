<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Banner;

class BannerController extends Controller
{
	/**
	 * 添加轮播图
	 * [create description]
	 * @param  Request $Request [description]
	 * @return [type]           [description]
	 */
    public function create(Request $Request)
    {
    	$data['img'] = $Request->img;
    	$res = Banner::create($data);
    	if ($res) return $this->success();
    	return $this->error();
    }
    /**
     * 查询
     * [show description]
     * @param  Request $Request [description]
     * @return [type]           [description]
     */
    public function show(Request $Request)
    {
    	$page = !empty($Request->page) ?? : 1;
    	$limit = !empty($Request->limit) ?? : 5;
    	$data = Banner::orderBy('id', 'DESC')-> offset($page)->limit($limit);
    	return $this->success($data);
    }
    /**
     * 删除数据
     * [destroy description]
     * @param  Request $Request [description]
     * @return [type]           [description]
     */
    public function destroy(Request $Request)
    {
    	$res = Banner::where('id', $Request->id)->destroy();
    	if ($res) return $this->success();
    	return $this->error();
    }
}

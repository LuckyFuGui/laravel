<?php

namespace App\Http\Controllers\Web;

use App\Model\Admins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
	
	/**
	 * 添加用户
	 * [create description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function story(Request $request)
    {
	    $data['name'] = $request->name;
	    $data['pwd'] = md5($request->pwd);
	    $data['token'] = md5(time() . $request->pwd . time());
	    $res = Admins::create($data);
	    if ($res) return $this->success();
	    return $this->error();
    }

    /**
     * 登陆
     * [login description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login(Request $request)
    {
    	$data['name'] = $request->name;
	    $data['pwd'] = md5($request->pwd);
	    $user = Admins::where($data)->find(1);
	    if ($user) {
	    	$pwd['token'] = md5(time() . $request->pwd . time());
	    	Admins::where('id', $user['id'])->update($pwd);
	    	return $this->success($pwd);
	    }
	    return $this->error();
    }

    /**
     * 退出登陆
     * [outLogin description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function outLogin(Request $request)
    {
    	$data['token'] = $request->token;
	    $user = Admins::where($data)->find(1);
	    if ($user) {
	    	$pwd['token'] = md5(time() . $request->token . time());
	    	Admins::where('id', $user['id'])->update($pwd);
	    	return $this->success();
	    }
	    return $this->error();
    }

    /**
     * 删除管理员
     * [destroy description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function destroy(Request $request)
    {
        $res = Admins::destroy($request->id);
        if ($res) return $this->success();
        return $this->error();
    }

    /**
     * 管理员列表
     * [index description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function index(Request $request)
    {
    	$page = $this->newPage($request->page);
    	$limit = $this->newLimit($request->limit);
        $data = Admins::orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $count = Admins::count();
        return $this->successPage($data, $count);
    }
}

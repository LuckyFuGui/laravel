<?php

namespace App\Http\Controllers\Api;

use App\Model\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * 修改
     * [save description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function save(Request $request)
    {
    	$data = $request->all();
    	unset($data['id']);
        $res = Address::find($request->id)->update($data);
        if ($res) return $this->success();
        return $this->error();
    }
    
    /**
     * 删除
     * [destroy description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function destroy(Request $request)
    {
        $res = Address::destroy($request->id);
        if ($res) return $this->success();
        return $this->error();
    }

    /**
     * 列表
     * [index description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function index(Request $request)
    {
        $page = $this->newPage($request->page);
        $limit = $this->newLimit($request->limit);
        $data = Address::where('uid', $request->uid)
            ->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $count = Address::where('uid', $request->uid)->count();
        return $this->successPage($data, $count);
    }

    /**
     * 添加
     * [store description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
        $res = Address::create($request->all());
        if ($res) return $this->success();
        return $this->error();
    }
}

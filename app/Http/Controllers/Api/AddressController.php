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
        $data = Address::query()->where('uid', $request->uid)
            ->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id','desc')
            ->get();
        $count = Address::where('uid', $request->uid)->count();
        return $this->successPage($data, $count);
    }

    /**
     * 单个地址
     * @param Request $request
     * @return array
     */
    public function onlyIndex(Request $request)
    {
        $data = Address::find($request->id)->first();
        return $this->success($data);
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

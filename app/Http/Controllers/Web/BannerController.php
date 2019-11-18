<?php

namespace App\Http\Controllers\Web;

use App\Model\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    /**
     * 修改
     * [save description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function save(Request $request)
    {
        $res = Banner::find($request->id)->update(['img' => $request->img]);
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
        $res = Banner::destroy($request->id);
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
        $data = Banner::select('img')
            ->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $count = Banner::count();
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
        $res = Banner::create($request->all());
        if ($res) return $this->success();
        return $this->error();
    }
}

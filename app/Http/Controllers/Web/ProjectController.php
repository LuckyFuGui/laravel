<?php

namespace App\Http\Controllers\Web;

use App\Model\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
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
        $res = Project::find($request->id)->update($data);
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
        $res = Project::destroy($request->id);
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
        $data = Project::where('type', $request->type)
        	->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $count = Project::where('type', $request->type)->count();
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
    	// dd($request->all());
        $res = Project::create($request->all());
        if ($res) return $this->success();
        return $this->error();
    }
}

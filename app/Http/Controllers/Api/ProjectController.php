<?php

namespace App\Http\Controllers\Api;

use App\Model\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
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
        $data = Project::query()->where('type', $request->type)
            ->where('state',1)
        	->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id','desc')
            ->get();
        $count = Project::where('type', $request->type)->where('state',1)->count();
        return $this->successPage($data, $count);
    }
}

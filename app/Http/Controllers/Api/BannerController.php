<?php

namespace App\Http\Controllers\Api;

use App\Model\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
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
        $data = Banner::select('img')
            ->orderby('id', 'DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        $count = Banner::count();
        return $this->successPage($data, $count);
    }
}

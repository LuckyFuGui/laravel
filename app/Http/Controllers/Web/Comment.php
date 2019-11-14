<?php

namespace App\Http\Controllers\Web;

use App\Model\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Comment extends Controller
{

    /**
     * 评论列表
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }


        if(!isset($request->limit)){
            $request->limit = 20;
        }else{
            isset($request->limit) && $request->limit > 20 ? 20 : $request->limit;
        }
        $query = \App\Model\Comment::query()->with('worker','orders.user');
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->get();

        $count = $query->count();
        return $this->successPage($data, $count);
    }
}

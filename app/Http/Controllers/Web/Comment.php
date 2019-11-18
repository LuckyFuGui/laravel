<?php

namespace App\Http\Controllers\Web;

use App\Model\Order;
use App\Model\Workers;
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
        $query = \App\Model\Comment::query()->with('orders.user');
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->get();

        foreach ($data as $k=>$v){
            $ids = explode(',',$v->worker_id);
            $data[$k]['worker'] = Workers::query()->whereIn('id',$ids)->get()->toArray();
        }



        $count = $query->count();
        return $this->successPage($data, $count);
    }
}

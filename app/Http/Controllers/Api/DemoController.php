<?php

namespace App\Http\Controllers\Api;

use App\Model\Demo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DemoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'title' => 'required',
        ]);
        $id = Demo::createGetId($data);
//        $datas = Demo::where('id',2)->get()->toArray();
//        $res = Demo::find(2)->delete();
        dd($id);
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 表
    protected $table = 'order';
    // 过滤字段
    protected $guarded = [];

    // 关联数据
    public function workerUser()
    {
        return $this->belongsTo('App\Model\Workers', 'sid');
    }


    //服务关联
    public function order_comment()
    {
        return $this->hasOne('App\Model\Comment', 'order_id');
    }

    /**
     * 订单关联用户
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User', 'uid');
    }

    public function order_project()
    {
        return $this->hasMany('App\Model\OrderProject', 'oid');
    }
}

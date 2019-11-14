<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserOrders extends Model
{
    // 表名
    protected $table  = 'orders_user';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'uid', 'json'
    ];

    /**
     * 用户模型关联
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User','uid');
    }
}

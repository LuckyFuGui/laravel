<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    // 表
    protected $table = 'comment';
    // 过滤字段
    protected $guarded = [];

    public function worker()
    {
        return $this->belongsTo('App\Model\Workers', 'worker_id');
    }
}

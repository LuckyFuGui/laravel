<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 表
    protected $table = 'order';
    // 过滤字段
    protected $guarded = [];
}

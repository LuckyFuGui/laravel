<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    // 表
    protected $table = 'address';
    // 过滤字段
    protected $guarded = [];
}

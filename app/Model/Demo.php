<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demo extends Model
{
    // 软删除
    use SoftDeletes;

    // 表明
    protected $table = "demo";

    // 允许添加字段
    protected $fillable = [];

    // 过滤字段
    protected $guarded = [];

    // 查询字段自动过滤
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // 添加默认数据
    protected $attributes = [];
}

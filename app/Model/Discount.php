<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    // 表名
    protected $table  = 'discount';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'status', 'type', 'admin_id', 'begin_at', 'end_at', 'effective_at', 'invalid_at', 'voucher_type','voucher_price',
        'voucher_num','sale_price','salable_num','sold_num'
    ];
}

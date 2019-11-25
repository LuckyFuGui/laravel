<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiscountUser extends Model
{
    // 表名
    protected $table  = 'discount_user';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'uid', 'discount_id', '	voucher_type', 'voucher_price', 'use_at', 'status'
    ];

    public function discount()
    {
        return $this->belongsTo('App\Model\Discount','discount_id');
    }
}

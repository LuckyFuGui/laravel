<?php

namespace App\Console\Commands;


use App\Model\DiscountPurchaseRecord;
use App\Model\DiscountUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class CheckConponStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check_conpon_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检测优惠券支付状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        DiscountPurchaseRecord::query()->with('discount')->where('pay_status',0)
            ->where('created_at','<',date('Y-m-d H:i:s',strtotime('-30 min')))
            ->chunk(100,function($items){
                foreach($items as $item){

                    DB::beginTransaction();
                    try {
                        $item->discount->salable_num += 1;
                        $item->discount->sold_num -= 1;
                        $item->discount->save();

                        DiscountUser::query()->where('pay_sn',$item->pay_sn)->delete();
                        $item->delete();

                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollBack();
                        info('优惠券检测失败');
                        info($e->getMessage());
                    }
                }
            });

        dump('执行完成');
    }
}

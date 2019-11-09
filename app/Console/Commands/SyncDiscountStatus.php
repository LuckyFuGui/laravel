<?php

namespace App\Console\Commands;


use App\Model\Discount;
use App\Model\LeaveLog;
use App\Model\Wasteland;
use App\Model\Workers;
use App\Models\PlSpu;
use App\Model\DailyCleaning;
use App\Services\Api\PlSpuApiService;
use Illuminate\Console\Command;

class SyncDiscountStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_discount_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新代金劵活动状态';

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
        Discount::query()->whereIn('status',[0,1])->chunk(100, function ($items) {
            foreach ($items as $item){
                if(time() > strtotime($item->end_at)){
                    $item->status = 2;
                }elseif (time() > strtotime($item->begin_at) && time() < strtotime($item->end_at)){
                    $item->status = 1;
                }else{
                    $item->status = 3;
                }
                $item->save();
            }
        });

        info('实时刷新代金劵状态');
    }
}

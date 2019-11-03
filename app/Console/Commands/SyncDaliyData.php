<?php

namespace App\Console\Commands;


use App\Models\PlSpu;
use App\Model\DailyCleaning;
use App\Services\Api\PlSpuApiService;
use Illuminate\Console\Command;

class SyncDaliyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_daliy_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '写入日常保洁基础数据';

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
        $data = [
            [
                'hour'=>2,
                'price'=>80
            ],[
                'hour'=>3,
                'price'=>108
            ],[
                'hour'=>4,
                'price'=>128
            ],[
                'hour'=>5,
                'price'=>148
            ],
        ];

        foreach ($data as $item){
            DailyCleaning::query()->create($item);
        }

        dump('写入完成');
    }
}

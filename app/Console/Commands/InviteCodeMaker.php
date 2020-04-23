<?php

namespace App\Console\Commands;

use App\Models\InviteCode;
use Illuminate\Console\Command;

class InviteCodeMaker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'produce invite_code record';

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
        InviteCode::create([
            'code' => str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'times' => 100
        ]);
        echo "\r\n Complete !";
    }
}

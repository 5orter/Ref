<?php

namespace App\Console\Commands;

use App\Infrastructure\Services\AsAccountantService;
use Illuminate\Console\Command;

class UpdateAccountantAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:accessToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update AS-Accountant Access Token.';

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
     * @return void
     */
    public function handle(): void
    {
        $status = (new AsAccountantService())->refreshAccessToken();

        if ($status) {
            $this->info('Success: Access Token Successfully Updated.');
        } else {
            $this->error('Error: Sorry, something went wrong.');
        }
    }
}

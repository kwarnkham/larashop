<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EncryptEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt the env with the provided encryption key.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = config('app')['env_encryption_key'];
        if ($key == null) echo 'No env encryption key found';
        else {
            $exitCode = Artisan::call("env:encrypt --force --key=$key");
            if ($exitCode == 0) {
                echo "Encrypted successfully";
            } else {
                echo "The command is execuated with exit code :$exitCode";
            }
        }
    }
}

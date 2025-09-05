<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePublicProfileUuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-uuids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate public profile UUIDs for existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('public_profile_uuid')->get();

        if ($users->isEmpty()) {
            $this->info('No users found without UUID.');

            return;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->public_profile_uuid = (string) Str::uuid();
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated UUIDs for {$users->count()} users.");
    }
}

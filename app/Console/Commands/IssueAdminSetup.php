<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IssueAdminSetup extends Command
{
    protected $signature = 'vidya:issue-admin-setup {--minutes=60 : Lifetime, 5 to 1440 minutes}';
    protected $description = 'Issue a first-admin token to private storage without printing it';

    public function handle(): int
    {
        $minutes = filter_var($this->option('minutes'), FILTER_VALIDATE_INT);
        if ($minutes === false || $minutes < 5 || $minutes > 1440) {
            $this->error('Lifetime must be between 5 and 1440 minutes.');
            return self::FAILURE;
        }
        $target = storage_path('app/private/first-admin-setup.txt');
        $created = false;
        try {
            DB::transaction(function () use ($minutes, $target, &$created) {
                $setup = DB::table('admin_setup')->where('id', 1)->lockForUpdate()->first();
                if (! $setup || $setup->completed_at || User::where('is_admin', true)->exists()) {
                    throw new \RuntimeException('Setup is closed or not migrated.');
                }
                $token = bin2hex(random_bytes(32));
                $oldMask = umask(0077);
                try {
                    $file = @fopen($target, 'x');
                } finally {
                    umask($oldMask);
                }
                if (! $file) {
                    throw new \RuntimeException('Retrieve and remove the existing private setup file before reissuing.');
                }
                $created = true;
                try {
                    $message = 'First-admin token (expires in '.$minutes.' minutes):'.PHP_EOL.$token.PHP_EOL;
                    if (! chmod($target, 0600) || fwrite($file, $message) !== strlen($message)) {
                        throw new \RuntimeException('Could not securely save token.');
                    }
                } finally {
                    fclose($file);
                }
                DB::table('admin_setup')->where('id', 1)->update([
                    'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes($minutes), 'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($created && is_file($target)) {
                unlink($target);
            }
            $this->error('Token not issued. Check setup state, migrations and private storage; retrieve/remove any existing setup file.');
            return self::FAILURE;
        }
        $this->info('Token saved privately: storage/app/private/first-admin-setup.txt (0600). Retrieve privately, then remove it.');
        return self::SUCCESS;
    }
}

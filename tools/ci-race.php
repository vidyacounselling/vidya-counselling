<?php

// Isolated MySQL concurrent setup test. This is not a public route or deploy command.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (getenv('GITHUB_ACTIONS') !== 'true' || getenv('DB_HOST') !== '127.0.0.1'
    || getenv('DB_DATABASE') !== 'vidya_race' || ! function_exists('pcntl_fork')) {
    throw new RuntimeException('Requires isolated CI MySQL race database and pcntl.');
}
$db = \Illuminate\Support\Facades\DB::connection();
if ($db->table('users')->count() !== 0) {
    throw new RuntimeException('Race database must be fresh.');
}
$token = bin2hex(random_bytes(32));
$db->table('admin_setup')->where('id', 1)->update([
    'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes(5),
]);
\Illuminate\Support\Facades\DB::purge();
$directory = sys_get_temp_dir().'/vidya-race-'.bin2hex(random_bytes(8));
if (! mkdir($directory, 0700)) {
    throw new RuntimeException('Cannot create isolated barrier directory.');
}
$children = [];
for ($i = 0; $i < 2; $i++) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        throw new RuntimeException('Could not fork race worker.');
    }
    if ($pid === 0) {
        try {
            file_put_contents($directory.'/'.$i.'.ready', 'ready');
            $deadline = microtime(true) + 15;
            while (! is_file($directory.'/go')) {
                if (microtime(true) > $deadline) {
                    throw new RuntimeException('Barrier timed out.');
                }
                usleep(10000);
            }
            $request = \Illuminate\Http\Request::create('http://localhost/register', 'POST', [
                'name' => 'Race Test '.$i, 'email' => 'race'.$i.'@example.test',
                'password' => 'TestOnly-Password123!', 'password_confirmation' => 'TestOnly-Password123!',
                'setup_token' => $token,
            ]);
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle($request);
            file_put_contents($directory.'/'.$i.'.json', json_encode(['status' => $response->getStatusCode(), 'location' => $response->headers->get('Location')]));
            $kernel->terminate($request, $response);
            exit(0);
        } catch (Throwable $error) {
            // Never output request data/token or exception context.
            file_put_contents($directory.'/'.$i.'.json', json_encode(['status' => 0]));
            exit(1);
        }
    }
    $children[] = $pid;
}
$deadline = microtime(true) + 10;
while (! is_file($directory.'/0.ready') || ! is_file($directory.'/1.ready')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Workers did not reach the barrier.');
    }
    usleep(10000);
}
file_put_contents($directory.'/go', 'go');
foreach ($children as $pid) {
    pcntl_waitpid($pid, $status);
    if (! pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
        throw new RuntimeException('Race worker failed.');
    }
}
$results = [];
for ($i = 0; $i < 2; $i++) {
    $results[] = json_decode(file_get_contents($directory.'/'.$i.'.json'), true);
}
$statuses = array_column($results, 'status');
sort($statuses);
\Illuminate\Support\Facades\DB::purge();
$db = \Illuminate\Support\Facades\DB::connection();
$setup = $db->table('admin_setup')->where('id', 1)->first();
if ($statuses !== [302, 404] || $db->table('users')->count() !== 1
    || $db->table('users')->where('is_admin', true)->count() !== 1
    || $setup->token_hash !== null || $setup->completed_at === null) {
    throw new RuntimeException('Concurrent setup did not produce exactly one admin with consumed setup.');
}
foreach ($results as $result) {
    if ($result['status'] === 302 && ! str_ends_with((string) $result['location'], '/admin')) {
        throw new RuntimeException('Successful registration did not reach admin.');
    }
}
foreach (glob($directory.'/*') as $file) {
    unlink($file);
}
rmdir($directory);
echo json_encode(['concurrentRegistration' => 'pass', 'statuses' => $statuses, 'admins' => 1], JSON_PRETTY_PRINT);

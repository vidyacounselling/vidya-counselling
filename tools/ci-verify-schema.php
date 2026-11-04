<?php

// CI-only verifier; never copy tools/ into a public directory or deployment archive.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $error): void {
    fwrite(STDERR, 'CI schema verification failed: '.$error->getMessage().PHP_EOL);
    exit(1);
});
if (getenv('GITHUB_ACTIONS') !== 'true' || getenv('DB_HOST') !== '127.0.0.1') {
    throw new RuntimeException('This verifier requires the isolated CI MySQL service.');
}
$snapshots = [];
foreach (['vidya_schema', 'vidya_restore'] as $database) {
    config(['database.connections.mysql.database' => $database]);
    \Illuminate\Support\Facades\DB::purge('mysql');
    $db = \Illuminate\Support\Facades\DB::connection('mysql');
    $tables = $db->select('SELECT TABLE_NAME AS name, ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME', [$database]);
    $snapshot = [];
    foreach ($tables as $table) {
        if ($table->engine !== 'InnoDB' || ! preg_match('/\A[a-z_]+\z/', $table->name)) {
            throw new RuntimeException('Unexpected table or storage engine.');
        }
        $row = (array) $db->selectOne('SHOW CREATE TABLE `'.$table->name.'`');
        // mysqldump makes inherited utf8mb4 column charsets explicit on restore.
        // Compare effective column metadata as well as DDL; normalize only that
        // redundant spelling when its matching utf8mb4 collation is present.
        $snapshot[$table->name] = [
            'ddl' => preg_replace('/ CHARACTER SET utf8mb4(?= COLLATE utf8mb4_)/', '', $row['Create Table']),
            'columns' => json_encode($db->select(
                'SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, CHARACTER_SET_NAME, COLLATION_NAME, GENERATION_EXPRESSION FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
                [$database, $table->name],
            )),
        ];
    }
    if ($db->table('users')->count() !== 0 || $db->table('sessions')->count() !== 0
        || $db->table('cache')->count() !== 0 || $db->table('admin_setup')->count() !== 1) {
        throw new RuntimeException('First-install database contains unexpected state.');
    }
    $setup = $db->table('admin_setup')->where('id', 1)->first();
    if (! $setup || $setup->token_hash !== null || $setup->expires_at !== null || $setup->completed_at !== null) {
        throw new RuntimeException('First-admin setup must be unused and unissued.');
    }
    $snapshot['migration_rows'] = $db->table('migrations')->orderBy('id')->get()->toJson();
    $snapshots[] = $snapshot;
}
if ($snapshots[0] !== $snapshots[1]) {
    foreach ($snapshots[0] as $key => $value) {
        if ($value !== ($snapshots[1][$key] ?? null)) {
            fwrite(STDERR, json_encode(['different' => $key, 'migrated' => $value, 'restored' => $snapshots[1][$key] ?? null], JSON_PRETTY_PRINT).PHP_EOL);
        }
    }
    throw new RuntimeException('Imported SQL does not match migrated schema and migration bookkeeping.');
}
echo json_encode(['schemaRestore' => 'pass', 'engine' => 'InnoDB', 'users' => 0, 'setup' => 'unused', 'tables' => array_keys($snapshots[0])], JSON_PRETTY_PRINT);

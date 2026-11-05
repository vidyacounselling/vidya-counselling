#!/usr/bin/env bash
set -euo pipefail
test "${GITHUB_ACTIONS:-}" = true
mysql_ci() { docker exec -i "$MYSQL_CONTAINER" mysql -uroot -pci-disposable-password "$@"; }
mysql_ci -e 'CREATE DATABASE vidya_upgrade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_race CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
DB_DATABASE=vidya_race php artisan migrate --force
DB_DATABASE=vidya_race BCRYPT_ROUNDS=4 php tools/ci-staff-race.php > build/evidence/staff-race.json
DB_DATABASE=vidya_upgrade BCRYPT_ROUNDS=4 php tools/ci-upgrade.php
# Replay the generated SQL against an independent exact baseline schema with representative prior data.
git show ba88f82b8cacb52d28f054e9723503a1592b55f6:database/migrations/0001_01_01_000000_create_users_table.php > build/prior-users.php
DB_DATABASE=vidya_import php artisan migrate --force --path=database/migrations/0001_01_01_000000_create_users_table.php
DB_DATABASE=vidya_import php artisan migrate --force --path=database/migrations/0001_01_01_000001_create_cache_table.php
DB_DATABASE=vidya_import php artisan migrate --force --path=database/migrations/0001_01_01_000002_create_jobs_table.php
DB_DATABASE=vidya_import php artisan migrate --force --path=database/migrations/2026_11_04_000001_create_admin_setup_table.php
mysql_ci vidya_import -e "INSERT INTO users (id,name,email,password,is_admin,created_at,updated_at) VALUES (41,'Import owner','import-owner@example.test','test-only-preserved-hash',1,NOW(),NOW());"
mysql_ci vidya_import -e 'UPDATE migrations SET batch=1;'
mysql_ci vidya_import < build/upgrade-20261105.sql
test "$(mysql_ci -N vidya_import -e "SELECT COUNT(*) FROM users WHERE id=41 AND password='test-only-preserved-hash' AND role='administrator' AND active=1")" = 1
test "$(mysql_ci -N vidya_import -e 'SELECT COUNT(*) FROM migrations')" = 5
mysql_ci -N vidya_import -e 'SHOW TABLES' > build/evidence/upgrade-import-tables.txt
sha256sum build/upgrade-20261105.sql > build/evidence/upgrade-sql-sha256.txt

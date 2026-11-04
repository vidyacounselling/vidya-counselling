#!/usr/bin/env bash
# Run only inside GitHub Actions with the disposable MySQL service.
set -euo pipefail
test "${GITHUB_ACTIONS:-}" = true
test -n "${MYSQL_CONTAINER:-}"
mysql_ci() { docker exec -i "$MYSQL_CONTAINER" mysql -uroot -pci-disposable-password "$@"; }
dump_ci() { docker exec "$MYSQL_CONTAINER" mysqldump -uroot -pci-disposable-password --set-gtid-purged=OFF --no-tablespaces --column-statistics=0 --skip-comments "$@"; }
mysql_ci -e 'CREATE DATABASE vidya_schema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_race CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
DB_DATABASE=vidya_race php artisan migrate --force
DB_DATABASE=vidya_race BCRYPT_ROUNDS=4 php tools/ci-race.php > build/evidence/mysql-race.json
DB_DATABASE=vidya_schema php artisan migrate --force
# No CREATE DATABASE, USE, DROP TABLE, test users, token, or production credentials.
dump_ci --no-data --skip-add-drop-table vidya_schema > build/first-install.sql
# Preserve Laravel migration bookkeeping and the unused setup singleton.
dump_ci --no-create-info --skip-add-locks --skip-disable-keys vidya_schema migrations admin_setup >> build/first-install.sql
mysql_ci vidya_restore < build/first-install.sql
php tools/ci-verify-schema.php > build/evidence/schema-restore.json
sha256sum build/first-install.sql > build/evidence/schema-sha256.txt

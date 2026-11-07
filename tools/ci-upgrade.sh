#!/usr/bin/env bash
set -euo pipefail
test "${GITHUB_ACTIONS:-}" = true
mysql_ci() { docker exec -i "$MYSQL_CONTAINER" mysql -uroot -pci-disposable-password "$@"; }
mysql_ci -e 'CREATE DATABASE vidya_upgrade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE vidya_race CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
DB_DATABASE=vidya_race php artisan migrate --force
DB_DATABASE=vidya_race BCRYPT_ROUNDS=4 php tools/ci-staff-race.php > build/evidence/staff-race.json
DB_DATABASE=vidya_upgrade BCRYPT_ROUNDS=4 php tools/ci-upgrade.php
DB_DATABASE=vidya_import BCRYPT_ROUNDS=4 php tools/ci-upgrade.php
mysql_ci vidya_import < build/upgrade-20261107.sql
test "$(mysql_ci -N vidya_import -e "SELECT COUNT(*) FROM users WHERE id=41 AND role='administrator' AND active=1")" = 1
test "$(mysql_ci -N vidya_import -e "SELECT COUNT(*) FROM posts WHERE id=73 AND body_html='<p>Preserved previous release article.</p>'")" = 1
test "$(mysql_ci -N vidya_import -e "SELECT COUNT(*) FROM comments WHERE id=53 AND body='Preserved previous release comment.'")" = 1
test "$(mysql_ci -N vidya_import -e "SELECT COUNT(*) FROM public_pages WHERE reference='post:73'")" = 1
test "$(mysql_ci -N vidya_import -e 'SELECT COUNT(*) FROM migrations')" = 7
test "$(mysql_ci -N vidya_import -e 'SELECT COUNT(*) FROM services')" = 3
test "$(mysql_ci -N vidya_import -e 'SELECT COUNT(*) FROM inquiries')" = 0
test "$(mysql_ci -N vidya_import -e 'SELECT COUNT(*) FROM reviews')" = 0
mysql_ci -N vidya_import -e 'SHOW TABLES' > build/evidence/upgrade-import-tables.txt
sha256sum build/upgrade-20261107.sql > build/evidence/upgrade-sql-sha256.txt

# Remote build (no hosting deployment)

The application uses Laravel 13, Blade, plain CSS and vanilla JavaScript. No frontend build is required. PHP/Composer run on GitHub-hosted runners, not on the owner's PC.

`.github/workflows/remote-build.yml` runs on pull requests to main and manual dispatch. It uses PHP 8.3, Composer 2 and a disposable MySQL 8.0 service. Production secrets are neither required nor used. Action runtime internals are unrelated to the application's stack.

First bootstrap run: resolve dependencies, generate composer.lock, lint PHP, run isolated SQLite and MySQL tests, race two first-admin registrations, create a schema from fresh migrations, export it and verify an import into a second fresh database. The generated lock and evidence are uploaded as a short-lived artifact. No production deployment archive is built until composer.lock is committed and a subsequent locked run passes.

After reviewing and committing the generated lock, rerun. The workflow installs locked production dependencies and creates `private-app.zip` and `public-only.zip`. No .env, logs, setup tokens, backup files or CI runtime state belong in these archives. Public front-controller paths still need adaptation if the host uses split private/public directories. Do not upload the private archive into a public directory.

There is no Hostinger upload, Git deployment, webhook, provider connection or production database access in this workflow. GitHub build success does not establish hosting readiness. The real hosting database version, private directory mapping, permissions, APP_KEY/config delivery, migration execution/import and rollback must still be verified independently.

`first-install.sql` is only a candidate for a completely empty database. It preserves the migration ledger and the unused first-admin singleton, and contains no user or token. Match the target database engine/version before import. It must never be rerun on a populated database. Future releases require tested forward migrations or individually reviewed SQL changes with backups; repeatedly importing the initial schema is not an upgrade process.

Account-specific deployment findings and private backups remain outside the public repository. No production default credentials exist. Disposable CI credentials and a non-production test key in the workflow are test fixtures only.

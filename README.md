# Vidya Counselling Centre

Existing Laravel 13 application with Blade, plain CSS and vanilla JavaScript. No frontend compilation or local PHP/Composer/Node installation is required.

The editorial release adds staff management, Administrator/Editor permissions, blog publishing, private autosave, revisions, taxonomy, media, SEO/schema and moderated comments. Public registration is closed. Staff accounts are not patient records.

- Canonical administration: `/admin`, `/admin/users`, `/admin/blogs`.
- Public articles: `/blog`, `/blog/{slug}`, `/blog/archive/category/{slug}`, `/blog/archive/tag/{slug}`.
- Quill 2.0.3 is self-hosted; BSD-3-Clause notices are in `public/vendor/quill/LICENSE`.
- Symfony HTML Sanitizer is used on the server; executable body content is rejected/removed.
- Scheduled visibility is determined by a shared UTC eligibility query. Human schedule input is Asia/Kolkata.
- Only Administrators manage staff or tracking configurations. Without visitor consent, nonessential tracking stays disabled globally.
- No default users, seeded production content or plaintext passwords are included.

The GitHub Actions workflow runs isolated SQLite/MySQL tests, a previous-release additive SQL upgrade/replay check, concurrent last-admin protection, and browser tests against actual remote Blade pages. Production dependencies come from committed composer.lock. CI fixtures and tools are excluded from deployment archives.

Never run tests, `migrate:fresh`, or first-install SQL against production. Account-specific deployment/rollback records, secrets and private backups are excluded from this public repository.

Run `tools/Verify-Landing.ps1` to verify original landing assets/content, allowing only the explicitly requested discreet Blog navigation link. Latest automated evidence is in repository Actions. Release work and verification status are documented separately; a passing build alone does not imply a production deployment.

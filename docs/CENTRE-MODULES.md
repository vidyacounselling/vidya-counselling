# Centre website operations

The visible brand is **Vidya Counseling Centre**. Keep the operational domain, existing email addresses, repository names, historical slugs and migrations unchanged. Authentic client quotations retain their original wording.

## Workspace permissions

Administrators manage all modules, staff accounts, consent records and website settings. Editors manage Blogs, Services and FAQs. Consultation Inquiries require Administrator access or the explicit per-user inquiry permission. An Editor role alone never grants access. Inactive/archived accounts cannot sign in.

Services support private previews, structured facts, publication, ordering, image selection, archived recovery and historical slug redirects. Verify fee/duration facts before entering them. SEO/manual Service JSON-LD is validated; no invented medical credentials or self-serving ratings are emitted.

Client Stories require recorded publication consent and date, with separate identity/image permissions. Keep evidence in your approved private records; enter only a reference in encrypted notes. Do not upload private screenshots as website media. Reviews always arrive Pending, with private encrypted email. Moderators preserve the immutable original when making a privacy edit; only approved, consented records are visible. Restored reviews return Pending; stories, services and FAQs return Draft.

Consultation and Let’s Talk are two interfaces to the same inquiry workflow, not appointment booking or live chat. Names, contact details, messages, preferred windows and follow-up notes are encrypted using the existing APP_KEY. Keep that key secure and unchanged. Lists show references and operational fields; details and contact actions require permission. There are no bulk exports or automatic notifications/providers. Review retention manually using the configured day threshold; archive recoverably, or deliberately redact personal data through the Administrator-only typed confirmation. Backups have separate retention.

## FAQs

Write an FAQ manually or use the clearly labelled **Template draft helper**. It uses page facts and creates selected editable drafts only; there is no AI API/key or paid integration. Select destinations through grouped searchable page choices, remove selection chips, and set an order per page. References survive service/post URL changes. Draft/archived content and FAQs do not appear publicly. Assign the optional canonical schema destination to one selected page; website-level FAQ schema remains off by default. Visible FAQs do not depend on schema. Google removed FAQ rich results in 2026; no rich-result promise is made.

## Verified business content

The three services, contact information and WhatsApp number were confirmed by the owner on 7 November 2026. The genuine logo is retained. The founder story comes from the supplied centre brief. A genuine founder photograph is still missing; the portrait stays hidden until an approved image is selected. No qualifications, prices, opening hours, reviews, outcome claims or clinical specialties have been invented. Empty review/story sections are honest empty states.

## Release and recovery

Remote PHP/Composer/MySQL build only; no Node/npm/Vite or local PHP/Composer installation. Production packages contain locked production dependencies and separate public assets. No .env, credentials, backups, logs, test records or CI tools belong in Git/public artifacts. Never connect Git auto-deployment to the live directory as part of this artifact workflow.

For this release use only `upgrade-20261107.sql` against the deployed R2 five-migration baseline. It adds two migrations (seven total), new tables and one staff permission column, and seeds approved centre facts. It preserves existing users, password hashes, posts, comments and media. Never use first-install SQL, migrate:fresh or a reset. DDL may auto-commit: inspect any partial failure before retrying.

Place application/vendor/.env outside public_html in a new private release, copy the existing .env with mode 600, preserve the shared storage and encryption key, then map public index.php to that release. Keep the previous release and entry point for code rollback. Roll back the entry point if critical verification fails; leave additive tables and new writes intact. Do not restore an old database over new writes without a reconciliation plan. A fresh backup is normally recommended; the owner explicitly declined a new snapshot for this release, and existing private backups/releases remain preserved.

## Verification expectations

Require passing locked dependency audit, compiled Blade lint, SQLite/MySQL regressions, independent additive SQL replay, and actual PHP-rendered responsive/browser form checks at 360, 390, 768, 1280 and 1440 pixels. Verify the deployed release header, real Laravel pages, registration closure, authenticated admin permissions, assets and blocked secret paths. Use disposable isolated fixtures for publishing/destructive tests. Do not publish fake client feedback or capture production inquiry details in screenshots.

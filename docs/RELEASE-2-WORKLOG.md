# Staff and editorial release

Target: existing vidyacounselling.com Laravel deployment. Baseline source ba88f82b8cacb52d28f054e9723503a1592b55f6. Existing release is private vidya-app/release with a public_html entry point. Preserve production .env, APP_KEY, accounts, storage and uploads. Never import first-install.sql into production.

Implementation in progress. Deployment is not yet performed.

Design: calm ink/teal, warm paper, white surfaces, subtle borders, semantic status colors; shared Blade admin shell and reusable form/table patterns. Staff are Administrator or Editor; both may publish editorial content, only Administrators manage staff and script configuration. Nonessential scripts stay disabled without a visitor consent implementation. Scheduling is a UTC time eligibility query, not an unconfigured worker.

Remote build must exercise previous-schema upgrades and preserve seeded prior-release owner IDs/password hashes. SQL upgrade must be additive and migration bookkeeping consistent. Production schema and private backup must be inspected before cutover.

Official references reviewed: https://quilljs.com/docs/quickstart (2.0.3); https://developers.google.com/search/docs/appearance/structured-data/article; https://developers.google.com/search/docs/appearance/structured-data/sd-policies; https://schema.org/BlogPosting; https://schema.org/BreadcrumbList. Structured data must match visible genuine content; external rich-result validation and rankings are never implied.

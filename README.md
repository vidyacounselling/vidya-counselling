# Vidya Counselling Centre

Laravel 13 साठी तयार केलेला Blade application source. Plain CSS आणि vanilla JavaScript; frontend build आवश्यक नाही.

**स्थिती: source तयार; dependencies install, composer.lock, PHP tests आणि deployment बाकी. Live website बदललेली नाही.**

- मूळ landing page: `resources/views/landing.blade.php`
- Authentication व dashboard: `app/Http/Controllers/`, `resources/views/auth/`, `resources/views/admin/`
- पहिला admin: expiring single-use token; `admin_setup` singleton row वर transaction lock.
- Production मध्ये कोणतेही seeded users/default passwords नाहीत.
- Local PHP, Composer किंवा Node installation आवश्यक नाही. पुढील Composer/Artisan commands फक्त पडताळलेल्या remote environment मध्ये चालवायच्या.

तपशील: [remote build workflow](docs/REMOTE-BUILD.md), [assets](docs/asset-inventory.json). Account-specific deployment/rollback notes आणि private backups फक्त local project मध्ये आहेत; public repository मध्ये नाहीत.

Existing PowerShell मधील lossless source check: `./tools/Verify-Landing.ps1`.

Remote environment मिळाल्यावर Composer 2 वापरून dependencies resolve/install करा, `composer.lock` review करा, आणि isolated test environment मध्ये `php artisan test` चालवा. Live database वर tests चालवू नका.

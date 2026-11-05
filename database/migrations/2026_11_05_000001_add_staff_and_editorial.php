<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('editor')->index();
            $t->boolean('active')->default(true)->index();
            $t->timestamp('last_login_at')->nullable();
            $t->text('bio')->nullable();
            $t->string('credentials')->nullable();
            $t->softDeletes();
        });
        DB::table('users')->where('is_admin', true)->update(['role'=>'administrator']);
        DB::table('admin_setup')->update(['token_hash'=>null,'expires_at'=>null]);
        Schema::create('staff_locks', function(Blueprint $t) { $t->unsignedTinyInteger('id')->primary(); });
        DB::table('staff_locks')->insert(['id'=>1]);
        foreach (['categories','tags'] as $name) {
            Schema::create($name, function(Blueprint $t) {
                $t->id(); $t->string('name',120)->unique(); $t->string('slug',160)->unique();
                $t->text('description')->nullable(); $t->string('seo_title')->nullable();
                $t->text('seo_description')->nullable(); $t->timestamps();
            });
        }
        Schema::create('media', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained('users'); $t->string('filename')->unique();
            $t->string('mime',40); $t->unsignedInteger('bytes'); $t->unsignedInteger('width'); $t->unsignedInteger('height');
            $t->string('alt')->default(''); $t->string('caption')->nullable(); $t->string('credit')->nullable(); $t->timestamps();
        });
        Schema::create('posts', function(Blueprint $t) {
            $t->id(); $t->foreignId('author_id')->constrained('users');
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('title')->default(''); $t->string('slug',180)->unique(); $t->text('excerpt')->nullable();
            $t->longText('body_html')->nullable(); $t->longText('body_delta')->nullable();
            $t->string('status',20)->default('draft'); $t->timestamp('published_at')->nullable();
            $t->timestamp('content_updated_at')->nullable(); $t->boolean('comments_enabled')->default(false);
            $t->string('seo_title')->nullable(); $t->text('seo_description')->nullable(); $t->string('canonical_url',1000)->nullable();
            $t->boolean('noindex')->default(false); $t->boolean('nofollow')->default(false);
            $t->string('og_title')->nullable(); $t->text('og_description')->nullable();
            $t->string('og_image',1000)->nullable(); $t->string('focus_phrase')->nullable();
            $t->string('language',10)->default('en'); $t->string('schema_mode',12)->default('automatic');
            $t->longText('manual_schema')->nullable(); $t->text('sources')->nullable();
            $t->string('reviewer_name')->nullable(); $t->string('reviewer_credentials')->nullable(); $t->timestamp('reviewed_at')->nullable();
            $t->unsignedInteger('version')->default(1); $t->timestamps(); $t->softDeletes();
            $t->index(['status','published_at']); $t->index(['author_id','created_at']); $t->index(['category_id','published_at']);
        });
        Schema::create('post_tag', function(Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['post_id','tag_id']);
        });
        Schema::create('post_slugs', function(Blueprint $t) {
            $t->string('slug',180)->primary(); $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->timestamps();
        });
        Schema::create('post_revisions', function(Blueprint $t) {
            $t->id(); $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->constrained('users');
            $t->longText('snapshot'); $t->timestamps(); $t->index(['post_id','created_at']);
        });
        Schema::create('post_working_copies', function(Blueprint $t) {
            $t->id(); $t->foreignId('post_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->constrained('users');
            $t->unsignedInteger('base_version'); $t->longText('snapshot'); $t->timestamps(); $t->unique(['post_id','user_id']);
        });
        Schema::create('comments', function(Blueprint $t) {
            $t->id(); $t->foreignId('post_id')->constrained(); $t->string('name',100); $t->string('email',254);
            $t->text('body'); $t->string('status',20)->default('pending'); $t->string('previous_status',20)->nullable();
            $t->foreignId('moderator_id')->nullable()->constrained('users'); $t->timestamp('moderated_at')->nullable(); $t->timestamps();
            $t->index(['status','created_at']); $t->index(['post_id','status']);
        });
        Schema::create('blog_scripts', function(Blueprint $t) {
            $t->id(); $t->foreignId('post_id')->constrained(); $t->string('name'); $t->text('description')->nullable();
            $t->string('placement',10)->default('head'); $t->text('configuration')->nullable();
            $t->string('consent',20)->default('analytics'); $t->boolean('enabled')->default(false);
            $t->foreignId('changed_by')->constrained('users'); $t->timestamp('activated_at')->nullable(); $t->timestamps();
        });
        Schema::create('site_settings', function(Blueprint $t) { $t->string('key')->primary(); $t->text('value'); $t->timestamps(); });
        DB::table('site_settings')->insert(['key'=>'blog_scripts_disabled','value'=>'1','created_at'=>now(),'updated_at'=>now()]);
        Schema::create('activities', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained('users'); $t->string('action',100);
            $t->string('subject',200)->nullable(); $t->timestamps(); $t->index('created_at');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Editorial production upgrade is forward-only. Restore a verified backup in a coordinated recovery window.');
    }
};

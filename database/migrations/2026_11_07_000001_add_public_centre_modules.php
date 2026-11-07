<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
 public function up(): void {
  Schema::table('users',fn(Blueprint $t)=>$t->boolean('can_manage_inquiries')->default(false));
  Schema::create('services',function(Blueprint $t){
   $t->id();$t->string('name',180);$t->string('slug',180)->unique();$t->text('summary')->nullable();$t->longText('body_html')->nullable();$t->longText('body_delta')->nullable();
   $t->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();$t->string('duration')->nullable();$t->string('fees')->nullable();$t->boolean('offer_verified')->default(false);$t->string('cta_label')->default('Request a Consultation');
   $t->unsignedInteger('display_order')->default(0);$t->boolean('featured')->default(false);$t->string('status',20)->default('draft');
   $t->string('seo_title')->nullable();$t->text('seo_description')->nullable();$t->string('canonical_url',1000)->nullable();$t->boolean('noindex')->default(false);$t->string('og_title')->nullable();$t->text('og_description')->nullable();$t->string('og_image',1000)->nullable();$t->string('schema_mode',12)->default('automatic');$t->longText('manual_schema')->nullable();
   $t->foreignId('updated_by')->nullable()->constrained('users');$t->unsignedInteger('version')->default(1);$t->timestamps();$t->softDeletes();$t->index(['status','display_order']);
  });
  Schema::create('service_sections',function(Blueprint $t){$t->id();$t->foreignId('service_id')->constrained()->cascadeOnDelete();$t->string('kind',20);$t->string('heading')->nullable();$t->text('content');$t->unsignedInteger('display_order')->default(0);$t->index(['service_id','kind','display_order']);});
  Schema::create('service_slugs',function(Blueprint $t){$t->string('slug',180)->primary();$t->foreignId('service_id')->constrained();$t->timestamps();});
  Schema::create('public_pages',function(Blueprint $t){$t->id();$t->string('reference',120)->unique();$t->string('kind',30);$t->unsignedBigInteger('entity_id')->nullable();$t->string('title');$t->string('path',500)->unique();$t->string('status',20)->default('published');$t->timestamps();$t->index(['kind','entity_id']);});
  foreach(['home'=>['Home','/'],'story'=>['Our Story','/our-story'],'services'=>['Services','/services'],'blog'=>['Blog','/blog'],'reviews'=>['Reviews','/reviews'],'contact'=>['Request a Consultation','/contact'],'stories'=>['Client Stories','/client-stories'],'privacy'=>['Privacy','/privacy']] as $key=>$page) DB::table('public_pages')->insert(['reference'=>'core:'.$key,'kind'=>'core','title'=>$page[0],'path'=>$page[1],'status'=>'published','created_at'=>now(),'updated_at'=>now()]);
  // Set-based backfill is safe for any production records; never export fixture-specific inserts.
  DB::statement("INSERT INTO public_pages (reference,kind,entity_id,title,path,status,created_at,updated_at) SELECT CONCAT('post:',id),'post',id,title,CONCAT('/blog/',slug),CASE WHEN deleted_at IS NOT NULL THEN 'archived' ELSE status END,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP FROM posts");
  foreach(['categories'=>'category','tags'=>'tag'] as $table=>$kind) DB::statement("INSERT INTO public_pages (reference,kind,entity_id,title,path,status,created_at,updated_at) SELECT CONCAT('$kind:',id),'$kind',id,name,CONCAT('/blog/archive/$kind/',slug),'published',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP FROM $table");
  Schema::create('faqs',function(Blueprint $t){$t->id();$t->string('question',500);$t->char('question_key',64)->unique();$t->text('answer_html')->nullable();$t->string('category',100)->nullable();$t->string('status',20)->default('draft');$t->text('editorial_notes')->nullable();$t->foreignId('schema_page_id')->nullable()->constrained('public_pages')->nullOnDelete();$t->foreignId('updated_by')->nullable()->constrained('users');$t->timestamps();$t->softDeletes();$t->index(['status','category']);});
  Schema::create('faq_page',function(Blueprint $t){$t->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();$t->foreignId('public_page_id')->constrained('public_pages')->cascadeOnDelete();$t->unsignedInteger('display_order')->default(0);$t->primary(['faq_id','public_page_id']);$t->index(['public_page_id','display_order']);});
  Schema::create('client_stories',function(Blueprint $t){
   $t->id();$t->string('title');$t->string('display_name',120)->nullable();$t->boolean('anonymous')->default(true);$t->text('quotation');$t->text('original_quotation');$t->longText('story_html')->nullable();$t->string('source_type',20)->default('written');$t->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();$t->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();$t->date('received_at')->nullable();$t->boolean('featured')->default(false);$t->unsignedInteger('display_order')->default(0);$t->string('status',20)->default('draft');$t->string('consent_status',20)->default('pending');$t->timestamp('consented_at')->nullable();$t->boolean('identity_consent')->default(false);$t->boolean('media_consent')->default(false);$t->boolean('source_consent')->default(false);$t->text('private_notes')->nullable();$t->foreignId('updated_by')->nullable()->constrained('users');$t->timestamps();$t->softDeletes();$t->index(['status','consent_status','featured']);
  });
  Schema::create('reviews',function(Blueprint $t){
   $t->id();$t->uuid('submission_key')->unique();$t->char('duplicate_key',64)->unique();$t->string('display_name',100)->nullable();$t->boolean('anonymous')->default(true);$t->text('email');$t->unsignedTinyInteger('rating')->nullable();$t->text('original_text');$t->text('public_text')->nullable();$t->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();$t->string('source',30)->default('website');$t->boolean('publication_consent')->default(false);$t->string('consent_version',40);$t->timestamp('consented_at')->nullable();$t->string('status',20)->default('pending');$t->string('previous_status',20)->nullable();$t->boolean('featured')->default(false);$t->unsignedInteger('display_order')->default(0);$t->text('moderation_notes')->nullable();$t->foreignId('moderator_id')->nullable()->constrained('users');$t->timestamp('moderated_at')->nullable();$t->timestamps();$t->softDeletes();$t->index(['status','publication_consent','display_order']);$t->index(['service_id','rating']);
  });
  Schema::create('inquiries',function(Blueprint $t){
   $t->id();$t->string('reference',40)->unique();$t->uuid('submission_key')->unique();$t->char('duplicate_key',64)->unique();
   foreach(['full_name','email','mobile','message','preferred_window'] as $key)$t->text($key)->nullable();
   $t->string('interest',60);$t->string('session_preference',30);$t->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();$t->string('contact_method',15);$t->string('source',30);$t->string('status',20)->default('new');$t->foreignId('assigned_to')->nullable()->constrained('users');$t->string('consent_version',40);$t->timestamp('consented_at');$t->timestamp('contacted_at')->nullable();$t->timestamp('closed_at')->nullable();$t->timestamp('redacted_at')->nullable();$t->timestamps();$t->softDeletes();$t->index(['status','created_at']);$t->index(['assigned_to','status']);$t->index(['source','interest','session_preference']);
  });
  Schema::create('inquiry_followups',function(Blueprint $t){$t->id();$t->foreignId('inquiry_id')->constrained('inquiries');$t->foreignId('user_id')->constrained('users');$t->string('action',30);$t->text('note')->nullable();$t->timestamps();});
 }
 public function down(): void {throw new RuntimeException('Forward-only production upgrade; restore a verified backup with write reconciliation.');}
};

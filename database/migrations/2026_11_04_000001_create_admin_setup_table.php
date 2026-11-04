<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_setup', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->char('token_hash', 64)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        DB::table('admin_setup')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }
    public function down(): void
    {
        Schema::dropIfExists('admin_setup');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('license_no')->nullable();
            $table->string('license_file')->nullable();
            $table->string('authorize_person')->nullable();
            $table->string('trade_license_no')->nullable();
            $table->text('address')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('password')->nullable();
            $table->integer('allotment')->nullable();
            $table->integer('quota')->default(1000000);
            $table->integer('quota_used')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};

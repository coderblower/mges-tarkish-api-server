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
        Schema::create('candidate_medical_tests', function (Blueprint $table) {
            $table->id();
            $table->integer('enrolled_by')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('candidate_id')->nullable();
            $table->integer('medical_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('test_id')->nullable();
            $table->integer('min')->default(0);
            $table->integer('max')->default(0);
            $table->string('result')->nullable();
            $table->integer('status')->default(0);
            $table->string('report')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_medical_tests');
    }
};

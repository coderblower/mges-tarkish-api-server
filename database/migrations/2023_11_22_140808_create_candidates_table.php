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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('gender')->nullable();
            $table->string('verified_certificate');
            $table->string('marital_status')->nullable();
            $table->string('issued_by')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('dateOfIssue')->nullable();
            $table->string('visitRussiaNumber')->nullable();
            $table->string('russia_trip_date')->nullable();
            $table->string('hostOrganization')->nullable();
            $table->string('route_Journey')->nullable();
            $table->string('relativesStaying')->nullable();
            $table->string('refusedRussian')->nullable();
            $table->string('deportedRussia')->nullable();
            $table->string('spousesName')->nullable();
            $table->string('spouses_birth_date')->nullable();
            $table->string('full_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('country')->nullable();
            $table->string('religion')->nullable();
            $table->string('nid')->nullable();
            $table->string('nid_file')->nullable();
            $table->string('pif_file')->nullable();
            $table->string('passport')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('passport_file')->nullable();
            $table->integer('medical_center_id')->nullable();
            $table->integer('designation_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->text('academic')->nullable();
            $table->text('academic_file')->nullable();
            $table->text('experience')->nullable();
            $table->text('experience_file')->nullable();
            $table->text('training')->nullable();
            $table->text('training_file')->nullable();
            $table->boolean('medical_status')->default(false);
            $table->boolean('training_status')->default(false);
            $table->integer('is_active')->default(0);
            $table->string('photo')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('referred_by')->nullable();
            $table->string('approval_status')->default('pending');
            $table->string('note')->nullable();
            $table->timestamps();

            // Add the full-text index on 'passport'
            $table->fullText('passport');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

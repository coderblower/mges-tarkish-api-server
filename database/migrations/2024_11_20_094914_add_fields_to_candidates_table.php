<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCandidatesTable extends Migration
{
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('passport_all_page')->nullable()->comment('URL of the file');
            $table->string('cv')->nullable()->comment('URL of the CV file');
            $table->string('resume')->nullable()->comment('URL of the resume file');
            $table->string('birth_certificate')->nullable()->comment('URL of the birth certificate file');
        });
    }

    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['passport_all_page', 'cv', 'resume', 'birth_certificate']);
        });
    }
}

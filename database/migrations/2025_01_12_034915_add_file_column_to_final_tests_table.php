<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileColumnToFinalTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('final_tests', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('status'); // Column for storing the file path
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('final_tests', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
}

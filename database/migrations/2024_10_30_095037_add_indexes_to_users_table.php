<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add index on role_id
            $table->index('role_id');

            // Add index on created_by (for filtering by creator)
            $table->index('created_by');

            // If you have a separate candidates table, you might want to add indexes there as well
            // Assuming the candidate table has user_id and passport columns
            Schema::table('candidates', function (Blueprint $table) {
                $table->index('passport'); // Index for passport search
                $table->index('user_id');  // Index for foreign key user_id
            });
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes if the migration is rolled back
            $table->dropIndex(['role_id']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex(['passport']);
            $table->dropIndex(['user_id']);
        });
    }
}

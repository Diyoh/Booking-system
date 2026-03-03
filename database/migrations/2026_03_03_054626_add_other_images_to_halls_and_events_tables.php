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
        Schema::table('halls', function (Blueprint $table) {
            $table->json('other_images')->nullable()->after('image_url');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->json('other_images')->nullable()->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn('other_images');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('other_images');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radpostauth', function (Blueprint $table) {
            $table->string('nasipaddress', 15)->default('')->after('authdate');
        });
    }

    public function down(): void
    {
        Schema::table('radpostauth', function (Blueprint $table) {
            $table->dropColumn('nasipaddress');
        });
    }
};

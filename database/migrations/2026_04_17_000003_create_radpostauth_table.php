<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('radpostauth')) {
            return;
        }

        Schema::create('radpostauth', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 64)->default('');
            $table->string('pass', 64)->default('');
            $table->string('reply', 64)->default('');
            $table->string('nasipaddress', 15)->nullable();
            $table->string('callingstationid', 50)->nullable();
            $table->string('calledstationid', 50)->nullable();
            $table->timestamp('authdate')->useCurrent();

            $table->index('username');
            $table->index('authdate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radpostauth');
    }
};

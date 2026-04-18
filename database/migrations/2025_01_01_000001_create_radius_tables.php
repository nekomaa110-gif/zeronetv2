<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FreeRADIUS standard: check attributes per user
        Schema::create('radcheck', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->default('')->index();
            $table->string('attribute', 64)->default('');
            $table->char('op', 2)->default('==');
            $table->string('value', 253)->default('');
        });

        // FreeRADIUS standard: reply attributes per user
        Schema::create('radreply', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->default('')->index();
            $table->string('attribute', 64)->default('');
            $table->char('op', 2)->default('=');
            $table->string('value', 253)->default('');
        });

        // FreeRADIUS standard: user-to-group mapping
        Schema::create('radusergroup', function (Blueprint $table) {
            $table->string('username', 64)->default('')->index();
            $table->string('groupname', 64)->default('');
            $table->integer('priority')->default(1);
        });

        // FreeRADIUS standard: group check attributes (paket/profile)
        Schema::create('radgroupcheck', function (Blueprint $table) {
            $table->id();
            $table->string('groupname', 64)->default('')->index();
            $table->string('attribute', 64)->default('');
            $table->char('op', 2)->default('==');
            $table->string('value', 253)->default('');
        });

        // FreeRADIUS standard: group reply attributes (paket/profile)
        Schema::create('radgroupreply', function (Blueprint $table) {
            $table->id();
            $table->string('groupname', 64)->default('')->index();
            $table->string('attribute', 64)->default('');
            $table->char('op', 2)->default('=');
            $table->string('value', 253)->default('');
        });

        // FreeRADIUS standard: accounting records
        Schema::create('radacct', function (Blueprint $table) {
            $table->bigIncrements('radacctid');
            $table->string('acctsessionid', 64)->default('')->index();
            $table->string('acctuniqueid', 32)->default('')->unique();
            $table->string('username', 64)->default('')->index();
            $table->string('realm', 64)->nullable()->default('');
            $table->string('nasipaddress', 15)->default('')->index();
            $table->string('nasportid', 32)->nullable()->default('');
            $table->string('nasporttype', 32)->nullable()->default('');
            $table->timestamp('acctstarttime')->nullable()->index();
            $table->timestamp('acctupdatetime')->nullable();
            $table->timestamp('acctstoptime')->nullable()->index();
            $table->integer('acctinterval')->nullable();
            $table->bigInteger('acctsessiontime')->nullable()->unsigned();
            $table->string('acctauthentic', 32)->nullable()->default('');
            $table->string('connectinfo_start', 50)->nullable()->default('');
            $table->string('connectinfo_stop', 50)->nullable()->default('');
            $table->bigInteger('acctinputoctets')->nullable();
            $table->bigInteger('acctoutputoctets')->nullable();
            $table->string('calledstationid', 50)->default('');
            $table->string('callingstationid', 50)->default('');
            $table->string('acctterminatecause', 32)->default('');
            $table->string('servicetype', 32)->nullable()->default('');
            $table->string('framedprotocol', 32)->nullable()->default('');
            $table->string('framedipaddress', 15)->default('')->index();
            $table->string('framedipv6address', 45)->default('')->index();
            $table->string('framedipv6prefix', 45)->default('')->index();
            $table->string('framedinterfaceid', 44)->default('')->index();
            $table->string('delegatedipv6prefix', 45)->default('')->index();
        });

        // FreeRADIUS standard: NAS list
        Schema::create('nas', function (Blueprint $table) {
            $table->id();
            $table->string('nasname', 128)->index();
            $table->string('shortname', 32)->nullable();
            $table->string('type', 30)->default('other');
            $table->integer('ports')->nullable();
            $table->string('secret', 60)->default('secret');
            $table->string('server', 64)->nullable();
            $table->string('community', 50)->nullable();
            $table->string('description', 200)->nullable()->default('RADIUS Client');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas');
        Schema::dropIfExists('radacct');
        Schema::dropIfExists('radgroupreply');
        Schema::dropIfExists('radgroupcheck');
        Schema::dropIfExists('radusergroup');
        Schema::dropIfExists('radreply');
        Schema::dropIfExists('radcheck');
    }
};

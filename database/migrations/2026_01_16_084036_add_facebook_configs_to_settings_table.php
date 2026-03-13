<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacebookConfigsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('facebook_client_id')->nullable()->after('google_redirect_url');
            $table->string('facebook_client_secret')->nullable()->after('facebook_client_id');
            $table->string('facebook_redirect_url')->nullable()->after('facebook_client_secret');
            $table->boolean('facebook_login_enabled')->default(true)->after('google_login_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_client_id',
                'facebook_client_secret',
                'facebook_redirect_url',
                'facebook_login_enabled'
            ]);
        });
    }
}

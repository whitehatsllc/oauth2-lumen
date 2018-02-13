<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthAuthCodesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->integer('sessionId')->unsigned();
            $table->string('redirectUri');
            $table->integer('expireTime');

            $table->timestamps();

            $table->index('sessionId');

            $table->foreign('sessionId')
                    ->references('id')->on('mah_oauth_sessions')
                    ->onDelete('cascade');
        });

        Schema::table('mah_oauth_auth_codes', function ($table) {
            $table->renameColumn('created_at', 'createdAt');
            $table->renameColumn('updated_at', 'updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mah_oauth_auth_codes', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_auth_codes_sessionId_foreign');
        });
        Schema::drop('mah_oauth_auth_codes');
    }

}

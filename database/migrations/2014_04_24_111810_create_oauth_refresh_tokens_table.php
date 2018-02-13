<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthRefreshTokensTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 40)->unique();
            $table->string('accessTokenId', 40)->primary();
            $table->integer('expireTime');

            $table->timestamps();

            $table->foreign('accessTokenId')
                    ->references('id')->on('mah_oauth_access_tokens')
                    ->onDelete('cascade');
        });
        Schema::table('mah_oauth_refresh_tokens', function ($table) {
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
        Schema::table('mah_oauth_refresh_tokens', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_refresh_tokens_accessTokenId_foreign');
        });
        Schema::drop('mah_oauth_refresh_tokens');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthAccessTokenScopesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_access_token_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accessTokenId', 40);
            $table->string('scopeId', 255);

            $table->timestamps();

            $table->index('accessTokenId');
            $table->index('scopeId');

            $table->foreign('accessTokenId')
                    ->references('id')->on('mah_oauth_access_tokens')
                    ->onDelete('cascade');

            $table->foreign('scopeId')
                    ->references('id')->on('mah_oauth_scopes')
                    ->onDelete('cascade');
        });

        Schema::table('mah_oauth_access_token_scopes', function ($table) {
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
        Schema::table('mah_oauth_access_token_scopes', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_access_token_scopes_scopeId_foreign');
            $table->dropForeign('mah_oauth_access_token_scopes_accessTokenId_foreign');
        });
        Schema::drop('mah_oauth_access_token_scopes');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthAuthCodeScopesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_auth_code_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('authCodeId', 40);
            $table->string('scopeId', 255);

            $table->timestamps();

            $table->index('authCodeId');
            $table->index('scopeId');

            $table->foreign('authCodeId')
                    ->references('id')->on('mah_oauth_auth_codes')
                    ->onDelete('cascade');

            $table->foreign('scopeId')
                    ->references('id')->on('mah_oauth_scopes')
                    ->onDelete('cascade');
        });

        Schema::table('mah_oauth_auth_code_scopes', function ($table) {
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
        Schema::table('mah_oauth_auth_code_scopes', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_auth_code_scopes_authCodeId_foreign');
            $table->dropForeign('mah_oauth_auth_code_scopes_scopeId_foreign');
        });
        Schema::drop('mah_oauth_auth_code_scopes');
    }

}

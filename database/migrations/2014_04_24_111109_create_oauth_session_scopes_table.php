<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthSessionScopesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_session_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sessionId')->unsigned();
            $table->string('scopeId', 255;

            $table->timestamps();

            $table->index('sessionId');
            $table->index('scopeId');

            $table->foreign('sessionId')
                    ->references('id')->on('mah_oauth_sessions')
                    ->onDelete('cascade');

            $table->foreign('scopeId')
                    ->references('id')->on('mah_oauth_scopes')
                    ->onDelete('cascade');
        });

        Schema::table('mah_oauth_session_scopes', function ($table) {
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
        Schema::table('mah_oauth_session_scopes', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_session_scopes_sessionId_foreign');
            $table->dropForeign('mah_oauth_session_scopes_scopeId_foreign');
        });
        Schema::drop('mah_oauth_session_scopes');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientScopesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_client_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clientId', 40);
            $table->string('scopeId', 255);

            $table->timestamps();

            $table->index('clientId');
            $table->index('scopeId');

            $table->foreign('clientId')
                    ->references('id')->on('mah_oauth_clients')
                    ->onDelete('cascade');

            $table->foreign('scopeId')
                    ->references('id')->on('mah_oauth_scopes')
                    ->onDelete('cascade');
        });

        Schema::table('mah_oauth_client_scopes', function ($table) {
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
        Schema::table('mah_oauth_client_scopes', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_client_scopes_clientId_foreign');
            $table->dropForeign('mah_oauth_client_scopes_scopeId_foreign');
        });
        Schema::drop('mah_oauth_client_scopes');
    }

}

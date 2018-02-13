<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientGrantsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_client_grants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clientId', 40);
            $table->string('grantId', 40);
            $table->timestamps();

            $table->index('clientId');
            $table->index('grantId');

            $table->foreign('clientId')
                    ->references('id')->on('mah_oauth_clients')
                    ->onDelete('cascade')
                    ->onUpdate('no action');

            $table->foreign('grantId')
                    ->references('id')->on('mah_oauth_grants')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
        });

        Schema::table('mah_oauth_client_grants', function ($table) {
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
        Schema::table('mah_oauth_client_grants', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_client_grants_clientId_foreign');
            $table->dropForeign('mah_oauth_client_grants_grantId_foreign');
        });
        Schema::drop('mah_oauth_client_grants');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientEndpointsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_client_endpoints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clientId', 40);
            $table->string('redirectUri');

            $table->timestamps();

            $table->unique(['clientId', 'redirectUri']);

            $table->foreign('clientId')
                    ->references('id')->on('mah_oauth_clients')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });

        Schema::table('mah_oauth_client_endpoints', function ($table) {
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
        Schema::table('mah_oauth_client_endpoints', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_client_endpoints_clientId_foreign');
        });

        Schema::drop('mah_oauth_client_endpoints');
    }

}

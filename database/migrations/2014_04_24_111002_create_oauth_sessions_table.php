<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthSessionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mah_oauth_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clientId', 40);
            $table->string('ownerType')->default('user');
            $table->string('ownerId');
            $table->string('clientRedirectUri')->nullable();
            $table->timestamps();

            $table->index(['clientId', 'ownerType', 'ownerId']);

            $table->foreign('clientId')
                    ->references('id')->on('mah_oauth_clients')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
        Schema::table('mah_oauth_sessions', function ($table) {
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
        Schema::table('mah_oauth_sessions', function (Blueprint $table) {
            $table->dropForeign('mah_oauth_sessions_clientId_foreign');
        });
        Schema::drop('mah_oauth_sessions');
    }

}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Laravel\Passport\PassportSchema;

class CreateOauthClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            PassportSchema::increments($table, 'id');

            PassportSchema::userId($table, true);

            $table->string('name');
            $table->string('secret', 100);
            $table->text('redirect');
            $table->text('scopes')->nullable()->default(null);

            $table->boolean('public_client')->default(0);
            $table->boolean('personal_access_client')->default(0);
            $table->boolean('password_client')->default(0);
            $table->boolean('trusted_client')->default(0);

            $table->boolean('revoked');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_clients');
    }
}

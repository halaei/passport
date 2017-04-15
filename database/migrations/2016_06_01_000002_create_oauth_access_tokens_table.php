<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Laravel\Passport\PassportSchema;

class CreateOauthAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();

            PassportSchema::userId($table, true);

            $table->string('client_id', 64)->index();
            $table->foreign(['client_id'])->references('id')->on('oauth_clients')->onDelete('CASCADE');

            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');

            $table->timestamps();

            $table->dateTime('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_access_tokens');
    }
}

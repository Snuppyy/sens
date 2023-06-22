<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneOtpOtpSentAtRegistrationTokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 16)->index();
            $table->string('otp', 8)->nullable();
            $table->timestamp('otp_sent_at')->nullable();
            $table->string('registration_token', 32)->nullable()->unique();
            $table->timestamp('token_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'otp', 'otp_sent_at', 'registration_token']);
        });
    }
}

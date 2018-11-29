<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('level')->default(config('gamify.badge_default_level', 1));
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->primary(['user_id', 'badge_id']);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('badge_id');
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
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
}

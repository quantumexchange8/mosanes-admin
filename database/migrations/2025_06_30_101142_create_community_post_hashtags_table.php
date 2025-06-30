<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('community_post_hashtags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_post_id');
            $table->unsignedBigInteger('hashtag_id');
            $table->timestamps();

            $table->foreign('community_post_id')
                ->references('id')
                ->on('community_posts')
                ->onDelete('cascade');

            $table->foreign('hashtag_id')
                ->references('id')
                ->on('hashtags')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_hashtags');
    }
};

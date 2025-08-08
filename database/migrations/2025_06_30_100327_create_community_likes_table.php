<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('community_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('community_post_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('community_post_id')
                ->references('id')
                ->on('community_posts')
                ->onDelete('cascade');

            $table->unique(['user_id', 'community_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_likes');
    }
};

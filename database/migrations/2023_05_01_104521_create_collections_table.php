<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('center_lng');
            $table->double('center_lat');
            $table->double('radius');
            $table->integer('frequency');
            $table->double('period');
            $table->boolean('has_started')->default(false);
            $table->boolean('is_finished')->default(false);
            $table->boolean('is_violated')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign('agent_id');

            $table->dropColumn('agent_id');
        });
        Schema::dropIfExists('collections');
    }
};

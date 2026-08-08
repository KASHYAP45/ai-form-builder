<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('form_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->ipAddress('submitted_ip')->nullable();

            $table->json('data');

            $table->timestamps();

            $table->index(['form_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('submissions');
    }
}

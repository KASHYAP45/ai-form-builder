<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('submission_answers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('field_key');

            $table->string('field_label');

            $table->longText('value')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('submission_answers');
    }
}

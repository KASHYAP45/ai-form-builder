<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormsTable extends Migration
{
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->longText('schema');

            $table->enum('status', ['draft', 'published'])
                ->default('draft');

            $table->timestamps();
            
            /**
             * Add an index for commonly queried columns to improve performance.
             * This index includes the 'id', 'title', 'status', and 'created_at
             */
            $table->index(
                ['id', 'title', 'status', 'created_at'],
                'forms_active_created_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('forms');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailtemplatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emailtemplates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("projects_projectid");
            $table->integer("languages_idlanguages");
            $table->string("ettype");
            $table->string("subject");
            $table->text("body");
            $table->tinyInteger("etdefault");
            $table->timestamps();
            //            $table->foreign('projects_projectid')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emailtemplates');
    }
}

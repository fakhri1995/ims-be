<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string("title")->nullable();
            $table->string("article_type")->nullable();
            $table->text("description")->nullable();
            $table->text("content")->nullable();
            $table->string("page_path")->nullable();
            $table->string("tags")->nullable();
            $table->string("company_name")->nullable();
            $table->text("quote")->nullable();
            $table->string("author")->nullable();
            $table->string("job_title")->nullable();
            $table->string("meta_title")->nullable();
            $table->string("meta_description")->nullable();
            $table->string("title_id")->nullable();
            $table->text("description_id")->nullable();
            $table->text("content_id")->nullable();
            $table->string("page_path_id")->nullable();
            $table->string("tags_id")->nullable();
            $table->text("quote_id")->nullable();
            $table->string("job_title_id")->nullable();
            $table->string("meta_title_id")->nullable();
            $table->string("meta_description_id")->nullable();
            $table->unsignedBigInteger('user_id'); //refernece user_id
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}

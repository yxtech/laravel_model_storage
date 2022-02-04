<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'files';

    /**
     * Run the migrations.
     * @table files
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type')->nullable();
            $table->string('collection_name')->nullable()->comment('To categorize file(s) in collection');
            $table->string('name')->nullable();
            $table->text('url')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('disk')->nullable()->comment('local, public, s3, etc...');
            $table->unsignedBigInteger('size')->nullable()->comment('in Bytes');
            $table->json('custom_properties')->nullable()->comment('json / Tag the properties of the files');

            $table->index(["user_id"], 'fk_files_users1_idx');
            $table->nullableTimestamps();


            $table->foreign('user_id', 'fk_files_users1_idx')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}

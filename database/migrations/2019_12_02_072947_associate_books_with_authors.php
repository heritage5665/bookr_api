<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AssociateBooksWithAuthors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            //create the author_id column as an unsigned integer
            $table->integer('author_id')->after('id')->unsigned();

            //create a basic index for the author_id column
            $table->index('author_id');

            //create a foriegn key constraint and cascade on delete.
            $table->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            //drop the foriegn key first
            $table->dropForeign('books_author_id_foriegn');

            //Now drop the basic index
            $table->dropIndex('books_author_id_index');

            //safe to drop the column
            $table->dropColumn('author_id');
        });
    }
}

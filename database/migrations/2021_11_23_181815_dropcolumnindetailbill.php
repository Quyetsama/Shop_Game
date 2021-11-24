<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Dropcolumnindetailbill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_bills', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('totalcoin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string("quantity")->nullable()->after('product_id');
            $table->string("totalcoin")->nullable()->after('quantity');
        });
    }
}

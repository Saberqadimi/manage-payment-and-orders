<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('address_id')->constrained()->nullable();
            $table->foreignId('shipping_id')->constrained();
            $table->integer('shipping_price');
            $table->integer('tax')->default(0);
            $table->string('description');
            $table->date('shipping_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adm_orders');
    }
};

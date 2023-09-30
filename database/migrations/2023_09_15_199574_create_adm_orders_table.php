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
            $table->foreignId('adm_addresses_id')->constrained('adm_addresses')->nullable();
            $table->foreignId('adm_shippings_id')->constrained('adm_shippings')->nullable();
            $table->integer('shipping_price');
            $table->integer('order_price')->nullable();
            $table->integer('payment_price')->nullable();
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

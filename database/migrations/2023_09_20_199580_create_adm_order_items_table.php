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
        Schema::create('adm_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('adm_orders');
            $table->foreignId('inventory_id')->constrained('adm_inventories');
            $table->integer('quantity');
            $table->integer('price');
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
        Schema::dropIfExists('adm_order_items');
    }
};

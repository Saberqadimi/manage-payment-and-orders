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
        Schema::create('adm_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('description')->nullable();
            $table->json('transaction')->nullable();
            $table->string('transaction_id')->unique();
            $table->integer('amount');
            $table->string('driver')->nullable();
            $table->string('reference_id')->nullable();
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
        Schema::dropIfExists('adm_payments');
    }
};

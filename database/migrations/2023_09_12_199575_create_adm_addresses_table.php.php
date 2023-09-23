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
        Schema::create('adm_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('address');
            $table->string('description');
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
        Schema::dropIfExists('adm_addresses');
    }
};

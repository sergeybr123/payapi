<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('payment_id');
            $table->string('email');
            $table->unsignedInteger('amount')->default(0);
            $table->string('crc')->nullable();
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['active', 'paid', 'complete'])->default('active');
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
        Schema::dropIfExists('invoices');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percentage or flat
            $table->decimal('value', 12, 2);
            $table->decimal('min_spent', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Seed default vouchers
        DB::table('vouchers')->insert([
            [
                'code' => 'JOKOWI',
                'type' => 'percentage',
                'value' => 10.00,
                'min_spent' => 100000.00,
                'max_discount' => 30000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRAWOWO',
                'type' => 'flat',
                'value' => 50000.00,
                'min_spent' => 250000.00,
                'max_discount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FUFUFAFA',
                'type' => 'percentage',
                'value' => 50.00,
                'min_spent' => 0.00,
                'max_discount' => 100000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

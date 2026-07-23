<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->json('before_data');
            $table->json('after_data');
            $table->text('reason')->comment('変更理由');
            $table->tinyInteger('status')->default(0)->comment('0:承認待ち, 1:承認済み');
            $table->foreignId('applicant_id')->nullable()->constrained('users')->comment('申請したユーザーのid');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('承認したユーザーのid');
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
        Schema::dropIfExists('attendance_requests');
    }
}

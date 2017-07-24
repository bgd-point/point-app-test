<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormulirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulir', function ($table) {
            $table->increments('id');

            $table->timestamp('form_date')->useCurrent();
            $table->string('form_number')->nullable()->unique();
            $table->string('barcode', 12)->nullable()->default(null)->unique();
            $table->text('notes')->nullable();
            
            // Reason for editing form
            $table->text('edit_notes')->nullable();

            $table->timestamp('request_approval_at')->nullable();
            $table->string('request_approval_token')->nullable();

            $table->tinyInteger('approval_status')->default(0); // -1 rejected | 0 pending | 1 approved
            $table->timestamp('approval_at')->useCurrent();
            $table->text('approval_message');
            $table->integer('approval_to')->unsigned()->index();
            $table->foreign('approval_to')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('archived')->nullable()->default(null); // null active | %FORM NUMBER% archived (form replaced / edited with other)
            $table->tinyInteger('form_status')->default(0); // -1 cancel | 0 open | 1 done
            
            $table->nullableTimestamps();

            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamp('canceled_at')->nullable();
            $table->integer('canceled_by')->unsigned()->nullable()->index();
            $table->foreign('canceled_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('formulirable_id')->index()->nullable();
            $table->string('formulirable_type')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('formulir');
    }
}

  <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('classroutines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('room_id')->nullable();
                $table->string('day');
                $table->string('start_time');
                $table->string('end_time');
                $table->enum('status',['Active','Inactive','Deleted'])->default('Active');
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                $table->foreign('room_id')->references('id')->on('classrooms')->onDelete('cascade');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('classroutines');
        }
    };

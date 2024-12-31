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
            Schema::create('classnotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->string('date');
                $table->longtext('class_note')->nullable();
                $table->string('note_photo')->nullable();
                $table->string('note_pdf')->nullable();
                $table->enum('status',['Active','Inactive','Deleted'])->default('Active');
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('session_id')->references('id')->on('sessionsyear')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('classnotes');
        }
    };

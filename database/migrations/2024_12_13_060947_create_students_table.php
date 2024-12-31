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
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->string('student_id');
                $table->string('name');
                $table->string('father_name');
                $table->string('mother_name');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('address');
                $table->string('date_of_birth');
                $table->date('admission_date');
                $table->string('photo')->nullable();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->string('password');
                $table->enum('status',['Active','Inactive','Deleted'])->default('Active');
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('session_id')->references('id')->on('sessionsyear')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('students');
        }
    };

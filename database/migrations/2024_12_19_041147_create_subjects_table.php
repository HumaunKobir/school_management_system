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
            Schema::create('subjects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->string('name');
                $table->string('subject_code');
                $table->enum('status',['Active','Inactive','Deleted'])->default('Active');
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
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
            Schema::dropIfExists('subjects');
        }
    };

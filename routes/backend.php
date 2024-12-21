<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\ClassController;
use App\Http\Controllers\Backend\TeacherController;
use App\Http\Controllers\Backend\ParentController;
use App\Http\Controllers\Backend\SubjectController;
use App\Http\Controllers\Backend\ExamController;
use App\Http\Controllers\Backend\ResultController;
use App\Http\Controllers\Backend\LibraryController;
use App\Http\Controllers\Backend\FeesController;
use App\Http\Controllers\Backend\AttendanceController;
use App\Http\Controllers\Backend\EventController;
use App\Http\Controllers\Backend\ClassRoutineController;
use App\Http\Controllers\Backend\GradeController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\ScholarshipController;
use App\Http\Controllers\Backend\SalaryController;
use App\Http\Controllers\Backend\DonationController;
use App\Http\Controllers\Backend\NoticeController;
use App\Http\Controllers\Backend\AssignmentController;
use App\Http\Controllers\Backend\ClassNoteController;
use App\Http\Controllers\Backend\SectionController;
use App\Http\Controllers\Backend\ClassRoomController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


	    use App\Http\Controllers\Backend\StudentController;


	use App\Http\Controllers\Backend\ClassesController;


	use App\Http\Controllers\Backend\GroupController;


	//don't remove this comment from route namespace

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LoginController::class, 'loginPage'])->name('home')->middleware('AuthCheck');

Route::get('/cache-clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('clear-compiled');
    Artisan::call('optimize:clear');
    Artisan::call('storage:link');
    Artisan::call('optimize');
    session()->flash('message', 'System Updated Successfully.');

    return redirect()->route('home');
});

Route::group(['as' => 'auth.'], function () {
    Route::get('/login', [LoginController::class, 'loginPage'])->name('login2')->middleware('AuthCheck');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => 'AdminAuth'], function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::resource('admin', AdminController::class);
    Route::get('admin/{id}/status/{status}/change', [AdminController::class, 'changeStatus'])->name('admin.status.change');

    // for role
    Route::resource('role', RoleController::class);

    // for permission entry
    Route::resource('permission', PermissionController::class);


        //for Teacher
    Route::resource('teacher', TeacherController::class);
    Route::get('teacher/{id}/status/{status}/change', [TeacherController::class, 'changeStatus'])->name('teacher.status.change');


        //for Parent
    Route::resource('parent', ParentController::class);
    Route::get('parent/{id}/status/{status}/change', [ParentController::class, 'changeStatus'])->name('parent.status.change');


        //for Subject
    Route::resource('subject', SubjectController::class);
    Route::get('subject/{id}/status/{status}/change', [SubjectController::class, 'changeStatus'])->name('subject.status.change');
    Route::get('groups/byClass/{classId}', [SubjectController::class, 'getGroupsByClass'])->name('groups.byClass');

        //for Exam
    Route::resource('exam', ExamController::class);
    Route::get('exam/{id}/status/{status}/change', [ExamController::class, 'changeStatus'])->name('exam.status.change');


        //for Result
    Route::resource('result', ResultController::class);
    Route::get('result/{id}/status/{status}/change', [ResultController::class, 'changeStatus'])->name('result.status.change');


        //for Library
    Route::resource('library', LibraryController::class);
    Route::get('library/{id}/status/{status}/change', [LibraryController::class, 'changeStatus'])->name('library.status.change');


        //for Fees
    Route::resource('fees', FeesController::class);
    Route::get('fees/{id}/status/{status}/change', [FeesController::class, 'changeStatus'])->name('fees.status.change');


        //for Attendance
    Route::resource('attendance', AttendanceController::class);
    Route::get('attendance/{id}/status/{status}/change', [AttendanceController::class, 'changeStatus'])->name('attendance.status.change');


        //for Event
    Route::resource('event', EventController::class);
    Route::get('event/{id}/status/{status}/change', [EventController::class, 'changeStatus'])->name('event.status.change');


        //for ClassRoutine
    Route::resource('classroutine', ClassRoutineController::class);
    Route::get('classroutine/{id}/status/{status}/change', [ClassRoutineController::class, 'changeStatus'])->name('classroutine.status.change');

    Route::get('groups/byClass/{classId}', [ClassRoutineController::class, 'getGroupsByClass'])->name('groups.byClass');
    Route::get('sections/byClass/{classId}', [ClassRoutineController::class, 'getSectionsByClass'])->name('sections.byClass');
    Route::get('subjects/byGroup/{groupId}', [ClassRoutineController::class, 'getSubjectsByGroup'])->name('subjects.byGroup');


        //for Grade
    Route::resource('grade', GradeController::class);
    Route::get('grade/{id}/status/{status}/change', [GradeController::class, 'changeStatus'])->name('grade.status.change');


        //for Report
    Route::resource('report', ReportController::class);
    Route::get('report/{id}/status/{status}/change', [ReportController::class, 'changeStatus'])->name('report.status.change');


        //for Payment
    Route::resource('payment', PaymentController::class);
    Route::get('payment/{id}/status/{status}/change', [PaymentController::class, 'changeStatus'])->name('payment.status.change');


        //for Scholarship
    Route::resource('scholarship', ScholarshipController::class);
    Route::get('scholarship/{id}/status/{status}/change', [ScholarshipController::class, 'changeStatus'])->name('scholarship.status.change');


        //for Salary
    Route::resource('salary', SalaryController::class);
    Route::get('salary/{id}/status/{status}/change', [SalaryController::class, 'changeStatus'])->name('salary.status.change');


        //for Donation
    Route::resource('donation', DonationController::class);
    Route::get('donation/{id}/status/{status}/change', [DonationController::class, 'changeStatus'])->name('donation.status.change');


        //for Notice
    Route::resource('notice', NoticeController::class);
    Route::get('notice/{id}/status/{status}/change', [NoticeController::class, 'changeStatus'])->name('notice.status.change');


        //for Assignment
    Route::resource('assignment', AssignmentController::class);
    Route::get('assignment/{id}/status/{status}/change', [AssignmentController::class, 'changeStatus'])->name('assignment.status.change');


        //for ClassNote
    Route::resource('classnote', ClassNoteController::class);
    Route::get('classnote/{id}/status/{status}/change', [ClassNoteController::class, 'changeStatus'])->name('classnote.status.change');


        //for Section
    Route::resource('section', SectionController::class);
    Route::get('section/{id}/status/{status}/change', [SectionController::class, 'changeStatus'])->name('section.status.change');


        //for ClassRoom
    Route::resource('classroom', ClassRoomController::class);
    Route::get('classroom/{id}/status/{status}/change', [ClassRoomController::class, 'changeStatus'])->name('classroom.status.change');



	//for Student
    Route::resource('student', StudentController::class);
    Route::get('student/{id}/status/{status}/change', [StudentController::class, 'changeStatus'])->name('student.status.change');
    Route::get('section/byClass/{classId}', [StudentController::class, 'getSectionsByClass'])->name('section.byClass');
    Route::get('group/byGroup/{classId}', [StudentController::class, 'getGroupsByClass'])->name('group.byClass');


	    //for Classes
    Route::resource('classes', ClassesController::class);
    Route::get('classes/{id}/status/{status}/change', [ClassesController::class, 'changeStatus'])->name('classes.status.change');


	    //for Group
    Route::resource('group', GroupController::class);
    Route::get('group/{id}/status/{status}/change', [GroupController::class, 'changeStatus'])->name('group.status.change');


	//don't remove this comment from route body
});
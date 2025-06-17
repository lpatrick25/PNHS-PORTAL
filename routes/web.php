<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\ClassRecordController;
use App\Http\Controllers\Navigation\AdminNavigationController;
use App\Http\Controllers\Navigation\PrincipalNavigationController;
use App\Http\Controllers\Navigation\StudentNavigationController;
use App\Http\Controllers\Navigation\TeacherNavigationController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentStatusController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherSubjectLoadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/password/change', [LoginController::class, 'showChangePasswordForm'])->name('password.change');
Route::put('/passwordChange/{id}', [PasswordController::class, 'update'])->name('password.update');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');


Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/dashboard', [AdminNavigationController::class, 'viewDashboard'])->name('admin.dashboard');

    Route::get('/viewStudents', [AdminNavigationController::class, 'viewStudents'])->name('admin.students');
    Route::get('/addStudent', [AdminNavigationController::class, 'addStudent'])->name('admin.addStudent');
    Route::get('/updateStudent/{studentLRN}', [AdminNavigationController::class, 'updateStudent'])->name('admin.updateStudent');

    Route::get('/viewTeachers', [AdminNavigationController::class, 'viewTeachers'])->name('admin.teachers');
    Route::get('/addTeacher', [AdminNavigationController::class, 'addTeacher'])->name('admin.addTeacher');
    Route::get('/updateTeacher/{teacherId}', [AdminNavigationController::class, 'updateTeacher'])->name('admin.updateTeacher');

    Route::get('/viewPrincipals', [AdminNavigationController::class, 'viewPrincipals'])->name('admin.principals');
    Route::get('/addPrincipal', [AdminNavigationController::class, 'addPrincipal'])->name('admin.addPrincipal');
    Route::get('/updatePrincipal/{principalId}', [AdminNavigationController::class, 'updatePrincipal'])->name('admin.updatePrincipal');

    Route::get('/viewAdvisers', [AdminNavigationController::class, 'viewAdvisers'])->name('admin.advisers');
    Route::get('/addAdviserStudent/{adviser}', [AdminNavigationController::class, 'addAdviserStudent'])->name('admin.addAdviserStudent');

    Route::get('/viewSubjects', [AdminNavigationController::class, 'viewSubjects'])->name('admin.subjects');

    Route::get('/viewTeacherSubjects', [AdminNavigationController::class, 'viewTeacherSubjects'])->name('admin.teacherSubjects');
    Route::get('/getTeacherList', [AdminNavigationController::class, 'getTeacherList'])->name('admin.getTeacherList');
    Route::get('/viewTeacherSubjectLoad/{teacherId}', [AdminNavigationController::class, 'viewTeacherSubjectLoad'])->name('admin.viewTeacherSubjectLoad');
    Route::get('/getSectionsByGradeLevel', [AdminNavigationController::class, 'getSectionsByGradeLevel'])->name('admin.getSectionsByGradeLevel');
});

Route::group(['prefix' => 'teacher', 'middleware' => ['auth', 'role:teacher']], function () {
    Route::get('/dashboard', [TeacherNavigationController::class, 'viewDashboard'])->name('teacher.dashboard');
    Route::get('/advisory', [TeacherNavigationController::class, 'viewAdvisory'])->name('teacher.advisory');
    Route::get('/advisory-students', [TeacherNavigationController::class, 'viewAdvisoryStudents'])->name('teacher.advisory-students');
    Route::get('/subjects', [TeacherNavigationController::class, 'viewTeacherSubject'])->name('teacher.subjects');
    Route::get('/subjects-teacher-load', [TeacherNavigationController::class, 'viewTeacherSubjectLoad'])->name('teacher.subjects-teacher-load');
    Route::get('/subject-students', [TeacherNavigationController::class, 'viewSubjectStudents'])->name('teacher.subject-students');
    Route::get('/attendance', [TeacherNavigationController::class, 'viewAttendanceTeacher'])->name('teacher.attendance');
    Route::get('/class-records', [TeacherNavigationController::class, 'viewClassRecordTeacher'])->name('teacher.class-records');
    Route::get('/report-card', [TeacherNavigationController::class, 'viewReportCardTeacher'])->name('teacher.report-card');
    Route::get('/profile', [TeacherNavigationController::class, 'viewProfile'])->name('teacher.profile');
    Route::get('/subject-loads/{teacherId}', [TeacherSubjectLoadController::class, 'viewTeacherLoads'])->name('teacherSubjectLoads.viewTeacherLoads');
});

Route::group(['prefix' => 'student', 'middleware' => ['auth', 'role:student']], function () {
    Route::get('/dashboard', [StudentNavigationController::class, 'viewDashboard'])->name('student.dashboard');
    // Add other student routes here
    Route::get('/subjects', [StudentController::class, 'subjects'])->name('student.subjects');
    Route::get('/grades', [StudentNavigationController::class, 'grades'])->name('student.grades');
    Route::get('/reportCards/getStudentGrades/{gradeLevel}/{section}', [StudentNavigationController::class, 'getStudentGrades'])->name('student.getStudentGrades');
    Route::get('/profile', [StudentController::class, 'profile'])->name('student.profile');
    Route::get('/attendances', [StudentNavigationController::class, 'viewAttendances'])->name('student.attendance');
    Route::get('/attendances/getStudentAttendance', [StudentNavigationController::class, 'getStudentAttendance'])->name('student.getStudentAttendance');
    Route::get('/class-records', [StudentNavigationController::class, 'classRecords'])->name('student.class-records');
    Route::get('/subjects', [StudentNavigationController::class, 'getStudentSubject'])->name('getStudentSubject');
    Route::get('/viewStudentRecords/{subjectListing}', [StudentNavigationController::class, 'viewStudentRecords'])->name('viewStudentRecords');
});

Route::group(['prefix' => 'principal', 'middleware' => ['auth', 'role:principal']], function () {
    Route::get('/dashboard', [PrincipalNavigationController::class, 'viewDashboard'])->name('principal.dashboard');
    // Add other principal routes here
    Route::get('/viewStudents', [PrincipalNavigationController::class, 'viewStudents'])->name('principal.students');
    Route::get('/viewTeachers', [PrincipalNavigationController::class, 'viewTeachers'])->name('principal.teachers');
});

// Address
Route::prefix('/address')->group(function () {
    Route::get('/getProvinces/{regionCode}', [AddressController::class, 'getProvinces'])->name('getProvinces');
    Route::get('/getMunicipalities/{provinceCode}', [AddressController::class, 'getMunicipalities'])->name('getMunicipalities');
    Route::get('/getBrgys/{municipalityCode}', [AddressController::class, 'getBrgys'])->name('getBrgys');
    Route::get('/getZipCode/{municipalityCode}', [AddressController::class, 'getZipCode'])->name('getZipCode');
});

Route::get('/profile', [ProfileController::class, 'viewProfile'])->name('viewProfile');

Route::resource('school-years', SchoolYearController::class);

Route::resource('students', StudentController::class);
Route::post('/students/updateImage/{studentLRN}', [StudentController::class, 'updateAvatar'])->name('students.updateAvatar');

Route::resource('teachers', TeacherController::class);
Route::post('/teachers/updateImage/{teacherId}', [TeacherController::class, 'updateAvatar'])->name('teachers.updateAvatar');

Route::resource('principals', PrincipalController::class);
Route::post('/principals/updateImage/{principalId}', [PrincipalController::class, 'updateAvatar'])->name('principals.updateAvatar');

Route::resource('advisers', AdviserController::class);
Route::get('/advisers/teacher/{teacherId}', [AdviserController::class, 'getByTeacherId'])->name('advisers.getByTeacherId');

Route::resource('studentStatuses', StudentStatusController::class);
Route::get('/studentStatuses/getAdviserStudents/{adviserId}/{schoolYearId}', [StudentStatusController::class, 'getAdviserStudents'])->name('studentStatuses.getAdviserStudents');
Route::get('/studentStatuses/not-enrolled/{schoolYearId}', [StudentStatusController::class, 'getNotEnrolled'])->name('studentStatuses.getNotEnrolled');

Route::resource('/subjects', SubjectController::class);

Route::resource('/teacherSubjectLoads', TeacherSubjectLoadController::class);

Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show');
Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
Route::post('/attendances/rfid', [AttendanceController::class, 'processRfid'])->name('attendances.processRfid');
Route::get('/attendance/teacher', [AttendanceController::class, 'viewAttendanceTeacher'])->name('viewAttendanceTeacher');
Route::get('/attendances/by-subject-load/{subject_load_id}', [AttendanceController::class, 'bySubjectLoad'])->name('attendances.bySubjectLoad');
Route::get('/attendances/by-date/{subject_load_id}/{attendance_date}', [AttendanceController::class, 'byDate'])->name('attendances.byDate');
Route::post('/attendances/generate', [AttendanceController::class, 'generate'])->name('attendances.generate');

// Class Records Routes
Route::get('/class-records', [ClassRecordController::class, 'index'])->name('classRecords.index');
Route::post('/class-records', [ClassRecordController::class, 'store'])->name('classRecords.store');
Route::put('/class-records/{id}', [ClassRecordController::class, 'update'])->name('classRecords.update');
Route::post('/class-records/score', [ClassRecordController::class, 'updateScore'])->name('classRecords.updateScore');
Route::post('/class-records/total-score', [ClassRecordController::class, 'updateTotalScore'])->name('classRecords.updateTotalScore');
Route::post('/class-records/generate', [ClassRecordController::class, 'generate'])->name('classRecords.generate');
Route::get('/class-records/teacher', [ClassRecordController::class, 'viewClassRecordTeacher'])->name('viewClassRecordTeacher');
Route::get('/class-records/subject/{subjectLoadId}', [ClassRecordController::class, 'viewClassRecord'])->name('viewClassRecord');
Route::get('/class-records/by-subject-load', [ClassRecordController::class, 'bySubjectLoad'])->name('classRecords.bySubjectLoad');
Route::get('/class-records/export/{subjectLoadId}', [ClassRecordController::class, 'export'])->name('classRecords.export');
Route::get('/class-records/download/{subjectLoadId}/{fileName}', [ClassRecordController::class, 'downloadExcel'])->name('classRecords.download');

// Report Card Routes
Route::post('/reportCards/{studentId}/{schoolYearId}', [ReportCardController::class, 'generateReportCard'])->name('reportCards.generateReportCard');
Route::get('/reportCards/download/{studentId}/{schoolYearId}', [ReportCardController::class, 'downloadReportCard'])->name('reportCards.downloadReportCard');

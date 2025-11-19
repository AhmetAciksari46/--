<?php

use App\Http\Controllers\Api\AuthController;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserProfile;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\PackageController;

use App\Http\Controllers\School\User\SchoolStudentController;
use App\Http\Controllers\School\User\TeacherProfileController;

use App\Http\Controllers\School\User\SchoolStudentProfileController;
use App\Http\Controllers\School\User\ManagerProfileController;
use App\Http\Controllers\School\User\ManagerController;

use App\Http\Controllers\School\User\TeacherController;

use App\Http\Controllers\School\General\SchoolController;
use App\Http\Controllers\School\General\ClassModelController;
use App\Http\Controllers\School\Week\SchoolWeekController;
use App\Http\Controllers\School\Week\SchoolWeekDayController;
use App\Http\Controllers\{
    UserController,
    SubscriptionController,
    SchoolHasGradeController,
    ClassScheduleController,
    AdditionalClassRoomController,
    StudentCurriculumOverrideController,
    AttendanceController,
    LessonSessionController,
    ContentController,
    SubjectController,
    PackageWeekGradeRuleController,
    GradeController,
    TeacherSubjectController
};
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\School\User\StudentParentController;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);



Route::middleware('auth:sanctum')->group(function () {
    Route::prefix("me")->group(function () {

        Route::get('/', function () {
            return response()->json(auth()->user());
        });
        Route::post("/logout", [AuthController::class, "logout"]);

        // Route::prefix("teacher")->middleware("role:teacher")->group(function () {
        //     Route::put("/updateprofile", [TeacherController::class, "update"]);
        //     Route::put("/updateprofilesettings", [TeacherProfileController::class, "updateprofilesettings"]);
        //     Route::get("/getprofilesettings", [TeacherProfileController::class, "getprofilesettings"]);
        // });

        // // School Student kendi profili ->Sadece ad syad ve şifre değişebilir// diğer bilgileri manager yada teacher değiştirir
        // Route::prefix("schoolstudent")->middleware("role:schoolstudent")->group(function () {
        //     Route::put("/updateprofile", [SchoolStudentController::class, "updateprofile"]);
        //     Route::get("/getprofilesettings", [SchoolStudentProfileController::class, "getprofilesettings"]);
        // });

        // // individual Student kendi profili
        // Route::prefix("individualstudent")->middleware("role:individualstudent")->group(function () {
        //     Route::put("/updateprofile", [IndividualStudentController::class, "updateprofile"]);
        //     Route::put("/updateprofilesettings", [IndividualStudentProfileController::class, "updateprofilesettings"]);
        //     Route::get("/getprofilesettings", [IndividualStudentProfileController::class, "getprofilesettings"]);
        // });
    });


    Route::middleware('role:manager')->prefix('manager')->group(function () {
        Route::get('/me', [ManagerController::class, 'getManagerUser']); //kendi user tür bilgilerini getir
        Route::put('/me', [ManagerController::class, 'updateManagerUser']); //kendi user tür bilgilerini getir
        Route::get('/profile/me', [ManagerProfileController::class, 'getManagerProfile']); //kendi managerprofil tür bilgilerini getir
        Route::put('/profile/me', [ManagerProfileController::class, 'updateManagerProfile']); //kendi managerprofil tür bilgilerini güncelle
        Route::post('/profile', [ManagerProfileController::class, 'storeManagerProfile']); //kendi managerprofil tür bilgilerini oluştur
    });


    // SUBJECTS (Admin only)
    Route::middleware('role:admin')->prefix('admin/subjectssss')->group(function () {
        Route::get('/', [SubjectController::class, 'index']);
        Route::post('/', [SubjectController::class, 'store']);
        Route::put('/{id}', [SubjectController::class, 'update']);
        Route::delete('/{id}', [SubjectController::class, 'destroy']);
    });

    // TEACHER SUBJECTS (Admin + Manager)
    Route::prefix('teacher-subjects')->group(function () {
        Route::get('/', [TeacherSubjectController::class, 'index']);
        Route::post('/', [TeacherSubjectController::class, 'store']);
        Route::delete('/{id}', [TeacherSubjectController::class, 'destroy']);
    });


    Route::middleware('role:admin')
        ->prefix('admin')->group(function () {
            Route::get('school-has-grades', [SchoolHasGradeController::class, 'index']);
            Route::post('school-has-grades', [SchoolHasGradeController::class, 'store']);
            Route::delete('school-has-grades/{id}', [SchoolHasGradeController::class, 'destroy']);
            Route::get('school-has-grades/by-school/{school_id}', [SchoolHasGradeController::class, 'getBySchoolId']);

            Route::apiResource('packages', PackageController::class);
            Route::apiResource('packages.grade-rules', PackageWeekGradeRuleController::class);
            // Abonelikleri listele
            Route::get('subscriptions', [SubscriptionController::class, 'index']);

            // Yeni abonelik oluştur (manuel)
            Route::post('subscriptions/create', [SubscriptionController::class, 'store']);
            //admin yeni üyelik oluştururken bunla
            Route::post('subscriptions/unlimited', [SubscriptionController::class, 'createUnlimited']);

            // Belirli aboneliği göster
            Route::get('subscriptions/{Subscription}', [SubscriptionController::class, 'show']);

            // Paket yükseltme (upgrade)
            Route::post('subscriptions/{id}/upgrade', [SubscriptionController::class, 'upgrade']);

            // Abonelik yenileme (manuel renew)
            Route::post('subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);

            // Aboneliği iptal et
            Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

            // Ödeme bilgisi güncelle
            Route::post('subscriptions/{id}/payment', [SubscriptionController::class, 'updatePayment']);

            // Abonelik pasif yap
            Route::post('subscriptions/{id}/deactivate', [SubscriptionController::class, 'deactivate']);

            // Abonelik aktif yap
            Route::post('subscriptions/{id}/activate', [SubscriptionController::class, 'activate']);


            Route::post("/managerregister", [AuthController::class, "managerregister"]);
            Route::get('/manager/{user_id}/profile', [ManagerProfileController::class, 'getManagerProfileById']);
            Route::put('/manager/{user_id}/profile', [ManagerProfileController::class, 'updateManagerProfileById']);
            Route::get('/manager/{id}', [ManagerController::class, 'getManagerUserById']);
            Route::put('/manager/{id}', [ManagerController::class, 'updateManagerUserById']);
            Route::post('/manager/{user_id}/profile', [ManagerProfileController::class, 'storeManagerProfileById']);

            Route::prefix('grades')->group(function () {
                Route::get('/', [GradeController::class, 'index']);
                Route::post('/store', [GradeController::class, 'store']);
                Route::put('/{id}', [GradeController::class, 'update']);
                Route::delete('/{id}', [GradeController::class, 'destroy']);
            });
            Route::resource('contents', ContentController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('branches', BranchController::class);
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

            Route::apiResource('subjects', SubjectController::class);
            Route::prefix("school")->group(function () { //by aadmin
                Route::get('/getschools', [SchoolController::class, 'schoollist']);
                Route::get('/getschoolbyid/{school}', [SchoolController::class, 'getSchool']);
                Route::post('/createschool', [SchoolController::class, 'createschool']);
                Route::put('/updateschool/{school}', [SchoolController::class, 'updateschool']);
                Route::delete('/deleteschool/{school}', [SchoolController::class, 'deleteschool']);
            });


            Route::prefix("teachers")->group(function () { //by aadmin
                Route::get('/', [AdminTeacherController::class, 'index']); // all teachers
                Route::get('/getteachersbyschoolid/{school}', [AdminTeacherController::class, 'getbySchoolId']); // o okuldaki öğretmenlerin tamamını getir
                Route::get('/{teacher}', [AdminTeacherController::class, 'show']); // getTeacherById
                Route::post('/', [AdminTeacherController::class, 'store']); // createTeacher
                Route::put('/{teacher}', [AdminTeacherController::class, 'update']); // updateTeacher
                Route::delete('/{teacher}', [AdminTeacherController::class, 'destroy']); // deleteTeacher
                Route::delete('/{teacher}/permissions', [AdminTeacherController::class, 'removePermissions']); // removeTeacherPermissions
                Route::get('/{teacher}/permissions', [AdminTeacherController::class, 'getPermissions']); // getTeacherPermissions
                Route::put('/{teacher}/permissions', [AdminTeacherController::class, 'updatePermissions']); // updateTeacherPermissions
                Route::get('/available-permissions', [AdminTeacherController::class, 'availablePermissionsForTeachers']);
            });
            Route::get('/getschool', [SchoolController::class, 'index']);  // okula bağlı userlar için okul bilgisi çekme

        });

    Route::middleware(CheckUserProfile::class)->group(function () {
        Route::get('getactivebranches', [BranchController::class, 'activeBranches']);

        // Paket satın alma
        Route::post('/packages/{package}/purchase', [PackageController::class, 'purchase'])
            ->name('packages.purchase');

        // Admin tarafından ödeme onayı




        //------------------------------------------------admin-----------------------------------------------
        //Managerın yapacağı işlemler
        Route::prefix("manager")->group(function () {
            Route::get('/teachers/available-permissions', [TeacherController::class, 'availablePermissionsForTeachers']);
            Route::get('/packages/{package}/my-grade-rules', [PackageWeekGradeRuleController::class, 'showManagerPackage']);

            Route::get('my-grades', [SchoolHasGradeController::class, 'myGrades'])
                ->middleware('auth:sanctum');
            Route::prefix("school")->group(function () {
                Route::get('/info', [SchoolController::class, 'info']);  // okula bağlı userlar için okul bilgisi çekme
                Route::put('/updateschool', [SchoolController::class, 'update']);
            });
        });


        Route::prefix("schools/{school}")->middleware(['ensure.school', 'active.school'])
            ->group(function () {
                Route::prefix('teachers')->group(function () {
                    Route::get('/', [TeacherController::class, 'index']); // teacherList
                    Route::get('/{teacher}', [TeacherController::class, 'show']); // getTeacherById
                    Route::post('/', [TeacherController::class, 'store']); // createTeacher
                    Route::put('/{teacher}', [TeacherController::class, 'update']); // updateTeacher
                    Route::delete('/{teacher}', [TeacherController::class, 'destroy']); // deleteTeacher
                    Route::delete('/{teacher}/permissions', [TeacherController::class, 'removePermissions']); // removeTeacherPermissions
                    Route::get('/{teacher}/permissions', [TeacherController::class, 'getPermissions']); // getTeacherPermissions
                    Route::put('/{teacher}/permissions', [TeacherController::class, 'updatePermissions']); // updateTeacherPermissions
                    Route::put('/{teacher}/reset-password', [TeacherController::class, 'resetPassword']); //resetTeacherPassword


                    // Yoklama Yönetimi (Session'a bağlı kaynak)
                    // Permission: 'record_attendance'
                    Route::get('sessions/{session}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
                    Route::post('sessions/{session}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
                });

                Route::prefix('students')->group(function () {
                    Route::get('/', [SchoolStudentProfileController::class, 'index']);
                    Route::get('/{student}', [SchoolStudentProfileController::class, 'show']);
                    Route::post('/', [SchoolStudentProfileController::class, 'store']);
                    Route::put('/{student}', [SchoolStudentProfileController::class, 'update']);
                    Route::delete('/{student}', [SchoolStudentProfileController::class, 'destroy']);
                    Route::put('/{student}/reset-password', [SchoolStudentProfileController::class, 'resetPassword']);
                    Route::get(
                        '/by-class/{classModel}',
                        [SchoolStudentProfileController::class, 'getByClassModel']
                    );
                });
                Route::prefix('students/{profile}/parents')->group(function () {
                    Route::get('/', [StudentParentController::class, 'index']);
                    Route::post('/', [StudentParentController::class, 'store']);
                    Route::get('/{parent}', [StudentParentController::class, 'show']);
                    Route::put('/{parent}', [StudentParentController::class, 'update']);
                    Route::delete('/{parent}', [StudentParentController::class, 'destroy']);
                });


                Route::prefix('classes')->group(function () {
                    Route::get('/', [ClassModelController::class, 'index']);
                    Route::post('/', [ClassModelController::class, 'store']);
                    Route::get('/{classModel}', [ClassModelController::class, 'show']);
                    Route::put('/{classModel}', [ClassModelController::class, 'update']);
                    Route::delete('/{classModel}', [ClassModelController::class, 'destroy']);
                });



                Route::middleware(['role:manager'])->group(function () {
                    Route::prefix('manager')->group(function () {
                        Route::resource('overrides', StudentCurriculumOverrideController::class)->only(['index', 'store', 'destroy']);
                    });
                    Route::prefix("weeks")->group(function () {

                        Route::get("/", [SchoolWeekController::class, "index"]);        // Tüm haftalar
                        Route::post("/", [SchoolWeekController::class, "store"]);      // Yeni hafta
                        Route::get("/{week}", [SchoolWeekController::class, "show"]);  // Hafta detay
                        Route::put("/{week}", [SchoolWeekController::class, "update"]); // Hafta güncelle
                        Route::delete("/{week}", [SchoolWeekController::class, "destroy"]); // Hafta sil
                        Route::post('/auto-generate', [SchoolWeekController::class, 'autoGenerate']);
                        Route::get('/check', [SchoolWeekController::class, 'checkWeeks']);
                    });

                    // School Week Day CRUD
                    Route::prefix("weeks/{week}/days")->group(function () {

                        Route::get("/", [SchoolWeekDayController::class, "index"]);        // Gün listesi
                        Route::post("/", [SchoolWeekDayController::class, "store"]);       // Yeni gün ekle
                        Route::get("/{day}", [SchoolWeekDayController::class, "show"]);    // Gün detay
                        Route::put("/{day}", [SchoolWeekDayController::class, "update"]);  // Gün güncelle
                        Route::delete("/{day}", [SchoolWeekDayController::class, "destroy"]); // Gün sil
                        Route::post('/auto-generate', [SchoolWeekDayController::class, 'autoGenerate']);
                        Route::get('/check', [SchoolWeekDayController::class, 'checkDays']);
                    });
                });








                // Öğretmen işlemleri
                Route::prefix('attendance')->group(function () {
                    Route::get('/{class_schedule_id}', [AttendanceController::class, 'index']);
                    Route::post('/store', [AttendanceController::class, 'store']);
                    Route::put('/{id}', [AttendanceController::class, 'update']);
                });
                Route::prefix('lessonsessions')->group(function () {
                    Route::get('/', [LessonSessionController::class, 'index']);
                    Route::get('/{id}', [LessonSessionController::class, 'show']);
                    Route::post('/store', [LessonSessionController::class, 'store']);
                    Route::put('/{id}', [LessonSessionController::class, 'update']);
                    Route::delete('/{id}', [LessonSessionController::class, 'destroy']);
                });

                Route::prefix('classschedules')->group(function () {
                    Route::get('/', [ClassScheduleController::class, 'index']);
                    Route::get('/{id}', [ClassScheduleController::class, 'show']);
                    Route::post('/store', [ClassScheduleController::class, 'store']);
                    Route::put('/{id}', [ClassScheduleController::class, 'update']);
                    Route::delete('/{id}', [ClassScheduleController::class, 'destroy']);
                });



                // Öğrenci işlemleri
                Route::prefix("students")->group(function () {
                    Route::post('/createuser', [SchoolStudentController::class, 'store']);  // öğrenci oluştur (user modeldeki bilgiler)
                    Route::post('/{user}/completeprofile', [SchoolStudentController::class, 'completeProfile']); // öğrenci profil, veli ve sağlık bilgilerini tamamlar
                    Route::get('/{id}', [SchoolStudentController::class, 'show']); // id ye göre öğrenci getir
                    Route::get('/byclass/{classModel}', [SchoolStudentController::class, 'getByClassModel']); //  sınıf id bazlı öğrencilerin tamamını getir
                    Route::get('/', [SchoolStudentController::class, 'index']); // o okuldaki öğrencilerin tamamını getir
                    Route::delete('/{id}', [SchoolStudentController::class, 'destroy']); //id ye göre öğrenci ve tüm profile bilgileri sil
                    Route::put('/{id}/update', [SchoolStudentController::class, 'update']); //  id ye göre öğrenci güncelle (user ve parent vs modeldeki bilgiler)

                    // Müfredat İçeriğini Görme (Kısıtlamalı)
                    // Rol Kontrolü: 'schoolstudent'
                    Route::get('curriculum/contents', [ContentController::class, 'studentIndex'])->name('curriculum.contents.index');
                });

                Route::prefix("additionalclassrooms")->group(function () {
                    Route::get("/getclass", [AdditionalClassRoomController::class, "index"]); // o okuldaki sınıfların tamamını getir
                    Route::post("/create", [AdditionalClassRoomController::class, "store"]); // sınıf oluştur
                    Route::get("/{id}", [AdditionalClassRoomController::class, "show"]); // id ye göre sınıf getir
                    Route::put("/update/{id}", [AdditionalClassRoomController::class, "updateClassroom"]); // id ye göre sınıf güncelle
                    Route::delete("/{id}", [AdditionalClassRoomController::class, "destroy"]); //id ye göre sınıf sil
                });
            });
    });
});

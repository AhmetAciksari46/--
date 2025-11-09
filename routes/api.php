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

use App\Http\Controllers\{
    UserController,
    PermissionController,
    SubscriptionController,
    IndividualStudentProfileController,
    IndividualStudentController,
    SchoolWeekController,
    AdditionalClassRoomController,
    StudentCurriculumOverrideController,
    AttendanceController,
    ContentController,
    SchoolWeekDayController,
    SubjectController,
    PackageWeekGradeRuleController,
    PackageWeekSubjectRuleController
};

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


    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::post("/managerregister", [AuthController::class, "managerregister"]);
        Route::get('/manager/{user_id}/profile', [ManagerProfileController::class, 'getManagerProfileById']);
        Route::put('/manager/{user_id}/profile', [ManagerProfileController::class, 'updateManagerProfileById']);
        Route::get('/manager/{id}', [ManagerController::class, 'getManagerUserById']);
        Route::put('/manager/{id}', [ManagerController::class, 'updateManagerUserById']);
        Route::post('/manager/{user_id}/profile', [ManagerProfileController::class, 'storeManagerProfileById']);


        Route::resource('contents', ContentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/subscriptions/{subscription}/approve', [PackageController::class, 'approvePayment']);
        Route::apiResource('branches', BranchController::class);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::apiResource('packages', PackageController::class);
        // 🏆 Sınıf (Grade) Kuralları CRUD
        Route::apiResource('packages.grade-rules', PackageWeekGradeRuleController::class)->except(['show']);

        // 📚 Ders (Subject) Kuralları CRUD
        Route::apiResource('packages.subject-rules', PackageWeekSubjectRuleController::class)->except(['show']);

        Route::apiResource('subjects', SubjectController::class);


        Route::prefix("school")->group(function () {
            Route::get('/getschools', [SchoolController::class, 'schoollist']);
            Route::get('/getschoolbyid/{school}', [SchoolController::class, 'getSchool']);
            Route::post('/createschool', [SchoolController::class, 'createschool']);
            Route::put('/updateschool/{school}', [SchoolController::class, 'updateschool']);
            Route::delete('/deleteschool/{school}', [SchoolController::class, 'deleteschool']);
        });
        Route::prefix("subscription")->group(function () {});
        //Route::prefix('admin')->middleware(['auth:sanctum', 'can:admin-access'])->group(function () {
        Route::get('subscriptions', [SubscriptionController::class, 'adminIndex']);
        Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
        // admin update/delete vs.
        Route::post('/subscriptions/{subscription}/approve', [PackageController::class, 'approvePayment'])
            ->middleware('role:admin') // Admin rolü zorunlu
            ->name('subscriptions.approve');
        Route::get('/getschool', [SchoolController::class, 'index']);  // okula bağlı userlar için okul bilgisi çekme

    });

    Route::middleware(CheckUserProfile::class)->group(function () {
        // Paket satın alma
        Route::post('/packages/{package}/purchase', [PackageController::class, 'purchase'])
            ->name('packages.purchase');

        // Admin tarafından ödeme onayı




        //------------------------------------------------admin-----------------------------------------------
        //Managerın yapacağı işlemler
        Route::prefix("manager")->group(function () {
            Route::prefix("school")->group(function () {
                Route::get('/info', [SchoolController::class, 'info']);  // okula bağlı userlar için okul bilgisi çekme
                Route::post('/createschool', [SchoolController::class, 'create']);
                Route::put('/updateschool', [SchoolController::class, 'update']);
            });

            Route::prefix("subscription")->group(function () {});
        });



        Route::prefix("schools/{school}")->middleware(['ensure.school', 'active.school'])
            ->group(function () {
                // Öğretmen işlemleri

                Route::middleware(['role:manager'])->group(function () {
                    Route::prefix('manager')->group(function () {
                        Route::resource('weeks', SchoolWeekController::class)->only(['index', 'update']);
                        Route::resource('overrides', StudentCurriculumOverrideController::class)->only(['index', 'store', 'destroy']);
                        Route::resource('days', SchoolWeekDayController::class)->only(['index', 'update']);
                    });

                    Route::prefix('teachers')->group(function () {
                        Route::get('/', [TeacherController::class, 'index']); // teacherList
                        Route::get('/{teacher}', [TeacherController::class, 'show']); // getTeacherById
                        Route::post('/', [TeacherController::class, 'store']); // createTeacher
                        Route::put('/{teacher}', [TeacherController::class, 'update']); // updateTeacher
                        Route::delete('/{teacher}', [TeacherController::class, 'destroy']); // deleteTeacher
                        Route::get('/teacherpermissions', [PermissionController::class, 'teacherPermissions']); //TODO: öğrenci ve manager içinde olacak aynısı.

                        // Permissions
                        Route::get('/{teacher}/permissions', [TeacherController::class, 'getPermissions']); // getTeacherPermissions
                        Route::put('/{teacher}/permissions', [TeacherController::class, 'updatePermissions']); // updateTeacherPermissions


                        // Yoklama Yönetimi (Session'a bağlı kaynak)
                        // Permission: 'record_attendance'
                        Route::get('sessions/{session}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
                        Route::post('sessions/{session}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
                    });
                    Route::prefix("classrooms")->group(function () {
                        Route::post('/', [ClassModelController::class, 'store']);
                        Route::put('/{classModel}', [ClassModelController::class, 'update']);
                        Route::delete('/{classModel}', [ClassModelController::class, 'destroy']);
                        Route::get('/classmodels', [ClassModelController::class, 'getBySchool']);
                        Route::get('/classmodels/{id}', [ClassModelController::class, 'getClassModelById']);
                    });
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


                // Route::prefix("teachers")->group(function () {
                //     Route::get("/getteachers", [TeacherController::class, "index"]); // o okuldaki öğretmenlerin tamamını getir
                //     Route::post("/create", [TeacherController::class, "store"]); // öğretmen oluştur
                //     Route::get("/{id}", [TeacherController::class, "show"]); // id ye göre öğretmen getir
                //     Route::delete("/{id}", [TeacherController::class, "destroy"]); // id ye göre öğretmen sil
                //     Route::put("/updateprofile/{id}", [TeacherController::class, "updateProfileByManager"]); // id ye göre öğretmen güncelle (manager için)
                //     Route::put("/updateprofilesettings{id}", [TeacherController::class, "updateProfileSettingsByManager"]); // id ye göre öğretmen profil settings güncelle (manager için)
                // });

                Route::prefix("additionalclassrooms")->group(function () {
                    Route::get("/getclass", [AdditionalClassRoomController::class, "index"]); // o okuldaki sınıfların tamamını getir
                    Route::post("/create", [AdditionalClassRoomController::class, "store"]); // sınıf oluştur
                    Route::get("/{id}", [AdditionalClassRoomController::class, "show"]); // id ye göre sınıf getir
                    Route::put("/update/{id}", [AdditionalClassRoomController::class, "updateClassroom"]); // id ye göre sınıf güncelle
                    Route::delete("/{id}", [AdditionalClassRoomController::class, "destroy"]); //id ye göre sınıf sil
                });
            });





        // Subscriptions





        Route::get('subscriptions', [SubscriptionController::class, 'index']); // kullanıcının abonelikleri
        Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
        Route::post('subscriptions', [SubscriptionController::class, 'store']);
        Route::patch('subscriptions/{id}', [SubscriptionController::class, 'update']);
        Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

        // Webhook (externally called, genelde auth/secret ile korunur)
        Route::post('subscriptions/webhook/payment', [SubscriptionController::class, 'paymentWebhook']);



        Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'show', 'store']);
        Route::get('/subscriptions/check/{school_id}', [SubscriptionController::class, 'checkActive']);
    });
});

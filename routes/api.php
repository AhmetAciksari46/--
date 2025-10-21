<?php

use App\Http\Controllers\Api\AuthController;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserProfile;

use App\Http\Controllers\{
    UserController,
    SchoolController,
    ClassModelController,
    CourseController,
    AssignmentController,
    ManagerController,
    PackageController,
    SubscriptionController,
    TeacherProfileController,
    ManagerProfileController,
    TeacherController,
    SchoolStudentController,
    SchoolStudentProfileController,
    IndividualStudentProfileController,
    IndividualStudentController,
    AdditionalClassRoomController
};

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

Route::post("/managerregister", [AuthController::class, "managerregister"]);
Route::post("/teacherregister", [AuthController::class, "teacherregister"]);
Route::post("/schoolstudentregister", [AuthController::class, "schoolstudentregister"]);
Route::post("/invidualstudentregister", [AuthController::class, "invidualstudentregister"]);


Route::get('/getpublicpackages', [PackageController::class, 'publicIndex']);
Route::get('/publicpackage/{id}', [PackageController::class, 'publicShow']);


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix("me")->group(function () {

        Route::get('/', function () {
            return response()->json(auth()->user());
        });
        Route::post("/logout", [AuthController::class, "logout"]);

        Route::prefix("manager")->middleware("role:manager")->group(function () {
            Route::put("/updateprofile", [ManagerController::class, "update"]);
            Route::put("/updateprofilesettings", [ManagerProfileController::class, "updateprofilesettings"]);
            Route::get("/getprofilesettings", [ManagerProfileController::class, "getprofilesettings"]);
        });

        // Teacher kendi profili
        //TODO : MANAGER TARAFLI KONTROL EDİLECEK
        Route::prefix("teacher")->middleware("role:teacher")->group(function () {
            Route::put("/updateprofile", [TeacherController::class, "updateprofile"]);
            Route::put("/updateprofilesettings", [TeacherProfileController::class, "updateprofilesettings"]);
            Route::get("/getprofilesettings", [TeacherProfileController::class, "getprofilesettings"]);
        });
        //TODO : MANAGER TARAFLI KONTROL EDİLECEK

        // School Student kendi profili ->Sadece ad syad ve şifre değişebilir// diğer bilgileri manager yada teacher değiştirir
        Route::prefix("schoolstudent")->middleware("role:schoolstudent")->group(function () {
            Route::put("/updateprofile", [SchoolStudentController::class, "updateprofile"]);
            Route::get("/getprofilesettings", [SchoolStudentProfileController::class, "getprofilesettings"]);
        });

        // individual Student kendi profili
        Route::prefix("individualstudent")->middleware("role:individualstudent")->group(function () {
            Route::put("/updateprofile", [IndividualStudentController::class, "updateprofile"]);
            Route::put("/updateprofilesettings", [IndividualStudentProfileController::class, "updateprofilesettings"]);
            Route::get("/getprofilesettings", [IndividualStudentProfileController::class, "getprofilesettings"]);
        });
    });



    Route::middleware(CheckUserProfile::class)->group(function () {
        // Paket satın alma
        Route::post('/packages/{package}/purchase', [PackageController::class, 'purchase'])
            ->name('packages.purchase');

        // Admin tarafından ödeme onayı
        Route::post('/subscriptions/{subscription}/approve', [PackageController::class, 'approvePayment'])
            ->middleware('role:admin') // Admin rolü zorunlu
            ->name('subscriptions.approve');

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        //----------------------------------------------admin----------------------------------------------------
        Route::prefix("admin")->group(function () {

            Route::prefix("package")->group(function () {
                Route::get('/getpackages', [PackageController::class, 'getpackages']);
                Route::get('/getpackagebyid/{id}', [PackageController::class, 'getpackagebyid']);
                Route::post('/createpackage', [PackageController::class, 'create']);
                Route::put('/updatepackagebyid/{id}', [PackageController::class, 'updatepackagebyid']);
                Route::delete('/deletepackagebyid/{id}', [PackageController::class, 'delete']);
            });
            Route::prefix("school")->group(function () {
                Route::get('/getschools', [SchoolController::class, 'schoollist']);
                Route::get('/getschoolbyid/{school}', [SchoolController::class, 'getSchool']);
                Route::post('/createschool', [SchoolController::class, 'createschool']);
                Route::put('/updateschool/{school}', [SchoolController::class, 'updateschool']);
                Route::delete('/deleteschool/{school}', [SchoolController::class, 'deleteschool']);
            });
            Route::prefix("subscription")->group(function () {});
        });
        //------------------------------------------------admin-----------------------------------------------
        Route::get('/getschool', [SchoolController::class, 'index']);  // okula bağlı userlar için okul bilgisi çekme
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

                // Öğrenci işlemleri

                Route::prefix("students")->group(function () {
                    Route::get("/getstudents", [SchoolStudentController::class, "index"]); // o okuldaki öğrencilerin tamamını getir
                    Route::get("/getstudentsbyclassid/{classModel}", [SchoolStudentController::class, "getstudentsbyclassid"]); //  sınıf id bazlı öğrencilerin tamamını getir
                    Route::get("/{id}", [SchoolStudentController::class, "show"]); // id ye göre öğrenci getir
                    Route::post("/createstudent", [SchoolStudentController::class, "store"]);    // öğrenci oluştur (user modeldeki bilgiler)
                    Route::post("/createstudentprofile", [SchoolStudentController::class, "createstudentprofile"]);    // öğrenci oluştur (schoolstudentprofile modeldeki bilgiler)
                    Route::post("/createstudentparent", [SchoolStudentController::class, "createstudentprofile"]);    // öğrenci oluştur (schoolstudentparent modeldeki bilgiler)
                    Route::post("/createstudenthealth", [SchoolStudentController::class, "createstudenthealth"]);    // öğrenci oluştur (schoolstudenthealth modeldeki bilgiler)
                    Route::put("/updatestudent/{id}", [SchoolStudentController::class, "updateById"]);    // id ye göre öğrenci güncelle (user modeldeki bilgiler)
                    Route::put("/updatestudentprofile/{id}", [SchoolStudentProfileController::class, "updateProfileSettingsById"]); // id ye göre öğrenci profil settings güncelle
                    Route::put("/updatestudentparent/{id}", [SchoolStudentController::class, "updateStudentParentById"]); // id ye göre öğrenci parent bilgilerini güncelle
                    Route::put("/updatestudenthealth/{id}", [SchoolStudentController::class, "updateStudentHealthById"]); // id ye göre öğrenci health bilgilerini güncelle
                    Route::put("/updateprofilesettings/{id}", [SchoolStudentProfileController::class, "updateProfileSettingsById"]); // id ye göre öğrenci profil settings güncelle
                    Route::delete("/{id}", [SchoolStudentController::class, "destroy"]); // id ye göre öğrenci ve tüm profile bilgileri sil
                });

                // Classroom işlemleri
                Route::prefix("classrooms")->group(function () {
                    Route::get("/getclass", [ClassModelController::class, "index"]); // o okuldaki sınıfların tamamını getir
                    Route::post("/create", [ClassModelController::class, "store"]); // sınıf oluştur
                    Route::get("/{id}", [ClassModelController::class, "show"]); // id ye göre sınıf getir
                    Route::put("/update/{id}", [ClassModelController::class, "updateClassroom"]); // id ye göre sınıf güncelle
                    Route::delete("/{id}", [ClassModelController::class, "destroy"]); //id ye göre sınıf sil
                });
                Route::prefix("additionalclassrooms")->group(function () {
                    Route::get("/getclass", [AdditionalClassRoomController::class, "index"]); // o okuldaki sınıfların tamamını getir
                    Route::post("/create", [AdditionalClassRoomController::class, "store"]); // sınıf oluştur
                    Route::get("/{id}", [AdditionalClassRoomController::class, "show"]); // id ye göre sınıf getir
                    Route::put("/update/{id}", [AdditionalClassRoomController::class, "updateClassroom"]); // id ye göre sınıf güncelle
                    Route::delete("/{id}", [AdditionalClassRoomController::class, "destroy"]); //id ye göre sınıf sil
                });
                // Öğretmen işlemleri
                Route::prefix("teachers")->group(function () {
                    Route::get("/getteachers", [TeacherController::class, "index"]); // o okuldaki öğretmenlerin tamamını getir
                    Route::post("/create", [TeacherController::class, "store"]); // öğretmen oluştur
                    Route::get("/{id}", [TeacherController::class, "show"]); // id ye göre öğretmen getir
                    Route::delete("/{id}", [TeacherController::class, "destroy"]); // id ye göre öğretmen sil
                    Route::put("/updateprofile/{id}", [TeacherController::class, "updateProfileByManager"]); // id ye göre öğretmen güncelle (manager için)
                    Route::put("/updateprofilesettings{id}", [TeacherController::class, "updateProfileSettingsByManager"]); // id ye göre öğretmen profil settings güncelle (manager için)
                });
            });

        // Subscriptions
        Route::get('subscriptions', [SubscriptionController::class, 'index']); // kullanıcının abonelikleri
        Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
        Route::post('subscriptions', [SubscriptionController::class, 'store']);
        Route::patch('subscriptions/{id}', [SubscriptionController::class, 'update']);
        Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
        // Subscriptions by Admin
        Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

            //Route::prefix('admin')->middleware(['auth:sanctum', 'can:admin-access'])->group(function () {
            Route::get('subscriptions', [SubscriptionController::class, 'adminIndex']);
            Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
            // admin update/delete vs.
        });

        // Webhook (externally called, genelde auth/secret ile korunur)
        Route::post('subscriptions/webhook/payment', [SubscriptionController::class, 'paymentWebhook']);



        Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'show', 'store']);
        Route::get('/subscriptions/check/{schoolId}', [SubscriptionController::class, 'checkActive']);
    });
});














        // // Schools
        // Route::apiResource('schools', SchoolController::class)->only(['index', 'show', 'store']);

        // // Classes
        // Route::apiResource('classes', ClassModelController::class)->only(['index', 'show', 'store']);

        // // Courses
        // Route::apiResource('courses', CourseController::class)->only(['index', 'show', 'store']);

        // // Assignments
        // Route::apiResource('assignments', AssignmentController::class)->only(['index', 'show', 'store']);

        // Packages
        //Route::apiResource('packages', PackageController::class)->only(['index', 'show', 'store']);

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
    IndividualStudentController
};
use App\Models\SchoolStudentProfile;
use PHPUnit\TextUI\XmlConfiguration\Logging\TeamCity;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

Route::post("/managerregister", [AuthController::class, "managerregister"]);
Route::post("/teacherregister", [AuthController::class, "teacherregister"]);
Route::post("/schoolstudentregister", [AuthController::class, "schoolstudentregister"]);
Route::post("/invidualstudentregister", [AuthController::class, "invidualstudentregister"]);



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
        Route::prefix("teacher")->middleware("role:teacher")->group(function () {
            Route::put("/updateprofile", [TeacherController::class, "updateprofile"]);
            Route::put("/updateprofilesettings", [TeacherProfileController::class, "updateprofilesettings"]);
            Route::get("/getprofilesettings", [TeacherProfileController::class, "getprofilesettings"]);
        });
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
        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Schools
        Route::apiResource('schools', SchoolController::class)->only(['index', 'show', 'store']);

        // Classes
        Route::apiResource('classes', ClassModelController::class)->only(['index', 'show', 'store']);

        // Courses
        Route::apiResource('courses', CourseController::class)->only(['index', 'show', 'store']);

        // Assignments
        Route::apiResource('assignments', AssignmentController::class)->only(['index', 'show', 'store']);

        // Packages
        Route::apiResource('packages', PackageController::class)->only(['index', 'show', 'store']);

        // Subscriptions
        Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'show', 'store']);
        Route::get('/subscriptions/check/{schoolId}', [SubscriptionController::class, 'checkActive']);

        Route::prefix("schools/{school}")->group(function () {

            // Öğrenci işlemleri
            Route::prefix("students")->group(function () {
                Route::get("/getstudents", [SchoolStudentController::class, "index"]); // o okuldaki öğrencilerin tamamını getir
                Route::get("/getstudentsbyclassid/{classId}", [SchoolStudentController::class, "getstudentsbyclassid"]); // o okuldaki sınıf id bazlı öğrencilerin tamamını getir
                Route::get("/{id}", [SchoolStudentController::class, "show"]); // id ye göre öğrenci getir
                Route::post("/create", [SchoolStudentController::class, "store"]);    // öğrenci oluştur
                Route::put("/updateprofile/{id}", [SchoolStudentController::class, "updateById"]);    // id ye göre öğrenci güncelle
                Route::put("/updateprofilesettings/{id}", [SchoolStudentProfileController::class, "updateProfileSettingsById"]); // id ye göre öğrenci profil settings güncelle
                Route::delete("/{id}", [SchoolStudentController::class, "destroy"]); // id ye göre öğrenci sil
            });

            // Classroom işlemleri
            Route::prefix("classrooms")->group(function () {
                Route::get("/getclass", [ClassModelController::class, "index"]); // o okuldaki sınıfların tamamını getir
                Route::post("/create", [ClassModelController::class, "store"]); // sınıf oluştur
                Route::get("/{id}", [ClassModelController::class, "show"]); // id ye göre sınıf getir
                Route::put("/update/{id}", [ClassModelController::class, "updateClassroom"]); // id ye göre sınıf güncelle
                Route::delete("/{id}", [ClassModelController::class, "destroy"]); //id ye göre sınıf sil
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
    });
});

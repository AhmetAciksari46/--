<?php
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\{
    UserController,
    SchoolController,
    ClassModelController,
    CourseController,
    AssignmentController,
    PackageController,
    SubscriptionController
};
Route::post("/register", [AuthController::class, "register"]);

Route::post("/managerregister", [AuthController::class, "managerregister"]);
Route::post("/teacherregister", [AuthController::class, "teacherregister"]);
Route::post("/schoolstudentregister", [AuthController::class, "schoolstudentregister"]);
Route::post("/invidualstudentregister", [AuthController::class, "invidualstudentregister"]);



Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);

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
});
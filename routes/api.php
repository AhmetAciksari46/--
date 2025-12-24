<?php

use App\Http\Controllers\Api\AuthController;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserProfile;

use App\Http\Controllers\School\User\TeacherProfileController;
use App\Http\Controllers\School\User\SchoolStudentProfileController;
use App\Http\Controllers\School\User\ManagerProfileController;
use App\Http\Controllers\School\User\ManagerController;
use App\Http\Controllers\School\User\TeacherController;

use App\Http\Controllers\School\General\SchoolController;
use App\Http\Controllers\School\General\TeacherSubjectController;
use App\Http\Controllers\School\General\ClassModelController;

use App\Http\Controllers\School\General\StudentPreRegistrationController;




use App\Http\Controllers\Admin\{
    ContentController,
    SubjectController,
    PackageWeekGradeRuleController,
    SchoolHasGradeController,
    GradeController,
    PackageController,
    BranchController,
    SubscriptionController
};

use App\Http\Controllers\School\Week\{
    AttendanceController,
    ClassScheduleController,
    LessonSessionController,
    SchoolWeekDayController,
    SchoolWeekController,
    PhysicalClassroomController
};


use App\Http\Controllers\Chat\{
    GroupController,
    MessageController,
    CommentController,
    ReactionController,
    LastReadMessageController,
    NotificationController,
    GroupMemberController,
    AttachmentController
};
use App\Http\Controllers\Permission\{
    UserPermissionController,
    TeacherPermissionController,
    StudentPermissionController,
};


use App\Http\Controllers\School\Student\{
    StudentLessonController,
};

use App\Http\Controllers\School\Teacher\{
    TeacherLessonController,
};

use App\Http\Controllers\{
    BirthDayCheckController,
    UserController,
    StudentCurriculumOverrideController,
    MediaFileController,
};
use App\Http\Controllers\School\User\StudentParentController;
use App\Http\Controllers\School\User\StudentHealthController;
use App\Http\Controllers\Permission\ManagerSelfPermissionController;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);



Route::middleware('auth:sanctum')->group(function () {


    //permission
    Route::prefix('manager/self/permissions')->group(function () {

        Route::get('/assignable', [ManagerSelfPermissionController::class, 'assignable']);

        Route::post('/assign', [ManagerSelfPermissionController::class, 'assign']);

        Route::post('/revoke', [ManagerSelfPermissionController::class, 'revoke']);
    });


    // 📘 Ön kayıt listeleme & oluşturma (school scoped)
    Route::get(
        '/schools/{school}/pre-registrations',
        [StudentPreRegistrationController::class, 'index']
    );

    Route::post(
        '/schools/{school}/pre-registrations',
        [StudentPreRegistrationController::class, 'store']
    );

    // 🔄 Ön kayıt state işlemleri
    Route::post(
        '/pre-registrations/{preRegistration}/submit',
        [StudentPreRegistrationController::class, 'submit']
    );

    Route::post(
        '/pre-registrations/{preRegistration}/approve',
        [StudentPreRegistrationController::class, 'approve']
    );

    Route::post(
        '/pre-registrations/{preRegistration}/cancel',
        [StudentPreRegistrationController::class, 'cancel']
    );

    Route::get('/birthdays/parents', [BirthDayCheckController::class, 'getParentBirthDays']);
    Route::get('/birthdays/students', [BirthDayCheckController::class, 'getStudentBirthDays']);
    Route::get('/birthdays/teachers', [BirthDayCheckController::class, 'getTeacherBirthDays']);






    Route::get(
        '/schools/teachers/assignable-permissions',
        [TeacherPermissionController::class, 'assignablePermissions']
    );

    Route::get(
        '/schools/{school}/teachers/{teacher}/permissions',
        [TeacherPermissionController::class, 'list']
    );

    Route::post(
        '/schools/{school}/teachers/{teacher}/permissions',
        [TeacherPermissionController::class, 'assign']
    );

    Route::delete(
        '/schools/{school}/teachers/{teacher}/permissions',
        [TeacherPermissionController::class, 'revoke']
    );


    Route::get(
        '/schools/students/assignable-permissions',
        [StudentPermissionController::class, 'assignablePermissions']
    );

    Route::get(
        '/schools/{school}/students/{student}/permissions',
        [StudentPermissionController::class, 'list']
    );

    Route::post(
        '/schools/{school}/students/{student}/permissions',
        [StudentPermissionController::class, 'assign']
    );

    Route::delete(
        '/schools/{school}/students/{student}/permissions',
        [StudentPermissionController::class, 'revoke']
    );




    Route::prefix('admin')->group(function () {

        Route::get(
            '/users/assignable-permissions',
            [UserPermissionController::class, 'assignablePermissions']
        );

        Route::get(
            '/users/{user}/permissions',
            [UserPermissionController::class, 'list']
        );

        Route::post(
            '/users/{user}/permissions',
            [UserPermissionController::class, 'assign']
        );

        Route::delete(
            '/users/{user}/permissions',
            [UserPermissionController::class, 'revoke']
        );
    });

    Route::prefix("me")->group(function () {
        Route::get("/", [AuthController::class, "meendpoint"]);
        Route::post("/logout", [AuthController::class, "logout"]);
    });


    //Route::middleware('role:manager')->prefix('manager')->group(function () {
    Route::prefix('manager')->group(function () {
        //Route::get('/me', [ManagerController::class, 'getManagerUser']); //kendi user tür bilgilerini getir
        Route::put('/me', [ManagerController::class, 'updateManagerUser']); //kendi user tür bilgilerini getir
        Route::put('/profile/me', [ManagerProfileController::class, 'updateManagerProfile']); //kendi managerprofil tür bilgilerini güncelle
        Route::post('/profile', [ManagerProfileController::class, 'storeManagerProfile']); //kendi managerprofil tür bilgilerini oluştur
    });
    Route::prefix('teacher')->group(function () {
        Route::get('/me', [TeacherProfileController::class, 'getprofilesettings']); //kendi user tür bilgilerini getir
        Route::put('/me', [TeacherController::class, 'updateTeacherUser']); //kendi user tür bilgilerini getir
        Route::put('/profile/me', [TeacherProfileController::class, 'updateprofilesettings']); //kendi teacherprofil tür bilgilerini güncelle
    });

    Route::prefix('admin/subjectssss')->group(function () {
        Route::get('/', [SubjectController::class, 'index']);
        Route::post('/', [SubjectController::class, 'store']);
        Route::put('/{id}', [SubjectController::class, 'update']);
        Route::delete('/{id}', [SubjectController::class, 'destroy']);
    });
    //Route::middleware('role:admin')->prefix('admin')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/school-has-grades', [SchoolHasGradeController::class, 'index']);
        Route::post('/school-has-grades', [SchoolHasGradeController::class, 'store']);
        Route::delete('/school-has-grades/{id}', [SchoolHasGradeController::class, 'destroy']);
        Route::get('/school-has-grades/by-school/{school}', [SchoolHasGradeController::class, 'getBySchoolId']);

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
        Route::put('/manager/{user_id}/profile', [ManagerProfileController::class, 'updateManagerProfileById']);
        Route::get('/manager/{id}', [ManagerController::class, 'getManagerUserById']);
        Route::put('/manager/{id}', [ManagerController::class, 'updateManagerUserById']);
        Route::post('/manager/{user_id}/profile', [ManagerProfileController::class, 'storeManagerProfileById']);

        Route::prefix('grades')->group(function () {
            Route::get('/', [GradeController::class, 'index']);
            Route::post('/store', [GradeController::class, 'store']);
            Route::put('/{grade}', [GradeController::class, 'update']);
            Route::get('/{grade}', [GradeController::class, 'show']);
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
    });

    Route::middleware(CheckUserProfile::class)->group(function () {
        Route::prefix('media')->middleware('auth:api')->group(function () {
            Route::post('/upload', [MediaFileController::class, 'store']);
            Route::get('/{media}', [MediaFileController::class, 'show']);
            Route::put('/{media}', [MediaFileController::class, 'update']);
            Route::delete('/{media}', [MediaFileController::class, 'destroy']);
        });
        Route::prefix('chat')->group(function () {
            Route::prefix('/groups')->group(function () {
                Route::put('/{group}', [GroupController::class, 'update']);
                Route::delete('/{group}', [GroupController::class, 'destroy']);
                Route::post('/{group}/last-read', [LastReadMessageController::class, 'update']);
                Route::get('/{group}/unread-count', [LastReadMessageController::class, 'unreadCount']);
                Route::get('/global', [GroupController::class, 'globalGroups']);
                Route::get('/{group}', [GroupController::class, 'show']);
                Route::get('/{group}/messages', [MessageController::class, 'index']);
                Route::post('/{group}/messages', [MessageController::class, 'store']);
                Route::prefix('/{group}')->group(function () {
                    Route::prefix('/members')->group(function () {
                        Route::get('/', [GroupMemberController::class, 'index']);
                        Route::post('/', [GroupMemberController::class, 'store']);
                        Route::put('/{member}', [GroupMemberController::class, 'update']);
                        Route::delete('/{member}', [GroupMemberController::class, 'destroy']);
                    });
                });
            });
            Route::prefix('/messages')->group(function () {
                Route::get('/{message}', [MessageController::class, 'show']);
                Route::put('/{message}', [MessageController::class, 'update']);
                Route::delete('/{message}', [MessageController::class, 'destroy']);
                Route::post('/{message}/pin', [MessageController::class, 'pin']);
                Route::delete('/{message}/pin', [MessageController::class, 'unpin']);
                Route::prefix('/{message}')->group(function () {
                    Route::get('/attachments', [AttachmentController::class, 'showMessageAttachment']);
                    Route::post('/attachments', [AttachmentController::class, 'store']);
                    Route::get('/comments', [CommentController::class, 'index']);
                    Route::post('/comments', [CommentController::class, 'store']);
                    Route::post('/reactions', [ReactionController::class, 'addMessageReaction']);
                    Route::delete('/reactions/{reaction}', [ReactionController::class, 'removeMessageReaction']);
                    Route::get('/reactions', [ReactionController::class, 'listMessageReactions']);
                });
            });
            Route::prefix('/comments')->group(function () {

                Route::put('/{comment}', [CommentController::class, 'update']);
                Route::delete('/{comment}', [CommentController::class, 'destroy']);
                Route::post('/{comment}/reactions', [ReactionController::class, 'addCommentReaction']);
                Route::delete('/{comment}/reactions/{reaction}', [ReactionController::class, 'removeCommentReaction']);
                Route::post('/{comment}/attachments', [AttachmentController::class, 'storee']);
            });
            Route::prefix('/notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'index']);
                Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
                Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
                Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
                Route::delete('/{notification}', [NotificationController::class, 'destroy']);
            });

            Route::prefix('/attachments')->group(function () {
                Route::get('/message/{attachment}/download', [AttachmentController::class, 'downloadMessageAttachment']);
                Route::delete('/message/{attachment}', [AttachmentController::class, 'deleteMessageAttachment']);
                Route::get('/comment/{attachment}', [AttachmentController::class, 'showCommentAttachment']);
                Route::get('/comment/{attachment}/download', [AttachmentController::class, 'downloadCommentAttachment']);
                Route::delete('/comment/{attachment}', [AttachmentController::class, 'deleteCommentAttachment']);
            });
            Route::get('/my-groups', [GroupController::class, 'myGroups']);
            Route::get('/unread-summary', [LastReadMessageController::class, 'unreadSummary']);
        });
        Route::post('/packages/{package}/purchase', [PackageController::class, 'purchase'])
            ->name('packages.purchase');




        Route::prefix("manager")->group(function () {
            Route::get('/packages/{package}/my-grade-rules', [PackageWeekGradeRuleController::class, 'showManagerPackage']);
            Route::get('/getactivesubjects', [SubjectController::class, 'activeSubjects']);
            Route::get('/getactivebranches', [BranchController::class, 'activeBranches']);

            Route::get('my-grades', [SchoolHasGradeController::class, 'myGrades'])
                ->middleware('auth:sanctum');
            Route::prefix("school")->group(function () {
                Route::get('/info', [SchoolController::class, 'info']);  // okula bağlı userlar için okul bilgisi çekme
                Route::put('/updateschool', [SchoolController::class, 'update']);
            });
        });
        Route::prefix("schools/{school}")->middleware(['ensure.school', 'active.school'])
            ->group(function () {
                Route::get('/chat/groups', [GroupController::class, 'schoolGroups']);

                Route::prefix('teacher-subjects')->group(function () {
                    Route::get('/', [TeacherSubjectController::class, 'index']);
                    Route::post('/', [TeacherSubjectController::class, 'store']);
                    Route::delete('/{id}', [TeacherSubjectController::class, 'destroy']);
                });
                Route::get('/classrooms', [PhysicalClassroomController::class, 'index']);

                Route::post('/classrooms', [PhysicalClassroomController::class, 'store']);

                Route::get('/classrooms/{classroom}', [PhysicalClassroomController::class, 'show']);

                Route::put('/classrooms/{classroom}', [PhysicalClassroomController::class, 'update']);

                Route::delete('/classrooms/{classroom}', [PhysicalClassroomController::class, 'destroy']);
                Route::prefix('class-schedules')->group(function () {

                    Route::get('/', [ClassScheduleController::class, 'index']);

                    Route::post('/', [ClassScheduleController::class, 'store']);

                    Route::get('/{schedule}', [ClassScheduleController::class, 'show']);

                    Route::put('/{schedule}', [ClassScheduleController::class, 'update']);

                    Route::delete('/{schedule}', [ClassScheduleController::class, 'destroy']);
                });
                Route::prefix('lesson-sessions')->group(function () {
                    Route::post('/generate', [LessonSessionController::class, 'generate']);
                    Route::get('/', [LessonSessionController::class, 'index']);

                    Route::post('/', [LessonSessionController::class, 'store']);

                    Route::get('/{session}', [LessonSessionController::class, 'show']);

                    Route::put('/{session}', [LessonSessionController::class, 'update']);

                    Route::delete('/{session}', [LessonSessionController::class, 'destroy']);
                    Route::prefix('/{session}/attendances')->group(function () {

                        Route::get('/', [AttendanceController::class, 'index']);

                        Route::post('/', [AttendanceController::class, 'store']);

                        Route::get('/{attendance}', [AttendanceController::class, 'show']);

                        Route::put('/{attendance}', [AttendanceController::class, 'update']);

                        Route::delete('/{attendance}', [AttendanceController::class, 'destroy']);
                        Route::post('/batch', [AttendanceController::class, 'batchStore']);
                    });
                });


                Route::prefix('teachers')->group(function () {
                    Route::get('/today-lessons', [TeacherLessonController::class, 'todayLessons']);
                    Route::get('/missing-attendance', [TeacherLessonController::class, 'missingAttendance']);
                    Route::get('/weekly-lessons', [TeacherLessonController::class, 'weeklyLessons']);

                    Route::get('/', [TeacherController::class, 'index']); // teacherList
                    Route::get('/{teacher}', [TeacherController::class, 'show']); // getTeacherById
                    Route::post('/', [TeacherController::class, 'store']); // createTeacher
                    Route::put('/{teacher}', [TeacherController::class, 'update']); // updateTeacher
                    Route::delete('/{teacher}', [TeacherController::class, 'destroy']); // deleteTeacher
                    Route::put('/{teacher}/reset-password', [TeacherController::class, 'resetPassword']); //resetTeacherPassword

                });

                Route::prefix('students')->group(function () {
                    Route::get('/', [SchoolStudentProfileController::class, 'index']);
                    Route::get('/{student}', [SchoolStudentProfileController::class, 'show']);
                    Route::post('/', [SchoolStudentProfileController::class, 'store']);
                    Route::put('/{student}', [SchoolStudentProfileController::class, 'update']);
                    Route::delete('/{student}', [SchoolStudentProfileController::class, 'destroy']);
                    Route::put('/{student}/reset-password', [SchoolStudentProfileController::class, 'resetPassword']);
                    Route::get('/by-class/{classModel}', [SchoolStudentProfileController::class, 'getByClassModel']);
                    Route::get('/{student}/details', [SchoolStudentProfileController::class, 'getDetails']);
                    Route::get('/{student}/today-lessons', [StudentLessonController::class, 'todayLessons']);
                    Route::get('/{student}/next-week-lessons', [StudentLessonController::class, 'nextWeekLessons']);
                });
                Route::prefix('students/{profile}/parents')->group(function () {
                    Route::get('/', [StudentParentController::class, 'index']);
                    Route::post('/', [StudentParentController::class, 'store']);
                    Route::get('/{parent}', [StudentParentController::class, 'show']);
                    Route::put('/{parent}', [StudentParentController::class, 'update']);
                    Route::delete('/{parent}', [StudentParentController::class, 'destroy']);
                });
                Route::prefix("students/{profile}/health")->group(function () {

                    Route::get('/', [StudentHealthController::class, 'show']);

                    Route::post('/', [StudentHealthController::class, 'store']);

                    Route::put('/', [StudentHealthController::class, 'update']);

                    Route::delete('/', [StudentHealthController::class, 'destroy']);
                });

                Route::prefix('classes')->group(function () {
                    Route::get('/{classModel}/chat/group', [GroupController::class, 'classroomGroup']);
                    Route::post('/{classModel}/chat/group', [GroupController::class, 'createClassroomGroup']);


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

                // Öğrenci işlemleri
                Route::prefix("students")->group(function () {
                    // Müfredat İçeriğini Görme (Kısıtlamalı)
                    // Rol Kontrolü: 'schoolstudent'
                    Route::get('curriculum/contents', [ContentController::class, 'studentIndex'])->name('curriculum.contents.index');
                });
            });
    });
});

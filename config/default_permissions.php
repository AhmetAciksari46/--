<?php

return [

    /**
     * Her kullanıcıya otomatik verilecek izinler.
     * (is_default = true yerine direkt burada da tutabilirsin)
     */
    'global' => [
        // İstersen buraya yaz
        // 'profile.update',
    ],

    /**
     * Rol bazlı default permissionlar.
     * Rol değişmeyecek dediğin için sadece created() sırasında uygulanacak.
     */
    'by_role' => [
        'manager' => [
            'subscription.create',
            'subscription.update',
            'teacher.permissions.update',
            'teacher.reset.password',
            'teacher.permissions.remove',
            'classmodel.create',
            'classmodel.update',
            'classmodel.delete',
            'classmodel.view.list',
            'school.update', //admin ve managerlar için
            'school.view.detail', //manager ve adminler için
            'studentpreregistration.create',
            'studentpreregistration.update',
            'studentpreregistration.view',
            'studentpreregistration.approve',
            'studentpreregistration.cancel',
            'teachersubject.create',
            'teachersubject.update',
            'teachersubject.delete',
            'teachersubject.view.list',
            'studentlesson.view.list',
            'teacherlesson.view.list',
            'manager.view.detail',
            'student.create',
            'student.update',
            'student.delete',
            'student.view.list',
            'teacher.create',
            'teacher.update',
            'teacher.delete',
            'teacher.view.list', //admin ve managerlar için
            'teacher.view.detail', //admin ve managerlar için
            'attendance.create',
            'attendance.update',
            'attendance.delete',
            'attendance.view.list',
            'classschedule.create',
            'classschedule.update',
            'classschedule.delete',
            'classschedule.view.list',
            'lessonsession.create',
            'lessonsession.update',
            'lessonsession.delete',
            'lessonsession.view.list',
            'PhysicalClassroom.create',
            'PhysicalClassroom.update',
            'PhysicalClassroom.delete',
            'PhysicalClassroom.view.list',
            'schoolweek.create',
            'schoolweek.update',
            'schoolweek.delete',
            'schoolweek.view.list',
            'parentbirthdays.view.detail',
            'teacherbirthdays.view.detail',
            'user.create',
            'user.update',
            'user.delete',
            'user.view.list',
            'profile.update',
            'settings.update',
            // başka manager defaultları...
        ],
        'teacher' => [
            // teacher defaultları...
        ],
        'school_student' => [
            //öğrenciler için
        ],
    ],
];

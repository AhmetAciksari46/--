 <?php
    return [

        // BRANCH
        'branch.create',
        'branch.update',
        'branch.delete',
        'branch.view',
        'branch.view.list',

        // GRADE
        'grade.create',
        'grade.update',
        'grade.delete',
        'grade.view',
        'grade.view.list',
        //mediapool
        'mediapool.view.list',
        'mediapool.view',
        'mediapool.create',
        'mediapool.update',
        'mediapool.delete',
        //content
        'content.delete',
        'content.update',
        'content.view',
        'content.create',
        'content.view.list',

        //category
        'category.delete',
        'category.view',
        'category.update',
        'category.create',
        'category.view.list',
        // PACKAGE
        'package.create',
        'package.update',
        'package.delete',
        'package.view',
        'package.view.list',

        //PACKAGE GRADE RULE
        'packagegrade.create',
        'packagegrade.update',
        'packagegrade.delete',
        'packagegrade.view',
        'packagegrade.view.list',

        //school has grade-> yine grade ile

        // SUBJECT
        'subject.create',
        'subject.update',
        'subject.delete',
        'subject.view',
        'subject.view.list',

        // SUBSCRIPTION
        'subscription.create',
        'subscription.update',
        'subscription.delete',
        'subscription.view',
        'subscription.view.list',

        //auth api managerda

        //chat
        //permissionlar role base
        // PERMISSIONS
        'teacher.permissions.update',
        'teacher.permissions.view',
        'teacher.available.permissions.view',
        'teacher.reset.password',
        'teacher.permissions.remove',

        //manager
        'managerpermission.create',
        'managerpermission.update',
        'managerpermission.view.list',
        'managerpermission.delete',
        'managerpermission.view',
        //user
        'userpermission.create',
        'userpermission.update',
        'userpermission.view.list',
        'userpermission.delete',
        'userpermission.view',

        //school
        //general
        // CLASS
        'classmodel.create',
        'classmodel.update',
        'classmodel.delete',
        'classmodel.view',
        'classmodel.view.list',
        //school
        'school.create', //admin için
        'school.update', //admin ve managerlar için
        'school.delete', //admin için
        'school.view', //tüm roller için
        'school.view.detail', //manager ve adminler için
        'school.view.list', //admin için
        //pre student
        'studentpreregistration.create',
        'studentpreregistration.update',
        'studentpreregistration.view',
        'studentpreregistration.approve',
        'studentpreregistration.cancel',
        // TEACHER SUBJECT
        'teachersubject.create',
        'teachersubject.update',
        'teachersubject.delete',
        'teachersubject.view',
        'teachersubject.view.list',
        //STUDENT
        //STUDENT LESSON
        'studentlesson.view.list',
        //TEACHER
        //teacher lesson
        'teacherlesson.view.list',
        //USER
        //MANAGER & auth içinde hem api/auth
        'manager.create',
        'manager.update',
        'manager.delete',
        'manager.view',
        'manager.view.list',
        'manager.view.detail',
        //MANAGER PROFİLE   ->MANAGER İLE ORTAK
        //STUDENT PROFİLE ->
        'student.create',
        'student.update',
        'student.delete',
        'student.view',
        'student.view.list',
        //STUDENT HEALTH PROFILE -> STUDENT İLE ORTAK
        //STUDENT PARENT -> STUDENT İLE ORTAK
        // TEACHER
        'teacher.create',
        'teacher.update',
        'teacher.delete',
        'teacher.view', //herkes için
        'teacher.view.list', //admin ve managerlar için
        'teacher.view.detail', //admin ve managerlar için

        //TEACHER PROFILE -> TEACHER İLE ORTAK
        //WEEK
        //ATTENDANCE
        'attendance.create',
        'attendance.update',
        'attendance.delete',
        'attendance.view',
        'attendance.view.list',
        //classschedule 
        'classschedule.create',
        'classschedule.update',
        'classschedule.delete',
        'classschedule.view',
        'classschedule.view.list',
        //lessonsession
        'lessonsession.create',
        'lessonsession.update',
        'lessonsession.delete',
        'lessonsession.view',
        'lessonsession.view.list',
        //physicalclassroom
        'PhysicalClassroom.create',
        'PhysicalClassroom.update',
        'PhysicalClassroom.delete',
        'PhysicalClassroom.view',
        'PhysicalClassroom.view.list',
        //schoolsession -> yapılmadı
        //schoolweek
        'schoolweek.create',
        'schoolweek.update',
        'schoolweek.delete',
        'schoolweek.view',
        'schoolweek.view.list',
        //schoolweekday ->yapılmadı

        //birthday
        'parentbirthdays.view.detail',
        'teacherbirthdays.view.detail',
        'studentbirthdays.view',


        //physicalclass
        'physicalclass.create',
        'physicalclass.view.list',
        'physicalclass.update',
        'physicalclass.delete',
        'physicalclass.view',

        //academicyear
        'academicyear.view.list',
        'academicyear.view',
        'academicyear.create',
        'academicyear.update',
        'academicyear.delete',

        //mediafile yapılmadı


        //studentcurriculum yapılmadı

        // USER
        'user.create',
        'user.update',
        'user.delete',
        'user.view',
        'user.view.list',

        // SETTINGS
        'profile.update',
        'settings.update',
    ];

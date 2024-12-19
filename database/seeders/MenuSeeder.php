<?php

namespace Database\Seeders;

use App\Models\Menu; // Correct import
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->datas() as $key => $value) {
            $this->createMenu($value);
        }
    }

    private function createMenu($data, $parent_id = null)
    {
        $menu = new Menu([
            'name' => $data['name'],
            'icon' => $data['icon'],
            'route' => $data['route'],
            'description' => $data['description'],
            'sorting' => $data['sorting'],
            'parent_id' => $parent_id,
            'permission_name' => $data['permission_name'],
            'status' => $data['status'],
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $menu->save();

        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createMenu($child, $menu->id);
            }
        }
    }

    private function datas()
    {
        return [
            [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'backend.dashboard',
                'description' => null,
                'sorting' => 1,
                'permission_name' => 'dashboard',
                'status' => 'Active',
            ],
            [
                'name' => 'User Manage',
                'icon' => 'list',
                'route' => null,
                'description' => null,
                'sorting' => 2,
                'permission_name' => 'user-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'User Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.admin.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'Admin-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'User List',
                        'icon' => 'list',
                        'route' => 'backend.admin.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'Admin-list',
                        'status' => 'Active',
                    ],
                ],
            ],
            [
                'name' => 'Permission Manage',
                'icon' => 'unlock',
                'route' => null,
                'description' => null,
                'sorting' => 3,
                'permission_name' => 'permission-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Permission Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.permission.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'permission-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Permission List',
                        'icon' => 'list',
                        'route' => 'backend.permission.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'permission-list',
                        'status' => 'Active',
                    ],
                ],
            ],
            [
                'name' => 'Role Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 4,
                'permission_name' => 'role-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Role Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.role.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'role-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Role List',
                        'icon' => 'list',
                        'route' => 'backend.role.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'role-list',
                        'status' => 'Active',
                    ],
                ],
            ],
            [
                'name' => 'Classes Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 5,
                'permission_name' => 'classes-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Classes Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.classes.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'classes-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Classes List',
                        'icon' => 'list',
                        'route' => 'backend.classes.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'classes-list',
                        'status' => 'Active',
                    ],
                ],
            ],
        
            
            [
                'name' => 'Student Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 6,
                'permission_name' => 'student-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Student Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.student.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'student-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Student List',
                        'icon' => 'list',
                        'route' => 'backend.student.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'student-list',
                        'status' => 'Active',
                    ],
                ],
            ],
            [
                'name' => 'Teacher Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 7,
                'permission_name' => 'teacher-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Teacher Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.teacher.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'teacher-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Teacher List',
                        'icon' => 'list',
                        'route' => 'backend.teacher.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'teacher-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Parent Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 8,
                'permission_name' => 'parent-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Parent Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.parent.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'parent-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Parent List',
                        'icon' => 'list',
                        'route' => 'backend.parent.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'parent-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Subject Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 9,
                'permission_name' => 'subject-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Subject Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.subject.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'subject-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Subject List',
                        'icon' => 'list',
                        'route' => 'backend.subject.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'subject-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Exam Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 10,
                'permission_name' => 'exam-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Exam Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.exam.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'exam-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Exam List',
                        'icon' => 'list',
                        'route' => 'backend.exam.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'exam-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Result Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 11,
                'permission_name' => 'result-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Result Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.result.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'result-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Result List',
                        'icon' => 'list',
                        'route' => 'backend.result.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'result-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Library Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 12,
                'permission_name' => 'library-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Library Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.library.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'library-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Library List',
                        'icon' => 'list',
                        'route' => 'backend.library.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'library-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Fees Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 13,
                'permission_name' => 'fees-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Fees Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.fees.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'fees-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Fees List',
                        'icon' => 'list',
                        'route' => 'backend.fees.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'fees-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Attendance Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 14,
                'permission_name' => 'attendance-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Attendance Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.attendance.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'attendance-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Attendance List',
                        'icon' => 'list',
                        'route' => 'backend.attendance.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'attendance-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Event Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 15,
                'permission_name' => 'event-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Event Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.event.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'event-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Event List',
                        'icon' => 'list',
                        'route' => 'backend.event.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'event-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'ClassRoutine Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 16,
                'permission_name' => 'classroutine-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'ClassRoutine Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.classroutine.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'classroutine-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'ClassRoutine List',
                        'icon' => 'list',
                        'route' => 'backend.classroutine.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'classroutine-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Grade Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 17,
                'permission_name' => 'grade-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Grade Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.grade.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'grade-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Grade List',
                        'icon' => 'list',
                        'route' => 'backend.grade.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'grade-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Report Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 18,
                'permission_name' => 'report-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Report Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.report.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'report-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Report List',
                        'icon' => 'list',
                        'route' => 'backend.report.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'report-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Payment Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 19,
                'permission_name' => 'payment-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Payment Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.payment.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'payment-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Payment List',
                        'icon' => 'list',
                        'route' => 'backend.payment.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'payment-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Scholarship Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 20,
                'permission_name' => 'scholarship-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Scholarship Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.scholarship.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'scholarship-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Scholarship List',
                        'icon' => 'list',
                        'route' => 'backend.scholarship.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'scholarship-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Salary Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 21,
                'permission_name' => 'salary-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Salary Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.salary.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'salary-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Salary List',
                        'icon' => 'list',
                        'route' => 'backend.salary.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'salary-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Donation Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 22,
                'permission_name' => 'donation-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Donation Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.donation.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'donation-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Donation List',
                        'icon' => 'list',
                        'route' => 'backend.donation.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'donation-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Notice Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 23,
                'permission_name' => 'notice-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Notice Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.notice.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'notice-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Notice List',
                        'icon' => 'list',
                        'route' => 'backend.notice.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'notice-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Assignment Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 24,
                'permission_name' => 'assignment-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Assignment Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.assignment.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'assignment-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Assignment List',
                        'icon' => 'list',
                        'route' => 'backend.assignment.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'assignment-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'ClassNote Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 25,
                'permission_name' => 'classnote-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'ClassNote Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.classnote.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'classnote-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'ClassNote List',
                        'icon' => 'list',
                        'route' => 'backend.classnote.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'classnote-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'Section Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 26,
                'permission_name' => 'section-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Section Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.section.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'section-add',
                        'status' => 'Active',
                    ],
                    [
                        [
                            'name' => 'Student Manage',
                            'icon' => 'layers',
                            'route' => null,
                            'description' => null,
                            'sorting' => 1,
                            'permission_name' => 'student-management',
                            'status' => 'Active',
                            'children' => [
                                [
                                    'name' => 'Student Add',
                                    'icon' => 'plus-circle',
                                    'route' => 'backend.student.create',
                                    'description' => null,
                                    'sorting' => 1,
                                    'permission_name' => 'student-add',
                                    'status' => 'Active',
                                ],
                                [
                                    'name' => 'Student List',
                                    'icon' => 'list',
                                    'route' => 'backend.student.index',
                                    'description' => null,
                                    'sorting' => 1,
                                    'permission_name' => 'student-list',
                                    'status' => 'Active',
                                ],
                            ],
                        ],   'name' => 'Section List',
                        'icon' => 'list',
                        'route' => 'backend.section.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'section-list',
                        'status' => 'Active',
                    ],
                ],
            ],


            [
                'name' => 'ClassRoom Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 27,
                'permission_name' => 'classroom-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'ClassRoom Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.classroom.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'classroom-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'ClassRoom List',
                        'icon' => 'list',
                        'route' => 'backend.classroom.index',
                        'description' => null,
                        'sorting' => 2,
                        'permission_name' => 'classroom-list',
                        'status' => 'Active',
                    ],
                ],
            ],


	    [
        'name' => 'Group Manage',
        'icon' => 'layers',
        'route' => null,
        'description' => null,
        'sorting' => 1,
        'permission_name' => 'group-management',
        'status' => 'Active',
        'children' => [
            [
                'name' => 'Group Add',
                'icon' => 'plus-circle',
                'route' => 'backend.group.create',
                'description' => null,
                'sorting' => 1,
                'permission_name' => 'group-add',
                'status' => 'Active',
            ],
            [
                'name' => 'Group List',
                'icon' => 'list',
                'route' => 'backend.group.index',
                'description' => null,
                'sorting' => 1,
                'permission_name' => 'group-list',
                'status' => 'Active',
            ],
        ],
    ],


	//don't remove this comment from menu seeder
        ];
    }
}
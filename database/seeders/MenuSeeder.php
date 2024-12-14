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
                'name' => 'Module Make',
                'icon' => 'slack',
                'route' => 'backend.moduleMaker',
                'description' => null,
                'sorting' => 1,
                'permission_name' => 'module maker',
                'status' => 'Active',
            ],
            [
                'name' => 'User Manage',
                'icon' => 'list',
                'route' => null,
                'description' => null,
                'sorting' => 1,
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
                        'sorting' => 1,
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
                'sorting' => 1,
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
                        'sorting' => 1,
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
                'sorting' => 1,
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
                        'sorting' => 1,
                        'permission_name' => 'role-list',
                        'status' => 'Active',
                    ],
                ],
            ],
            [
                'name' => 'Class Manage',
                'icon' => 'layers',
                'route' => null,
                'description' => null,
                'sorting' => 1,
                'permission_name' => 'class-management',
                'status' => 'Active',
                'children' => [
                    [
                        'name' => 'Class Add',
                        'icon' => 'plus-circle',
                        'route' => 'backend.class.create',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'class-add',
                        'status' => 'Active',
                    ],
                    [
                        'name' => 'Class List',
                        'icon' => 'list',
                        'route' => 'backend.class.index',
                        'description' => null,
                        'sorting' => 1,
                        'permission_name' => 'class-list',
                        'status' => 'Active',
                    ],
                ],
            ],
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
            ],





	[
        'name' => 'Teacher Manage',
        'icon' => 'layers',
        'route' => null,
        'description' => null,
        'sorting' => 1,
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
                'sorting' => 1,
                'permission_name' => 'teacher-list',
                'status' => 'Active',
            ],
        ],
    ],


	//don't remove this comment from menu seeder
        ];
    }
}
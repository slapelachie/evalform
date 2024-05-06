<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseController
{
    public function index()
    {
        return view('admin/index');
    }

    public function users()
    {
        return view('admin/users');
    }

    public function createUser()
    {
        $data['formTitle'] = "Create a New User";
        return view('admin/user_form', $data);
    }

    public function editUser($userId)
    {
        $users = auth()->getProvider();
        $user = $users->findById($userId);
        if ($user === null) {
            // Return 404;
            return;
        }
        $user->admin = $user->can('admin.access');

        $data['formTitle']  = "Edit " . $user->username;
        $data['user'] = $user;
        return view('admin/user_form', $data);
    }
}

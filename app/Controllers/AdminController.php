<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AdminController
 *
 * Handles admin-specific operations such as managing users.
 */
class AdminController extends BaseController
{
    /**
     * Load the form for creating or editing a user.
     *
     * @param string $formTitle Title to be displayed on the form.
     * @param object|null $user User object containing data to populate the form (optional).
     * @return string Rendered user form view
     */
    private function loadUserForm($formTitle, $user = null)
    {
        $data['formTitle'] = $formTitle;

        // If a user object is provided, include it in the data for form population
        if ($user !== null) {
            $data['user'] = $user;
        }

        return view('admin/user_form', $data);
    }

    /**
     * Display the admin dashboard.
     *
     * @return string Rendered admin dashboard view
     */
    public function index()
    {
        return view('admin/index');
    }

    /**
     * Display the users management page.
     *
     * @return string Rendered users management view
     */
    public function users()
    {
        return view('admin/users');
    }

    /**
     * Load the form for creating a new user.
     *
     * @return string Rendered new user form view
     */
    public function createUser()
    {
        return $this->loadUserForm('Create a New User');
    }

    /**
     * Load the form for editing an existing user.
     *
     * @param int $userId The ID of the user to edit.
     * @return string Rendered edit user form view
     * @throws \CodeIgniter\Exceptions\PageNotFoundException If the user is not found.
     */
    public function editUser($userId)
    {
        $users = auth()->getProvider();
        $user = $users->findById($userId);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'This user could not be found!'
            );
        }
        $user->admin = $user->can('admin.access');

        return $this->loadUserForm('Edit ' . $user->username, $user);
    }
}

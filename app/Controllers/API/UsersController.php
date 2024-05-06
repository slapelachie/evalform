<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;

class UsersController extends ResourceController
{
    protected $format = 'json';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $userProvider = auth()->getProvider();
        $users = $userProvider->select('id, username, last_active, active')->findall();

        foreach ($users as &$user) {
            $authUser = $userProvider->findById($user->id);
            $user->admin = $authUser->can('admin.access');
        }
        unset($user);

        return $this->respond($users);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $query = auth()->getProvider()->select('id, username, last_active');
        $user = $query->findById($id);
        $user->admin = $user->can('admin.access');

        return $this->respond($user);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $users = auth()->getProvider();

        // Fetch input as associative array
        $data = $this->request->getJSON(true);

        // Create a new shield user
        $user = new User([
            'username' => esc($data['username']),
            'email' => esc($data['email']),
            'password' => $data['password'],
        ]);

        // Save the user and handle errors
        if (!$users->save($user)) {
            return $this->failServerError('Failed to create user');
        }

        // Get the new user
        $newUser = $users->findById($users->getInsertID());

        // Set the correct group for the new user
        if (isset($data['admin']) && $data['admin'] === true) {
            $newUser->addGroup('superadmin');
        } else {
            $users->addToDefaultGroup($newUser);
        }

        // Handle if the password should be reset
        if (isset($data['reset_password']) && $data['reset_password']) {
            $newUser->forcePasswordReset();
        }

        // Activate the new user
        $newUser->activate();

        return $this->respondCreated($newUser);
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        log_message('debug', "Updating user!");
        $users = auth()->getProvider();
        $user = $users->findById($id);
        if ($user == null) {
            return $this->failNotFound('User not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if (!$users->update($id, $data)) {
            return $this->failServerError('Could not update the user');
        }

        // Get the new user
        $updatedUser = $users->findById($id);

        // Set the correct group for the new user
        if (isset($data['admin'])) {
            if ($data['admin']) {
                $updatedUser->addGroup('superadmin');
            } else {
                $updatedUser->removeGroup('superadmin');
            }
        }

        // Handle if the password should be reset
        if (isset($data['reset_password']) && $data['reset_password']) {
            $updatedUser->forcePasswordReset();
        }

        return $this->respondUpdated($updatedUser);
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        $users = auth()->getProvider();

        $user = $users->findById($id);
        if ($user == null) {
            return $this->failNotFound('User not found with id: ' . $id);
        }

        if ($users->delete($user->id, true)) {
            return $this->respondDeleted($user);
        }

        return $this->failServerError('Could not delete the user.');
    }
}

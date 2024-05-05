<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

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
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        $users = auth()->getProvider();
        $user = $users->findById($id);

        return $this->respond($user);
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
        $users = auth()->getProvider();
        $user = $users->findById($id);
        if ($user == null) {
            return $this->failNotFound('User not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if ($users->update($id, $data)) {
            $updatedUser = $users->findById($id);
            return $this->respondUpdated($updatedUser);
        }
        return $this->failServerError('Could not update the user');
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
        //
    }
}

<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;

/**
 * Class UsersController
 *
 * Handles CRUD operations for users via REST API
 */
class UsersController extends ResourceController
{
    protected const MSG_VALIDATION_ERROR = 'Invalid input. Please check the data and try again.';
    protected const MSG_SERVER_ERROR = 'An unexpected error occurred. Please try again later.';

    protected const MSG_NOT_FOUND = 'The requested user could not be found.';
    protected const MSG_CREATED = 'The user has been successfully created.';
    protected const MSG_UPDATED = 'The user has been successfully updated.';
    protected const MSG_DELETED = 'The user has been successfully deleted.';

    /** @var string $format The response format (e.g., JSON) */
    protected $format = 'json';

    /**
     * Return an array of user objects in array format, including admin status.
     *
     * @return ResponseInterface JSON response containing all users
     */
    public function index()
    {
        $userProvider = auth()->getProvider();
        $users = $userProvider->select('id, username, last_active, active')->findall();

        // Add admin status for each user
        foreach ($users as &$user) {
            $authUser = $userProvider->findById($user->id);
            $user->admin = $authUser->can('admin.access');
        }
        unset($user);

        return $this->respond($users);
    }

    /**
     * Return the details of a user by their ID, including admin status.
     *
     * @param int|string|null $id The ID of the user to retrieve
     * @return ResponseInterface JSON response containing the user data
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Retrieve the user by ID with selected fields
        $query = auth()->getProvider()->select('id, username, last_active', 'active');
        $user = $query->findById($id);
        if ($user === null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Add admin status to the user object
        $user->admin = $user->can('admin.access');

        return $this->respond($user);
    }

    /**
     * Helper function to configure the user's groups and settings.
     *
     * @param User $user The user entity being configured
     * @param array $data The input data used for configuration
     */
    private function setupUser(User $user, array $data)
    {
        // Set the appropriate group for the user based on the admin flag
        if (isset($data['admin'])) {
            if ($data['admin']) {
                $user->addGroup('superadmin');
            } else {
                $user->removeGroup('superadmin');
            }
        }

        // Trigger a password reset if requested
        if (isset($data['reset_password']) && $data['reset_password']) {
            $user->forcePasswordReset();
        }
    }

    /**
     * Create a new user resource using data from the request's JSON body.
     *
     * @return ResponseInterface JSON response indicating the created user
     */
    public function create()
    {
        $users = auth()->getProvider();

        // Fetch input as associative array
        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Create a new shield user
        $user = new User([
            'username' => esc($data['username']),
            'email' => esc($data['email']),
            'password' => $data['password'],
        ]);

        // Save the user entity and handle errors
        if (!$users->save($user)) {
            return $this->failServerError(self::MSG_SERVER_ERROR);
        }

        $newUser = $users->findById($users->getInsertID());
        $this->setupUser($newUser, $data);
        $newUser->activate();

        return $this->respondCreated($newUser, self::MSG_CREATED);
    }

    /**
     * Update an existing user resource using the provided ID and data from the request's JSON body.
     *
     * @param int|string|null $id The ID of the user to update
     * @return ResponseInterface JSON response indicating the updated user or an error
     */
    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $users = auth()->getProvider();
        $user = $users->findById($id);
        if ($user == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Update the user entity and handle errors
        if (!$users->update($id, $data)) {
            return $this->failServerError(self::MSG_SERVER_ERROR);
        }

        $updatedUser = $users->findById($id);
        $this->setupUser($updatedUser, $data);

        return $this->respondUpdated($updatedUser, self::MSG_UPDATED);
    }

    /**
     * Delete a user resource using the provided ID.
     *
     * @param int|string|null $id The ID of the user to delete
     * @return ResponseInterface JSON response indicating the deleted user or an error
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $users = auth()->getProvider();

        $user = $users->findById($id);
        if ($user == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Attempt to delete the user and handle errors
        if ($users->delete($user->id, true)) {
            return $this->respondDeleted($user, self::MSG_DELETED);
        }

        return $this->failServerError(self::MSG_SERVER_ERROR);
    }
}

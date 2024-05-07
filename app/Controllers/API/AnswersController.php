<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Class AnswersController
 *
 * Handles CRUD operations for answers via REST API
 */
class AnswersController extends ResourceController
{
    /** @var string $modelName The name of the model this controller interacts with */
    protected $modelName = 'App\Models\AnswersModel';
    /** @var string $format Response format to be returned (e.g., JSON) */
    protected $format = 'json';

    /**
     * Get a list of answers or count of answers if `count` is set.
     *
     * @return ResponseInterface JSON response containing a list of answers or their count
     */
    public function index()
    {
        // Retrieve query parameters
        $questionId = $this->request->getGet('question_id');
        $count = $this->request->getGet('count');

        // Initialise the query with the model
        $query = $this->model;

        // If a question id is provided, filter answers by that question id
        if ($questionId !== null) {
            $query = $query->where('question_id', $questionId);
        }

        // If a count query is passed, return the count of filtered answers
        if (isset($count)) {
            $responseCount = $query->countAllResults(false);
            return $this->respond(['count' => $responseCount]);
        }

        // Retrieve and return all filtered answers
        $answers = $query->findAll();
        return $this->respond($answers);
    }

    /**
     * Get a specific answer by its ID.
     *
     * @param int|null $id The ID of the answer to be retrieved
     * @return ResponseInterface JSON response containing the answer data
     */
    public function show($id = null)
    {
        // Ensure an id is providec
        if ($id === null) {
            return $this->failValidationErrors('ID cannot be null.');
        }

        // Retrieve the answer by its id, if not found, return an error
        $answer = $this->model->find($id);
        if ($answer === null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        return $this->respond($answer);
    }

    /**
     * Create a new answer with data from the request's JSON body.
     *
     * @return ResponseInterface JSON response indicating the created answer
     */
    public function create()
    {
        // Retrieve json data from the request
        $data = $this->request->getJSON(true);

        // Ensure valid json data is provided
        if ($data === null) {
            return $this->failValidationErrors('Invalid JSON.');
        }

        // Attempt to insert the data into the database
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        // Retrieve the inserted answer by its id
        $answerId = $this->model->getInsertID();
        $answer = $this->model->find($answerId);

        return $this->respondCreated($answer, 'Answer created successfully.');
    }

    /**
     * Update an existing answer's data using its ID and data from the request's JSON body.
     *
     * @param int|null $id The ID of the answer to update
     * @return ResponseInterface JSON response indicating the updated answer or an error
     */
    public function update($id = null)
    {
        // Ensure an id is provided
        if ($id === null) {
            return $this->failValidationErrors('ID cannot be null.');
        }

        // Retrieve the existing answer by its id
        $answer = $this->model->find($id);
        if ($answer == null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        // Retrieve json data from the request
        $data = $this->request->getJSON(true);

        // Ensure valid json data is provided
        if ($data === null) {
            return $this->failValidationErrors('Invalid JSON.');
        }

        // Attempt to update the existing answer
        if ($this->model->update($id, $data)) {
            $updatedAnswer = $this->model->find($id);
            return $this->respondUpdated($updatedAnswer, 'Answer updated successfully.');
        }

        return $this->failServerError('Could not update the answer');
    }

    /**
     * Delete an answer by its ID.
     *
     * @param int|null $id The ID of the answer to delete
     * @return ResponseInterface JSON response indicating the deleted answer or an error
     */
    public function delete($id = null)
    {
        // Ensure an id is provided
        if ($id === null) {
            return $this->failValidationErrors('ID cannot be null.');
        }

        // Retrieve the existing answer by its id
        $answer = $this->model->find($id);
        if ($answer === null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        // Attempt to delete the answer by its id
        if ($this->model->delete($id)) {
            return $this->respondDeleted($answer, 'Answer deleted successfully.');
        }

        return $this->failServerError('Could not delete the answer');
    }
}

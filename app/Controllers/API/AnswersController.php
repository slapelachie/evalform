<?php

namespace App\Controllers\API;

use App\Models\QuestionsModel;
use App\Models\SurveysModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Class AnswersController
 *
 * Handles CRUD operations for answers via REST API
 */
class AnswersController extends ResourceController
{
    protected const MSG_VALIDATION_ERROR = 'Invalid input. Please check the data and try again.';
    protected const MSG_SERVER_ERROR = 'An unexpected error occurred. Please try again later.';

    protected const MSG_NOT_FOUND = 'The requested answer could not be found.';
    protected const MSG_CREATED = 'The answer has been successfully created.';
    protected const MSG_UPDATED = 'The answer has been successfully updated.';
    protected const MSG_DELETED = 'The answer has been successfully deleted.';
    protected const MSG_UNAUTHORISED_UPDATE = 'You are not authorised to update this answer.';
    protected const MSG_UNAUTHORISED_DELETE = 'You are not authorised to delete this answer.';

    /** @var string $modelName The name of the model this controller interacts with */
    protected $modelName = 'App\Models\AnswersModel';
    /** @var string $format Response format to be returned (e.g., JSON) */
    protected $format = 'json';

    /**
     * Check whether the current user has permissions to modify a specific answer.
     *
     * @param int $questionId The ID of the question to check
     * @return bool True if the user has permissions, false otherwise
     */
    private function checkPermissions($questionId)
    {
        // Initialise models
        $questionsModel = new QuestionsModel();
        $surveysModel = new SurveysModel();

        // Retrieve current authenticated user
        $currentUser = auth()->user();
        $userId = $currentUser->id;
        $isAdmin = $currentUser->can('admin.access');

        // If the user is not an admin, ensure they own the survey associated with the response
        if (!$isAdmin) {
            $question = $questionsModel->find($questionId);

            if ($question === null) {
                return false;
            }

            $survey = $surveysModel->find($question['survey_id']);

            // Ensure that the current user is the owner of the survey
            if ($survey === null || $survey['owner_id'] != $userId) {
                return false;
            }
        }

        return true;
    }

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
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Retrieve the answer by its id, if not found, return an error
        $answer = $this->model->find($id);
        if ($answer === null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
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
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Attempt to insert the data into the database
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        // Retrieve the inserted answer by its id
        $answerId = $this->model->getInsertID();
        $answer = $this->model->find($answerId);

        return $this->respondCreated($answer, self::MSG_CREATED);
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
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Retrieve the existing answer by its id
        $answer = $this->model->find($id);
        if ($answer == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Verify that the current user has permissions to modify this answer
        if (!$this->checkPermissions($answer['question_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_UPDATE);
        }

        // Retrieve json data from the request
        $data = $this->request->getJSON(true);

        // Ensure valid json data is provided
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Attempt to update the existing answer
        if ($this->model->update($id, $data)) {
            $updatedAnswer = $this->model->find($id);
            return $this->respondUpdated($updatedAnswer, self::MSG_UPDATED);
        }

        return $this->failServerError(self::MSG_SERVER_ERROR);
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
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Retrieve the existing answer by its id
        $answer = $this->model->find($id);
        if ($answer === null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Verify that the current user has permissions to modify this answer
        if (!$this->checkPermissions($answer['question_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_DELETE);
        }

        // Attempt to delete the answer by its id
        if ($this->model->delete($id)) {
            return $this->respondDeleted($answer, self::MSG_DELETED);
        }

        return $this->failServerError(self::MSG_SERVER_ERROR);
    }
}

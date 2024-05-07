<?php

namespace App\Controllers\API;

use App\Models\SurveysModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class QuestionsController extends ResourceController
{
    protected const MSG_VALIDATION_ERROR = 'Invalid input. Please check the data and try again.';
    protected const MSG_SERVER_ERROR = 'An unexpected error occurred. Please try again later.';

    protected const MSG_NOT_FOUND = 'The requested question could not be found.';
    protected const MSG_CREATED = 'The question has been successfully created.';
    protected const MSG_UPDATED = 'The question has been successfully updated.';
    protected const MSG_DELETED = 'The question has been successfully deleted.';
    protected const MSG_UNAUTHORISED_ACCESS = 'You are not authorised to access this question.';
    protected const MSG_UNAUTHORISED_UPDATE = 'You are not authorised to update this question.';
    protected const MSG_UNAUTHORISED_DELETE = 'You are not authorised to delete this question.';

    /** @var string $modelName The name of the model this controller interacts with */
    protected $modelName = 'App\Models\QuestionsModel';

    /** @var string $modelName The name of the model this controller interacts with */
    protected $format = 'json';

    /**
     * Check whether the current user has permissions to a specific question.
     *
     * @param int $surveyId The ID of the survey to check
     * @return bool True if the user has permissions, false otherwise
     */
    private function checkPermissions($surveyId)
    {
        // Initialise models
        $surveysModel = new SurveysModel();

        // Retrieve current authenticated user
        $currentUser = auth()->user();
        $userId = $currentUser->id;
        $isAdmin = $currentUser->can('admin.access');

        // If the user is not an admin, ensure they own the survey associated with the response
        if (!$isAdmin) {
            $survey = $surveysModel->find($surveyId);

            // Ensure that the current user is the owner of the survey
            if ($survey === null || $survey['owner_id'] != $userId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a list of questions or count of questions if `count` is set.
     *
     * @return ResponseInterface JSON response containing a list of questions or their count
     */
    public function index()
    {
        // Retrieve query parameters
        $surveyId = $this->request->getGet('survey_id');
        $type = $this->request->getGet('type');
        $count = $this->request->getGet('count');

        $query = $this->model;

        $filters = ['survey_id' => $surveyId, 'type' => $type];

        // Apply filters if present
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query = $query->where($field, $value);
            }
        }

        // If `count` query parameter is set, return the count of filtered questions
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $questions = $query->findall();
        return $this->respond($questions);
    }

    /**
     * Get a specific question by its ID.
     *
     * @param int|null $id The ID of the question to retrieve
     * @return ResponseInterface JSON response containing the question data
     */
    public function show($id = null)
    {
        $question = $this->model->find($id);
        if ($question == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        return $this->respond($question);
    }

    /**
     * Create a new question with data from the request's JSON body.
     *
     * @return ResponseInterface JSON response indicating the created question
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Attempt to insert the data into the database
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        $questionId = $this->model->getInsertID();
        $question = $this->model->find($questionId);

        return $this->respondCreated($question, self::MSG_CREATED);
    }

    /**
     * Update an existing question's data using its ID and data from the request's JSON body.
     *
     * @param int|null $id The ID of the question to update
     * @return ResponseInterface JSON response indicating the updated question or an error
     */
    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $question = $this->model->find($id);
        if ($question === null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Check if the current user has the necessary permissions to update this question
        if (!$this->checkPermissions($question['survey_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_UPDATE);
        }

        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Attempt to update the existing question
        if ($this->model->update($id, $data)) {
            $updatedQuestion = $this->model->find($id);
            return $this->respondUpdated($updatedQuestion, self::MSG_UPDATED);
        }

        return $this->failServerError(self::MSG_SERVER_ERROR);
    }

    /**
     * Delete a question by its ID.
     *
     * @param int|null $id The ID of the question to delete
     * @return ResponseInterface JSON response indicating the deleted question or an error
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $question = $this->model->find($id);
        if ($question === null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Check if the current user has the necessary permissions to delete this question
        if (!$this->checkPermissions($question['survey_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_DELETE);
        }

        // Attempt to delete the question by its id
        if ($this->model->delete($id)) {
            return $this->respondDeleted($question, self::MSG_DELETED);
        }

        return $this->failServerError(self::MSG_SERVER_ERROR);
    }
}

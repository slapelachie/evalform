<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\SurveyResponsesModel;
use App\Models\SurveysModel;

/**
 * Class QuestionResponsesController
 *
 * Handles CRUD operations for question responses via REST API
 */
class QuestionResponsesController extends ResourceController
{
    /** @var string $modelName The model associated with this controller */
    protected $modelName = 'App\Models\QuestionResponsesModel';

    /** @var string $format Response format to be returned (e.g., JSON) */
    protected $format = 'json';

    /**
     * Check whether the current user has permissions to access a specific question response.
     *
     * @param int $questionResponseId The ID of the question response to check
     * @return bool True if the user has permissions, false otherwise
     */
    private function checkPermissions($questionResponseId)
    {
        // Initialise models
        $surveyResponsesModel = new SurveyResponsesModel();
        $surveysModel = new SurveysModel();

        // Retrieve current authenticated user
        $currentUser = auth()->user();
        $userId = $currentUser->id;
        $isAdmin = $currentUser->can('admin.access');

        // If the user is not an admin, ensure they own the survey associated with the response
        if (!$isAdmin) {
            $surveyResponse = $surveyResponsesModel->find($questionResponseId);

            if ($surveyResponse === null) {
                return false;
            }

            $survey = $surveysModel->find($surveyResponse['survey_id']);

            // Ensure that the current user is the owner of the survey
            if ($survey === null || $survey['owner_id'] != $userId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve a list of question responses or the count of responses if `count` is set.
     * Applies filters based on the provided query parameters.
     *
     * @return ResponseInterface JSON response containing the list of question responses or their count
     */
    public function index()
    {
        // Retrieve query parameters for filtering
        $questionId = $this->request->getGet('question_id');
        $answerId = $this->request->getGet('answer_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $count = $this->request->getGet('count');

        // Retrieve current authenticated user information
        $currentUser = auth()->user();
        $userId = $currentUser->id;
        $isAdmin = $currentUser->can('admin.access');

        // Initialise query with necessary joins and filters
        $query = $this->model
            ->select('question_responses.*')
            ->join(
                'survey_responses',
                'survey_responses.id = question_responses.survey_response_id',
                'inner'
            )
            ->join('surveys', 'surveys.id = survey_responses.survey_id', 'inner');

        // Apply filters based on provided query parameters
        if ($questionId !== null) {
            $query = $query->where('question_responses.question_id', $questionId);
        }

        if ($answerId !== null) {
            $query = $query->where('question_responses.answer_id', $answerId);
        }

        if ($startDate !== null) {
            $query = $query->where(
                'survey_responses.submit_time >=',
                date('Y-m-d H:i:s', (int) $startDate)
            );
        }

        if ($endDate !== null) {
            $query = $query->where(
                'survey_responses.submit_time <=',
                date('Y-m-d H:i:s', (int) $endDate)
            );
        }

        // Apply additional filter to limit results to surveys owned by the current user if not admin
        if (!$isAdmin) {
            $query = $query->where('surveys.owner_id', $userId);
        }

        // Count results if the `count` query is set
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        // Retrieve and return all filtered question responses
        $responses = $query->findall();
        return $this->respond($responses);
    }

    /**
     * Retrieve a specific question response by its ID.
     *
     * @param int|null $id The ID of the question response to retrieve
     * @return ResponseInterface JSON response containing the question response or an error
     */
    public function show($id = null)
    {
        // Retrieve the specific question response
        $response = $this->model->find($id);

        // If the response does not exist
        if ($response === null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        // Verify that the current user has permissions to access this response
        if (!$this->checkPermissions($response['id'])) {
            return $this->failForbidden('You do not have access to this question response.');
        }

        return $this->respond($response);
    }

    /**
     * Create a new question response with data from the request's JSON body.
     *
     * @return ResponseInterface JSON response indicating the created response
     */
    public function create()
    {
        // Retrieve data from the request's json body
        $data = $this->request->getJSON(true);

        // Ensure valid json data is provided
        if ($data === null) {
            return $this->failValidationErrors('Invalid JSON.');
        }

        // Attempt to insert the data into the database
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        // Retrieve the inserted response by its id
        $responseId = $this->model->getInsertID();
        $response = $this->model->find($responseId);

        return $this->respondCreated($response, 'Question response created successfully.');
    }

    /**
     * Update an existing question response using its ID and data from the request's JSON body.
     *
     * @param int|null $id The ID of the question response to update
     * @return ResponseInterface JSON response indicating the updated response or an error
     */
    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('ID cannot be null.');
        }

        // Retrieve the existing question response by its id
        $response = $this->model->find($id);

        // If the response doesn't exist
        if ($response === null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        // Verify that the current user has permissions to access this response
        if (!$this->checkPermissions($response['id'])) {
            return $this->failForbidden('You do not have access to this question response.');
        }

        // Retrieve data from the request's json body
        $data = $this->request->getJSON(true);

        // Ensure valid json data is provided
        if ($data === null) {
            return $this->failValidationErrors('Invalid JSON.');
        }

        // Attempt to update the existing question response
        if ($this->model->update($id, $data)) {
            $updatedResponse = $this->model->find($id);
            return $this->respondUpdated(
                $updatedResponse,
                'Question response updated successfully'
            );
        }
        return $this->failServerError('Could not update the question response');
    }

    /**
     * Delete a question response by its ID.
     *
     * @param int|null $id The ID of the question response to delete
     * @return ResponseInterface JSON response indicating the deleted response or an error
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('ID cannot be null.');
        }

        // Retrieve the existing question response by its id
        $response = $this->model->find($id);

        // If the response doesn't exist
        if ($response === null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        // Verify that the current user has permissions to access this response
        if (!$this->checkPermissions($response['id'])) {
            return $this->failForbidden('You do not have access to this question response.');
        }

        // Attempt to delete the response by its id
        if ($this->model->delete($id)) {
            return $this->respondDeleted($response, 'Question response deleted successfully');
        }

        return $this->failServerError('Could not delete the question response');
    }
}

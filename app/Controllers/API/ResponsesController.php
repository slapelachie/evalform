<?php

namespace App\Controllers\API;

use App\Models\SurveysModel;
use CodeIgniter\RESTful\ResourceController;

/**
 * Class ResponsesController
 *
 * Manages survey responses with CRUD operations via REST API.
 */
class ResponsesController extends ResourceController
{
    protected const MSG_VALIDATION_ERROR = 'Invalid input. Please check the data and try again.';
    protected const MSG_SERVER_ERROR = 'An unexpected error occurred. Please try again later.';

    protected const MSG_NOT_FOUND = 'The requested response could not be found.';
    protected const MSG_CREATED = 'The response has been successfully created.';
    protected const MSG_UPDATED = 'The response has been successfully updated.';
    protected const MSG_DELETED = 'The response has been successfully deleted.';
    protected const MSG_UNAUTHORISED_ACCESS = 'You are not authorised to access this response.';
    protected const MSG_UNAUTHORISED_UPDATE = 'You are not authorised to update this response.';
    protected const MSG_UNAUTHORISED_DELETE = 'You are not authorised to delete this response.';

    /** @var string $modelName The model name representing the survey responses */
    protected $modelName = 'App\Models\SurveyResponsesModel';
    /** @var string $format Response format to be returned (e.g., JSON) */
    protected $format = 'json';

    /**
     * Check whether the current user has permissions to a specific survey response.
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
     * Retrieve error messages from a given model and format them as a string.
     *
     * @param object $model The model from which to retrieve errors
     * @return string Concatenated error message
     */
    private function getModelErrorMessage($model)
    {
        $errors = $model->errors();
        $errorMessage = is_array($errors) ? implode('; ', $errors) : $errors;
        return $errorMessage;
    }

    /**
     * Retrieve question responses associated with a specific survey response.
     *
     * @param int $surveyResponseId The ID of the survey response
     * @return array An array of question responses
     */
    private function getResponses($surveyResponseId)
    {
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        // Retrieve all question responses tied to a specific survey response id
        $questionResponses = $questionResponsesModel
            ->where('survey_response_id', $surveyResponseId)
            ->findAll();

        return $questionResponses;
    }

    /**
     * Retrieve all survey responses or count if `count` is specified.
     *
     * @return ResponseInterface JSON response containing all survey responses or their count
     */
    public function index()
    {
        $surveyId = $this->request->getGet('survey_id');
        $count = $this->request->getGet('count');

        // Retrieve current authenticated user information
        $currentUser = auth()->user();
        $userId = $currentUser->id;
        $isAdmin = $currentUser->can('admin.access');

        // Initialise query and join surveys to get owner id later
        $query = $this->model
            ->select('survey_responses.*')
            ->join('surveys', 'surveys.id = survey_responses.survey_id', 'inner');

        // Apply additional filter to limit results to surveys owned by the current user if not admin
        if (!$isAdmin) {
            $query = $query->where('surveys.owner_id', $userId);
        }

        // Apply an additional filter for survey responses associated with the specified survey
        if ($surveyId !== null) {
            $query = $query->where('survey_id', $surveyId);
        }

        // Count results and send it as a response
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $surveyResponses = $query->findall();

        // Retrieve question responses associated with each survey response
        foreach ($surveyResponses as &$surveyResponse) {
            $responses = $this->getResponses($surveyResponse['id']);
            $surveyResponse['responses'] = $responses;
        }
        unset($surveyResponse);

        return $this->respond($surveyResponses);
    }

    /**
     * Retrieve a specific survey response by its ID.
     *
     * @param int|null $id The ID of the survey response to retrieve
     * @return ResponseInterface JSON response containing the survey response data
     */
    public function show($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $surveyResponse = $this->model->find($id);
        if ($surveyResponse == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Check if the user has permissions for the associated survey
        if (!$this->checkPermissions($surveyResponse['survey_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_ACCESS);
        }

        $surveyResponse['responses'] = $this->getResponses($id);

        return $this->respond($surveyResponse);
    }

    /**
     * Insert new question responses into the database for a given survey response.
     *
     * @param array $responses Array of question response data
     * @param int $surveyResponseId The ID of the survey response to associate with the questions
     * @throws \Exception If an error occurs during insertion
     */
    private function insertResponses($responses, $surveyResponseId)
    {
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();
        $questionsModel = new \App\Models\QuestionsModel();

        // Iterate through each response and insert it appropriately
        foreach ($responses as $response) {
            $questionId = $response['question_id'];
            $responseData = [
                'survey_response_id' => $surveyResponseId,
                'question_id' => $questionId,
                'answer_id' => null,
                'answer_text' => null,
            ];

            // Retrieve question data to determine the type of response
            $question = $questionsModel->find($questionId);
            if ($question['type'] == 'multiple_choice') {
                $responseData['answer_id'] = $response['answer'];
            } elseif ($question['type'] == 'free_text') {
                $responseData['answer_text'] = $response['answer'];
            }

            // Attempt to insert the question response into the database
            if (!$questionResponsesModel->insert($responseData)) {
                $errorMessage = $this->getModelErrorMessage($questionResponsesModel);
                throw new \Exception($errorMessage);
            }
        }
    }

    /**
     * Create a new survey response and its associated question responses.
     *
     * @return ResponseInterface JSON response indicating the created survey response
     */
    public function create()
    {
        $this->model->transStart();

        $data = $this->request->getJSON(true);

        // Insert the new survey response into the database
        if (!$this->model->insert($data)) {
            $this->model->transRollback();
            $errorMessage = $this->getModelErrorMessage($this->model);
            return $this->fail($errorMessage);
        }

        $surveyResponseId = $this->model->getInsertId();

        // Insert the associated question responses if they exist
        if (isset($data['responses'])) {
            try {
                $this->insertResponses($data['responses'], $surveyResponseId);
            } catch (\Exception $error) {
                $this->model->transRollback();
                return $this->fail($error->getMessage());
            }
        }

        $this->model->transComplete();

        // Retrieve and return the newly created survey response with its question responses
        $surveyResponse = $this->model->find($surveyResponseId);
        $surveyResponse['responses'] = $this->getResponses($surveyResponseId);

        return $this->respondCreated($surveyResponse);
    }
}

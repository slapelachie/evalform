<?php

namespace App\Controllers\API;

use App\Models\QuestionsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Class SurveyController
 *
 * Handles CRUD operations for surveys via REST API
 */
class SurveyController extends ResourceController
{
    protected const MSG_VALIDATION_ERROR = 'Invalid input. Please check the data and try again.';
    protected const MSG_SERVER_ERROR = 'An unexpected error occurred. Please try again later.';

    protected const MSG_NOT_FOUND = 'The requested survey could not be found.';
    protected const MSG_CREATED = 'The survey has been successfully created.';
    protected const MSG_UPDATED = 'The survey has been successfully updated.';
    protected const MSG_DELETED = 'The survey has been successfully deleted.';
    protected const MSG_UNAUTHORISED_ACCESS = 'You are not authorised to access this survey.';
    protected const MSG_UNAUTHORISED_UPDATE = 'You are not authorised to update this survey.';
    protected const MSG_UNAUTHORISED_DELETE = 'You are not authorised to delete this survey.';

    /** @var string $modelName The model name representing the survey responses */
    protected $modelName = 'App\Models\SurveysModel';
    /** @var string $format Response format to be returned (e.g., JSON) */
    protected $format = 'json';

    /**
     * Check whether the current user has permissions to a specific survey.
     *
     * @param int $ownerId The ID of the survey owner
     * @return bool True if the user has permissions, false otherwise
     */
    private function checkPermissions($ownerId)
    {
        // Retrieve current authenticated user
        $currentUser = auth()->user();
        return $currentUser->can('admin.access') || $ownerId == $currentUser->id;
    }

    /**
     * Retrieve error messages from a model as a concatenated string.
     *
     * @param object $model The model object containing the errors
     * @return string The concatenated error messages
     */
    private function getModelErrorMessage($model)
    {
        $errors = $model->errors();
        return is_array($errors) ? implode('; ', $errors) : $errors;
    }

    /**
     * Retrieve all questions and associated answers for a specific survey.
     *
     * @param int $surveyId The ID of the survey
     * @return array An array of questions with their corresponding answers
     */
    private function getQuestions($surveyId)
    {
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $questions = $questionsModel->where('survey_id', $surveyId)->findAll();

        // Attach answers to their respective questions
        foreach ($questions as &$question) {
            $question['answers'] = $answersModel->where('question_id', $question['id'])->findAll();
        }
        unset($question);

        return $questions;
    }

    /**
     * Get a paginated list of surveys or their count if requested.
     *
     * @return ResponseInterface JSON response containing a list of surveys or their count
     */
    public function index()
    {
        $ownerId = $this->request->getGet('owner_id');
        $status = $this->request->getGet('status');
        $count = $this->request->getGet('count');

        // Pagination setup
        $pager = \Config\Services::pager();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);

        $query = $this->model;

        // Apply optional filters
        $filters = ['owner_id' => $ownerId, 'status' => $status];
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query = $query->where($field, $value);
            }
        }

        $totalResults = $query->countAllResults(false);

        // If only count is requested, return the count of surveys
        if (isset($count)) {
            return $this->respond(['count' => $totalResults]);
        }

        // Apply pagination to the query and retrieve the surveys
        $surveys = $query->findall($perPage, ($page - 1) * $perPage);

        // Attach questions to each survey
        foreach ($surveys as &$survey) {
            $survey['questions'] = $this->getQuestions($survey['id']);
        }
        unset($survey);

        // Generate pagination links
        $paginationLinks = $pager->makeLinks($page, $perPage, $totalResults, 'bootstrap_full');

        return $this->respond([
            'results' => $surveys,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalResults,
                'links' => $paginationLinks,
            ],
        ]);
    }

    /**
     * Retrieve a specific survey by its ID.
     *
     * @param int|null $id The ID of the survey to retrieve
     * @return ResponseInterface JSON response containing the survey data
     */
    public function show($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        // Attach questions to the survey
        $survey['questions'] = $this->getQuestions($id);

        return $this->respond($survey);
    }

    /**
     * Insert or update questions for a specific survey.
     *
     * @param array $questions The list of questions to insert/update
     * @param int $surveyId The survey ID associated with the questions
     * @throws \Exception If an error occurs during question insertion/updating
     */
    private function upsertQuestions($questions, $surveyId)
    {
        $questionsModel = new \App\Models\QuestionsModel();

        // Retrieve existing question IDs associated with the survey
        $existingQuestionIds = array_column(
            $questionsModel->where('survey_id', $surveyId)->findAll(),
            'id'
        );

        $updatedQuestionIds = [];

        // Iterate through provided questions for insertion/updating
        foreach ($questions as $question) {
            $questionData = [
                'survey_id' => $surveyId,
                'type' => $question['type'],
                'question_number' => $question['question_number'],
                'question' => $question['question'],
            ];

            // Update existing questions or insert new ones
            if (isset($question['id']) && in_array($question['id'], $existingQuestionIds)) {
                if (!$questionsModel->update($question['id'], $questionData)) {
                    throw new \Exception(
                        ($errorMessage = $this->getModelErrorMessage($questionsModel))
                    );
                }
                $updatedQuestionIds[] = $question['id'];
                $questionId = $question['id'];
            } else {
                if (!$questionsModel->insert($questionData)) {
                    $errorMessage = $this->getModelErrorMessage($questionsModel);
                    throw new \Exception($errorMessage);
                }

                $questionId = $questionsModel->getInsertID();
            }

            // Insert/update answers for each question if available
            if (isset($question['answers']) && $question['type'] == 'multiple_choice') {
                try {
                    $this->upsertAnswers($question['answers'], $questionId);
                } catch (\Exception $error) {
                    throw $error;
                }
            }
        }

        // Remove any questions not updated
        $questionsModel
            ->whereIn('id', array_diff($existingQuestionIds, $updatedQuestionIds))
            ->delete();
    }

    /**
     * Insert or update answers for a specific question.
     *
     * @param array $answers The list of answers to insert/update
     * @param int $questionId The question ID associated with the answers
     * @throws \Exception If an error occurs during answer insertion/updating
     */
    private function upsertAnswers($answers, $questionId)
    {
        $answersModel = new \App\Models\AnswersModel();

        // Retrieve existing answers for this question
        $existingAnswerIds = array_column(
            $answersModel->where('question_id', $questionId)->findAll(),
            'id'
        );

        $updatedAnswerIds = [];

        // Iterate through provided answers for insertion/updating
        foreach ($answers as $answer) {
            $answerData = [
                'question_id' => $questionId,
                'position' => $answer['position'],
                'answer' => $answer['answer'],
            ];

            // Update existing answers or insert new ones
            if (isset($answer['id']) && in_array($answer['id'], $existingAnswerIds)) {
                if (!$answersModel->update($answer['id'], $answerData)) {
                    $errorMessage = $this->getModelErrorMessage($answersModel);
                    throw new \Exception($errorMessage);
                }
                $updatedAnswerIds[] = $answer['id'];
            } else {
                if (!$answersModel->insert($answerData)) {
                    $errorMessage = $this->getModelErrorMessage($answersModel);
                    throw new \Exception($errorMessage);
                }
            }
        }

        // Remove any answers not updated
        $answersModel->whereIn('id', array_diff($existingAnswerIds, $updatedAnswerIds))->delete();
    }

    /**
     * Create a new survey with questions and answers (if provided).
     *
     * @return ResponseInterface JSON response containing the created survey data
     */
    public function create()
    {
        $this->model->transStart();

        // Retrieve and validate the input data
        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        // Insert the survey data and check for errors
        if (!$this->model->insert($data)) {
            $this->model->transRollback();
            return $this->fail($this->getModelErrorMessage($this->model));
        }

        $surveyId = $this->model->getInsertID();
        

        // Insert/update questions if provided
        if (isset($data['questions'])) {
            try {
                // $this->upsertQuestions($data['questions'], $surveyId);
            } catch (\Exception $error) {
                $this->model->transRollback();
                return $this->fail($error->getMessage());
            }
        }

        $this->model->transComplete();

        // Retrieve the full survey data including questions
        $survey = $this->model->find($surveyId);
        $survey['questions'] = $this->getQuestions($surveyId);

        return $this->respondCreated($survey, self::MSG_CREATED);
    }

    /**
     * Update an existing survey and its questions/answers.
     *
     * @param int|null $id The ID of the survey to update
     * @return ResponseInterface JSON response containing the updated survey data
     */
    public function update($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        if (!$this->checkPermissions($survey['owner_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_UPDATE);
        }

        $data = $this->request->getJSON(true);
        if ($data === null) {
            return $this->failValidationErrors(self::MSG_VALIDATION_ERROR);
        }

        $this->model->transStart();

        if (!$this->model->update($id, $data)) {
            $this->model->transRollback();
            return $this->failServerError(self::MSG_SERVER_ERROR);
        }

        // Insert/update questions if provided
        if (isset($data['questions'])) {
            try {
                // $this->upsertQuestions($data['questions'], $id);
            } catch (\Exception $error) {
                $this->model->transRollback();
                return $this->failServerError(self::MSG_SERVER_ERROR);
            }
        }

        $this->model->transComplete();

        // Retrieve the updated survey data including questions
        $updatedSurvey = $this->model->find($id);
        $updatedSurvey['questions'] = $this->getQuestions($id);

        return $this->respondUpdated($updatedSurvey, self::MSG_UPDATED);
    }

    /**
     * Delete a survey by its ID.
     *
     * @param int|null $id The ID of the survey to delete
     * @return ResponseInterface JSON response confirming deletion
     */
    public function delete($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound(self::MSG_NOT_FOUND);
        }

        if (!$this->checkPermissions($survey['owner_id'])) {
            return $this->failForbidden(self::MSG_UNAUTHORISED_UPDATE);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted($survey, self::MSG_DELETED);
        }
        return $this->failServerError(self::MSG_SERVER_ERROR);
    }
}

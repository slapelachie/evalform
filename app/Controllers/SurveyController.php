<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class SurveyController
 *
 * Handles operations related to surveys, including creation, management, and exporting.
 */
class SurveyController extends BaseController
{
    private function checkPermissions($ownerId)
    {
        // Retrieve current authenticated user
        $currentUser = auth()->user();
        return $currentUser->can('admin.access') || $ownerId == $currentUser->id;
    }

    /**
     * Retrieve a specific survey by its ID or throw a 404 error if not found.
     *
     * @param int $surveyId The ID of the survey to be retrieved
     * @return array The survey data
     * @throws \CodeIgniter\Exceptions\PageNotFoundException If the survey is not found
     */
    private function getSurveyOrThrow($surveyId)
    {
        $surveysModel = new \App\Models\SurveysModel();
        $survey = $surveysModel->find($surveyId);
        if ($survey === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'This survey could not be found!'
            );
        }
        return $survey;
    }

    /**
     * Retrieve questions of a given survey along with their possible choices.
     *
     * @param int $surveyId The ID of the survey
     * @return array An array of questions, each containing a list of choices
     */
    private function getSurveyQuestionsWithChoices($surveyId)
    {
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $questions = $questionsModel
            ->where('survey_id', $surveyId)
            ->orderBy('question_number', 'ASC')
            ->findAll();

        // Add choices to each question
        foreach ($questions as &$question) {
            $question['choices'] = $answersModel
                ->where('question_id', $question['id'])
                ->orderBy('position', 'ASC')
                ->findAll();
        }
        unset($question);

        return $questions;
    }

    /**
     * Display a list of surveys.
     *
     * @return string The view containing the list of surveys
     */
    public function index()
    {
        return view('surveys');
    }

    /**
     * Display a specific survey's details along with its questions and choices.
     *
     * @param int $surveyId The ID of the survey to be viewed
     * @return string The view containing the survey details
     */
    public function view($surveyId)
    {
        $data['survey'] = $this->getSurveyOrThrow($surveyId);
        $data['questions'] = $this->getSurveyQuestionsWithChoices($surveyId);

        return view('survey', $data);
    }

    /**
     * Render the survey creation form.
     *
     * @return string The view containing the survey creation form
     */
    public function create()
    {
        $userId = auth()->user()->id;
        return view('survey_form', [
            'user_id' => $userId,
            'survey' => null,
            'questions' => null,
        ]);
    }

    /**
     * Manage a specific survey.
     *
     * @param int $surveyId The ID of the survey to be managed
     * @return string|ResponseInterface The view for managing the survey or a 403 response if unauthorized
     */
    public function manage($surveyId)
    {
        $data['survey'] = $this->getSurveyOrThrow($surveyId);

        // Check if the user has the correct permissions
        if (!$this->checkPermissions($data['survey']['owner_id'])) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        return view('survey_manage', $data);
    }

    /**
     * Edit a specific survey's details and questions.
     *
     * @param int $surveyId The ID of the survey to be edited
     * @return string|ResponseInterface The view containing the edit form or a 403 response if unauthorized
     */
    public function edit($surveyId)
    {
        $userId = auth()->user()->id;
        $survey = $this->getSurveyOrThrow($surveyId);

        // Check if the user has the correct permissions
        if (!$this->checkPermissions($survey['owner_id'])) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $questions = $this->getSurveyQuestionsWithChoices($surveyId);

        return view('survey_form', [
            'user_id' => $userId,
            'survey' => $survey ?? [],
            'questions' => $questions ?? [],
        ]);
    }

    /**
     * Build an individual response for a question from a response data array.
     *
     * @param array $questionResponse The response data for a specific question
     * @return array|null The processed response data or null if the response is invalid
     */
    private function buildIndividualResponse($questionResponse)
    {
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $question = $questionsModel->find($questionResponse['question_id']);

        // Determine the answer value (free text or multiple-choice)
        $answerValue = $questionResponse['answer_text'];
        if ($questionResponse['answer_id'] != null) {
            $answer = $answersModel->find($questionResponse['answer_id']);
            if ($answer !== null) {
                $answerValue = $answer['answer'];
            } else {
                return null;
            }
        }

        if ($question === null) {
            return null;
        }

        // Build the response
        return [
            $questionResponse['id'],
            $questionResponse['survey_response_id'],
            $question['question'],
            $answerValue,
        ];
    }

    /**
     * Build and organize all responses for a given survey into an exportable format.
     *
     * @param int $surveyId The ID of the survey to export
     * @return array A multidimensional array containing response data
     */
    private function buildSurveyData($surveyId)
    {
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        // Initialize data structure with headers
        $surveyResponses = $surveyResponsesModel->where('survey_id', $surveyId)->findAll();
        $data = [['id', 'survey_response_id', 'question', 'answer']];

        // Populate data with individual responses
        foreach ($surveyResponses as $surveyResponse) {
            $questionResponses = $questionResponsesModel
                ->where('survey_response_id', $surveyResponse['id'])
                ->findAll();

            foreach ($questionResponses as $questionResponse) {
                $individualResponse = $this->buildIndividualResponse($questionResponse);
                if ($individualResponse !== null) {
                    $data[] = $individualResponse;
                }
            }
        }

        return $data;
    }

    /**
     * Export a survey's data to a CSV file and send it in the response.
     *
     * @param array $data The data to be exported
     * @return ResponseInterface The response containing the CSV file download
     */
    private function exportToCSV($data)
    {
        $response = $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="survey_results.csv"')
            ->noCache();

        $fp = fopen('php://output', 'w');

        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        return $response->send();
    }

    /**
     * Export a specific survey's data in the requested format.
     *
     * @param int|null $surveyId The ID of the survey to export
     * @return ResponseInterface The response containing the exported data or an error message
     */
    public function export($surveyId = null)
    {
        $format = $this->request->getGet('format') ?? 'csv';
        $survey = $this->getSurveyOrThrow($surveyId);

        // Check if the user has the correct permissions
        if (!$this->checkPermissions($survey['owner_id'])) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $data = $this->buildSurveyData($surveyId);

        if ($format == 'csv') {
            return $this->exportToCSV($data);
        }

        return $this->response->setStatusCode(400)->setBody("Format '$format' is not supported.");
    }

    /**
     * Display a 'thank you' message after survey completion.
     *
     * @return string The view containing the thank you message
     */
    public function thankYou()
    {
        return view('survey_complete');
    }
}

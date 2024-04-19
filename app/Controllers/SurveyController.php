<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SurveyController extends BaseController
{
    public function index($survey_id)
    {
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $questionAnswerChoicesModel = new \App\Models\QuestionAnswerChoicesModel();

        $data['survey'] = $surveysModel->where('id', $survey_id)->first();
        $data['questions'] = $questionsModel->where('survey_id', $survey_id)->orderBy('question_number', 'ASC')->findAll();

        foreach ($data['questions'] as &$question) {
            $question['choices'] = $questionAnswerChoicesModel->where('question_id', $question['id'])->orderBy('position', 'ASC')->findAll();
        }
        unset($question);

        return view('survey', $data);
    }

    private function handleSurveyResponseError($status_code, $message)
    {
        log_message('error', $message);
        $this->response->setStatusCode($status_code)->setJSON(['error' => $message]);
    }

    private function createSurveyResponse(int $survey_id)
    {
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();

        $survey_response_data = ['survey_id' => $survey_id];
        return $surveyResponsesModel->insert($survey_response_data, true);
    }

    private function insertQuestionResponse(int $question_id, int $survey_response_id, $answer)
    {
        $questionsModel = new \App\Models\QuestionsModel();
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        $question = $questionsModel->find($question_id);
        if (!$question) {
            $this->handleSurveyResponseError(400, "Question not found with id $question_id");
            return false;
        }

        $question_response_payload = [
            'survey_response_id' => $survey_response_id,
            'question_id' => $question_id,
            'answer_id' => ($question['type'] == 'multiple_choice') ? $answer : null,
            'answer_text' => ($question['type'] == 'free_text') ? $answer : null,
        ];

        $questionResponsesModel->insert($question_response_payload);
    }

    private function processQuestionResponses($question_responses, $survey_response_id)
    {
        // Process each survey question response
        foreach ($question_responses as $question_response) {
            if (!isset($question_response['question_id'], $question_response['value'])) {
                $this->handleSurveyResponseError(400, 'Missing question ID or value in one or more responses.');
                return false;
            }

            $question_id = $question_response['question_id'];
            $answer = $question_response['value'];

            return $this->insertQuestionResponse($question_id, $survey_response_id, $answer);
        }

        return true;
    }

    public function surveySubmit($survey_id)
    {
        // Convert json into an array
        $question_response_data = $this->request->getJSON(true);

        // Check if json is valid
        if (empty($question_response_data)) {
            return $this->handleSurveyResponseError(400, 'Invalid JSON data: ' . json_last_error_msg());
        }

        // Start a transaction
        db_connect()->transBegin();

        // Create survey response
        $survey_response_id = $this->createSurveyResponse($survey_id);

        if (!$this->processQuestionResponses($question_response_data, $survey_response_id)) {
            // Error handling is done within the function
            db_connect()->transRollback();
            return;
        }

        // Check if any errors occured, rollback if it has
        if (db_connect()->transStatus() === false) {
            db_connect()->transRollback();
            return $this->handleSurveyResponseError(500, "Error processing your request");
        } else {
            db_connect()->transCommit();
            return $this->response->setStatusCode(201)->setJSON(['message' => 'Survey response submitted sucessfully']);
        }
    }

    public function manage($survey_id)
    {
        // TODO: Need to check permissions
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $questionAnswerChoicesModel = new \App\Models\QuestionAnswerChoicesModel();

        $data['survey'] = $surveysModel->where('id', $survey_id)->first();
        $data['questions'] = $questionsModel->where('survey_id', $survey_id)->orderBy('question_number', 'ASC')->findAll();

        foreach ($data['questions'] as &$question) {
            $question['choices'] = $questionAnswerChoicesModel->where('question_id', $question['id'])->orderBy('position', 'ASC')->findAll();
        }
        unset($question);

        // TODO: Need to get survey response stuff for stats

        return view('survey_manage', $data);
    }

    public function edit($survey_id)
    {
        //
    }
}

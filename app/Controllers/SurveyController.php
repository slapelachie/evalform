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

        foreach($data['questions'] as &$question)
        {
            $question['choices'] = $questionAnswerChoicesModel->where('question_id', $question['id'])->orderBy('position', 'ASC')->findAll();
        }
        unset($question);

        return view('survey', $data);
    }

    public function handle_survey_response_error($status_code, $message) {
        log_message('error', $message);
        $this->response->setStatusCode($status_code)->setJSON(['error' => $message]);
    }

    public function survey_submit($survey_id)
    {
        // Load required models
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        // Convert json into an array
        $data = $this->request->getJSON(true);

        // Check if json is valid
        if (empty($data)) {
            return $this->handle_survey_response_error(400, 'Invalid JSON data: ' . json_last_error_msg());
        }

        // Start a transaction
        db_connect()->transBegin();

        // Create survey_response
        $survey_response_data = ['survey_id' => $survey_id];
        $survey_response_id = $surveyResponsesModel->insert($survey_response_data, true);

        // Process each survey question response
        foreach ($data as $question_response) {
            if(!isset($question_response['question_id'], $question_response['value'])) {
                db_connect()->transRollback();
                return $this->handle_survey_response_error(400, 'Missing question ID or value in one or more responses.');
            }

            $question_id = $question_response['question_id'];
            $answer = $question_response['value'];
            $question = $questionsModel->find($question_id);

            if (!$question) {
                db_connect()->transRollback();
                return $this->handle_survey_response_error(400, "Question not found with id $question_id");
            }

            $question_response_data = [
                'survey_response_id' => $survey_response_id,
                'question_id' => $question_id,
                'answer_id' => ($question['type'] == 'multiple_choice') ? $answer : null,
                'answer_text' => ($question['type'] == 'free_text') ? $answer : null,
            ];

            $questionResponsesModel->insert($question_response_data);
        }

        // Check if any errors occured, rollback if it has
        if (db_connect()->transStatus() === false) {
            db_connect()->transRollback();
            return $this->handle_survey_response_error(500, "Error processing your request");
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

        foreach($data['questions'] as &$question)
        {
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

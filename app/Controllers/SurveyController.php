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

        $data['survey'] = $surveysModel->find($survey_id);
        if ($data['survey'] == null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        $data['questions'] = $questionsModel->where('survey_id', $survey_id)->orderBy('question_number', 'ASC')->findAll();

        foreach ($data['questions'] as &$question) {
            $question['choices'] = $questionAnswerChoicesModel->where('question_id', $question['id'])->orderBy('position', 'ASC')->findAll();
        }
        unset($question);

        return view('survey', $data);
    }

    public function create()
    {
        $data['user_id'] = auth()->user()->id;
        return view('survey_create', $data);
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

    private function processQuestionResponses($post_data, $survey_response_id)
    {
        // Process each survey question response
        foreach ($post_data as $question_id => $answer) {
            $this->insertQuestionResponse($question_id, $survey_response_id, $answer);
        }

        return true;
    }

    public function surveySubmit($survey_id)
    {
        $surveysModel = new \App\Models\SurveysModel();

        $post_data = $this->request->getPost();

        // TODO: Check if survey actually exists

        // Check if post data is valid
        if (empty($post_data)) {
            return $this->handleSurveyResponseError(400, 'Invalid post data!');
        }

        // Start a transaction
        db_connect()->transBegin();

        // Create survey response
        $survey_response_id = $this->createSurveyResponse($survey_id);

        if (!$this->processQuestionResponses($post_data, $survey_response_id)) {
            // Error handling is done within the function
            db_connect()->transRollback();
            return;
        }

        // Check if any errors occured, rollback if it has
        if (db_connect()->transStatus() === false) {
            db_connect()->transRollback();
            // TODO: display end of survey page error
            return $this->handleSurveyResponseError(500, "Error processing your request");
        }

        db_connect()->transCommit();

        $data['survey'] = $surveysModel->find($survey_id);
        return view('survey_complete', $data);
    }

    public function manage($survey_id)
    {
        // TODO: Need to check permissions
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $questionAnswerChoicesModel = new \App\Models\QuestionAnswerChoicesModel();

        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        $data['survey'] = $surveysModel->find($survey_id);
        if ($data['survey'] == null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        $data['questions'] = $questionsModel->where('survey_id', $survey_id)->orderBy('question_number', 'ASC')->findAll();

        foreach ($data['questions'] as &$question) {
            $question_id = $question['id'];
            $question['choices'] = $questionAnswerChoicesModel->where('question_id', $question_id)->orderBy('position', 'ASC')->findAll();

            $total_response_count = count($questionResponsesModel->where('question_id', $question_id)->findAll());

            foreach ($question['choices'] as &$answer) {
                $answer_id = $answer['id'];
                $answer_response_count = count($questionResponsesModel->where('answer_id', $answer_id)->findAll());
                $data['responses'][$answer_id]['count'] = $answer_response_count;
                $data['responses'][$answer_id]['percent'] = $total_response_count != 0 ? round($answer_response_count / $total_response_count * 100) : 'N/A';
            }
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

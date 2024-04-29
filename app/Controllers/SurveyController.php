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

    public function thankYou()
    {
        return view("survey_complete");
    }
}

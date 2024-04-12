<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SurveyController extends BaseController
{
    public function index()
    {
        //
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
}

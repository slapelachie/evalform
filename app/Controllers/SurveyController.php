<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SurveyController extends BaseController
{
    public function index()
    {
        return view('surveys');
    }

    public function view($surveyId)
    {
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $data['survey'] = $surveysModel->find($surveyId);
        if ($data['survey'] == null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        $data['questions'] = $questionsModel->where('survey_id', $surveyId)->orderBy('question_number', 'ASC')->findAll();

        foreach ($data['questions'] as &$question) {
            $question['choices'] = $answersModel->where('question_id', $question['id'])->orderBy('position', 'ASC')->findAll();
        }
        unset($question);

        return view('survey', $data);
    }

    public function create()
    {
        $data['user_id'] = auth()->user()->id;
        return view('survey_create', $data);
    }


    public function manage($surveyId)
    {
        // TODO: Need to check permissions
        $surveysModel = new \App\Models\SurveysModel();

        $data['survey'] = $surveysModel->find($surveyId);
        if ($data['survey'] == null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        return view('survey_manage', $data);
    }

    public function edit($surveyId)
    {
        //
    }

    public function thankYou()
    {
        return view("survey_complete");
    }
}

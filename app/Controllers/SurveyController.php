<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SurveysModel;
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
        $userId = auth()->user()->id;
        return view('survey_form', [
            'user_id' => $userId,
            'survey' => null,
            'questions' => null,
        ]);
    }


    public function manage($surveyId)
    {
        $surveysModel = new \App\Models\SurveysModel();

        $data['survey'] = $surveysModel->find($surveyId);
        if ($data['survey'] == null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        if ($data['survey']['owner_id'] != auth()->user()->id) {
            return $this->response->setStatusCode(403)->setBody("Forbidden");
        }

        return view('survey_manage', $data);
    }

    public function edit($surveyId)
    {
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $userId = auth()->user()->id;

        // Check if survey exists
        $survey = $surveysModel->find($surveyId);
        if ($survey === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        // Check if current user has correct permissions
        if ($survey['owner_id'] != $userId) {
            return $this->response->setStatusCode(403)->setBody("Forbidden");
        }

        // Get questions
        $questions = $questionsModel->where('survey_id', $survey['id'])->findAll();

        // Get answers for questions
        foreach ($questions as &$question) {
            $answers = $answersModel->where('question_id', $question['id'])->findAll();
            $question['answers'] = $answers;
        }
        unset($question);

        return view('survey_form', [
            'user_id' => $userId,
            'survey' => $survey ?? [],
            'questions' => $questions ?? [],
        ]);
    }

    public function thankYou()
    {
        return view("survey_complete");
    }
}

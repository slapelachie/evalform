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

    public function export($surveyId = null)
    {
        $surveysModel = new \App\Models\SurveysModel();
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        // Get format to export as
        $format = $this->request->getGet('format') ?? "csv";

        // Check if survey exists
        $survey = $surveysModel->find($surveyId);
        if ($survey === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("This survey could not be found!");
        }

        // Check if have correct permissions
        if ($survey['owner_id'] != auth()->user()->id) {
            return $this->response->setStatusCode(403)->setBody("Forbidden");
        }

        $surveyResponses = $surveyResponsesModel->where('survey_id', $surveyId)->findAll();
        $data = [['id', 'survey_response_id', 'question', 'answer']];

        foreach ($surveyResponses as $surveyResponse) {
            $questionResponses = $questionResponsesModel->where('survey_response_id', $surveyResponse['id'])->findAll();

            foreach ($questionResponses as $questionResponse) {
                $question = $questionsModel->find($questionResponse['question_id']);

                // Get the answer to the question, it is either free text or the value of the mc
                $answerValue = $questionResponse['answer_text'];
                if ($questionResponse['answer_id'] != null) {
                    $answer = $answersModel->find($questionResponse['answer_id']);
                    $answerValue = $answer['answer'];
                }

                // Skip if the question or the answer do not exist
                if ($question == null || $answer == null) {
                    continue;
                }

                // Build the response
                $individualResponse = [$questionResponse['id'], $questionResponse['survey_response_id'], $question['question'], $answerValue];

                $data[] = $individualResponse;
            }
        }

        if ($format == "csv") {
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

        return $this->response
            ->setStatusCode(400)
            ->setBody("Format '$format' is not supported.");
    }

    public function thankYou()
    {
        return view("survey_complete");
    }
}

<?php

namespace App\Controllers\API;

use App\Models\QuestionsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class SurveyController extends ResourceController
{
    protected $modelName = 'App\Models\SurveysModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Survey not found with id: ' . $id);
        }

        return $this->respond($survey);
    }

    private function insertQuestions($questions, $surveyId)
    {
        $questionsModel = new \App\Models\QuestionsModel();

        foreach ($questions as $question) {
            $questionData = [
                'survey_id' => $surveyId,
                'type' => $question['type'],
                'question_number' => $question['question_number'],
                'question' => $question['question'],
            ];

            // Insert questions
            if (!$questionsModel->insert($questionData)) {
                $this->model->transRollback();
                return $this->fail($questionsModel->errors());
            }

            $questionId = $questionsModel->getInsertID();

            // Handle answers if they exist
            if (isset($question['answers']) && $question['type'] == 'multiple_choice') {
                $this->insertAnswers($question['answers'], $questionId);
            }
        }
    }

    private function insertAnswers($answers, $questionId)
    {
        $questionAnswerChoicesModel = new \App\Models\QuestionAnswerChoicesModel();

        foreach ($answers as $answer) {
            $answerData = [
                'question_id' => $questionId,
                'position' => $answer['position'],
                'answer' => $answer['answer'],
            ];

            if (!$questionAnswerChoicesModel->insert($answerData)) {
                $this->model->transRollback();
                return $this->fail($questionAnswerChoicesModel->errors());
            }
        }
    }

    public function create()
    {
        $this->model->transStart();

        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            $this->model->transRollback();
            return $this->fail($this->model->errors());
        }

        $surveyId = $this->model->getInsertID();

        // Handle questions if they exist
        if (isset($data['questions'])) {
            $this->insertQuestions($data['questions'], $surveyId);
        }

        $this->model->transComplete();

        $survey = $this->model->find($surveyId);

        return $this->respondCreated($survey);
    }

    public function update($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Survey not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            $updatedSurvey = $this->model->find($id);
            return $this->respondUpdated($updatedSurvey);
        }
        return $this->failServerError('Could not update the survey');
    }

    public function delete($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Survey not found with id: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted($survey);
        }
        return $this->failServerError('Could not delete the survey');
    }
}

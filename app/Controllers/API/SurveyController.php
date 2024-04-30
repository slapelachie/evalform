<?php

namespace App\Controllers\API;

use App\Models\QuestionsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class SurveyController extends ResourceController
{
    protected $modelName = 'App\Models\SurveysModel';
    protected $format = 'json';

    private function getModelErrorMessage($model)
    {
        $errors = $model->errors();
        $errorMessage = is_array($errors) ? implode('; ', $errors) : $errors;
        return $errorMessage;
    }

    private function getQuestions($surveyId)
    {
        $questionsModel = new \App\Models\QuestionsModel();
        $answersModel = new \App\Models\AnswersModel();

        $questions = $questionsModel->where('survey_id', $surveyId)->findAll();

        foreach ($questions as &$question) {
            $answers = $answersModel->where('question_id', $question['id'])->findAll();
            $question['answers'] = $answers;
        }
        unset($question);

        return $questions;
    }

    public function index()
    {
        $ownerId = $this->request->getGet('owner_id');
        $status = $this->request->getGet('status');
        $count = $this->request->getGet('count');

        $query = $this->model;

        if ($ownerId !== null) {
            $query = $query->where("owner_id", $ownerId);
        }

        if ($status !== null) {
            $query = $query->where("status", $status);
        }

        // Count results and send it as a response
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $surveys = $query->findall();

        foreach ($surveys as &$survey) {
            $questions = $this->getQuestions($survey['id']);
            $survey['questions'] = $questions;
        }
        unset($survey);

        return $this->respond($surveys);
    }

    public function show($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Survey not found with id: ' . $id);
        }

        $survey['questions'] = $this->getQuestions($id);

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
                $errorMessage = $this->getModelErrorMessage($questionsModel);
                throw new \Exception($errorMessage);
            }

            $questionId = $questionsModel->getInsertID();

            // Handle answers if they exist
            if (isset($question['answers']) && $question['type'] == 'multiple_choice') {
                try {
                    $this->insertAnswers($question['answers'], $questionId);
                } catch (\Exception $error) {
                    throw $error;
                }
            }
        }
    }

    private function insertAnswers($answers, $questionId)
    {
        $answersModel = new \App\Models\AnswersModel();

        foreach ($answers as $answer) {
            $answerData = [
                'question_id' => $questionId,
                'position' => $answer['position'],
                'answer' => $answer['answer'],
            ];

            if (!$answersModel->insert($answerData)) {
                $errorMessage = $this->getModelErrorMessage($answersModel);
                throw new \Exception($errorMessage);
            }
        }
    }

    public function create()
    {
        $this->model->transStart();

        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            $this->model->transRollback();
            $errorMessage = $this->getModelErrorMessage($this->model);
            return $this->fail($errorMessage);
        }

        $surveyId = $this->model->getInsertID();

        // Handle questions if they exist
        if (isset($data['questions'])) {
            try {
                $this->insertQuestions($data['questions'], $surveyId);
            } catch (\Exception $error) {
                $this->model->transRollback();
                return $this->fail($error->getMessage());
            }
        }

        $this->model->transComplete();

        $survey = $this->model->find($surveyId);

        $survey["questions"] = $this->getQuestions($surveyId);

        return $this->respondCreated($survey);
    }

    public function update($id = null)
    {
        // TODO
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

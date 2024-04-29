<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;

class ResponsesController extends ResourceController
{
    protected $modelName = 'App\Models\SurveyResponsesModel';
    protected $format = 'json';

    private function getModelErrorMessage($model)
    {
        $errors = $model->errors();
        $errorMessage = is_array($errors) ? implode('; ', $errors) : $errors;
        return $errorMessage;
    }

    private function getResponses($surveyResponseId)
    {
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();

        $questionResponses = $questionResponsesModel->where('survey_response_id', $surveyResponseId)->findAll();

        return $questionResponses;
    }

    public function index()
    {
        $surveyResponses = $this->model->findall();

        foreach ($surveyResponses as &$surveyResponse) {
            $responses = $this->getResponses($surveyResponse['id']);
            $surveyResponse['responses'] = $responses;
        }
        unset($surveyResponse);

        return $this->respond($surveyResponses);
    }

    public function show($id = null)
    {
        $surveyResponse = $this->model->find($id);
        if ($surveyResponse == null) {
            return $this->failNotFound("Survey response not found with id: " . $id);
        }

        $surveyResponse['responses'] = $this->getResponses($id);

        return $this->respond($surveyResponse);
    }


    private function insertResponses($responses, $surveyResponseId)
    {
        $questionResponsesModel = new \App\Models\QuestionResponsesModel();
        $questionsModel = new \App\Models\QuestionsModel();

        foreach ($responses as $response) {
            $questionId = $response['question_id'];
            $responseData = [
                'survey_response_id' => $surveyResponseId,
                'question_id' => $questionId,
                'answer_id' => null,
                'answer_text' => null,
            ];

            $question = $questionsModel->find($questionId);
            if ($question['type'] == 'multiple_choice') {
                $responseData['answer_id'] = $response['answer'];
            } elseif ($question['type'] == 'free_text') {
                $responseData['answer_text'] = $response['answer'];
            }

            if (!$questionResponsesModel->insert($responseData)) {
                $errorMessage = $this->getModelErrorMessage($questionResponsesModel);
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

        $surveyResponseId = $this->model->getInsertId();

        if (isset($data['responses'])) {
            try {
                $this->insertResponses($data['responses'], $surveyResponseId);
            } catch (\Exception $error) {
                $this->model->transRollback();
                log_message('debug', 'error thrown in insert response insert ' . $error->getMessage());
                return $this->fail($error->getMessage());
            }
        }

        $this->model->transComplete();

        $surveyResponse = $this->model->find($surveyResponseId);
        $surveyResponse['responses'] = $this->getResponses($surveyResponseId);

        return $this->respondCreated($surveyResponse);
    }
}

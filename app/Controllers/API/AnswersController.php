<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class AnswersController extends ResourceController
{
    protected $modelName = 'App\Models\AnswersModel';
    protected $format = 'json';

    public function index()
    {
        $questionId = $this->request->getGet('question_id');
        $count = $this->request->getGet('count');

        $query = $this->model;

        if ($questionId !== null) {
            $query = $query->where('question_id', $questionId);
        }

        // Count results and send it as a response
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $answers = $query->findall();
        return $this->respond($answers);
    }

    public function show($id = null)
    {
        $answer = $this->model->find($id);
        if ($answer == null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        return $this->respond($answer);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        $answerId = $this->model->getInsertID();
        $answer = $this->model->find($answerId);

        return $this->respondCreated($answer);
    }

    public function update($id = null)
    {
        $answer = $this->model->find($id);
        if ($answer == null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            $updatedAnswer = $this->model->find($id);
            return $this->respondUpdated($updatedAnswer);
        }
        return $this->failServerError('Could not update the answer');
    }

    public function delete($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Answer not found with id: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted($survey);
        }
        return $this->failServerError('Could not delete the answer');
    }
}

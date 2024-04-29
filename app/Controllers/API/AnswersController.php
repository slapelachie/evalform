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

        $query = $this->model;

        if ($questionId !== null) {
            $query = $query->where('question_id', $questionId);
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

        $answer_id = $this->model->getInsertID();
        $answer = $this->model->find($answer_id);

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
            $updated_answer = $this->model->find($id);
            return $this->respondUpdated($updated_answer);
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

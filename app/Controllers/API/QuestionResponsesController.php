<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class QuestionResponsesController extends ResourceController
{
    protected $modelName = 'App\Models\QuestionResponsesModel';
    protected $format = 'json';

    public function index()
    {
        $questionId = $this->request->getGet('question_id');
        $answerId = $this->request->getGet('answer_id');
        $count = $this->request->getGet('count');

        $query = $this->model;

        if ($questionId !== null) {
            $query = $query->where('question_id', $questionId);
        }

        if ($answerId !== null) {
            $query = $query->where('answer_id', $answerId);
        }

        // Count results and send it as a response
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $responses = $query->findall();
        return $this->respond($responses);
    }

    public function show($id = null)
    {
        $response = $this->model->find($id);
        if ($response == null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        return $this->respond($response);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        $responseId = $this->model->getInsertID();
        $response = $this->model->find($responseId);

        return $this->respondCreated($response);
    }

    public function update($id = null)
    {
        $response = $this->model->find($id);
        if ($response == null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            $updatedResponse = $this->model->find($id);
            return $this->respondUpdated($updatedResponse);
        }
        return $this->failServerError('Could not update the question response');
    }

    public function delete($id = null)
    {
        $response = $this->model->find($id);
        if ($response == null) {
            return $this->failNotFound('Question response not found with id: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted($response);
        }
        return $this->failServerError('Could not delete the question response');
    }
}

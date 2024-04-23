<?php

namespace App\Controllers\API;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class QuestionsController extends ResourceController
{
    protected $modelName = 'App\Models\QuestionsModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $question = $this->model->find($id);
        if ($question == null) {
            return $this->failNotFound('Question not found with id: ' . $id);
        }

        return $this->respond($question);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        $question_id = $this->model->getInsertID();
        $question = $this->model->find($question_id);

        return $this->respondCreated($question);
    }

    public function update($id = null)
    {
        $question = $this->model->find($id);
        if ($question == null) {
            return $this->failNotFound('Question not found with id: ' . $id);
        }

        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            $updated_question = $this->model->find($id);
            return $this->respondUpdated($updated_question);
        }
        return $this->failServerError('Could not update the question');
    }

    public function delete($id = null)
    {
        $survey = $this->model->find($id);
        if ($survey == null) {
            return $this->failNotFound('Question not found with id: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted($survey);
        }
        return $this->failServerError('Could not delete the question');
    }
}

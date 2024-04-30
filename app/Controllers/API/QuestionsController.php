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
        $surveyId = $this->request->getGet('survey_id');
        $count = $this->request->getGet('count');

        $query = $this->model;

        if ($surveyId !== null) {
            $query = $query->where('survey_id', $surveyId);
        }

        // Count results and send it as a response
        if (isset($count)) {
            $responseCount = $query->countAllResults();
            return $this->respond(['count' => $responseCount]);
        }

        $questions = $query->findall();
        return $this->respond($questions);
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

        $questionId = $this->model->getInsertID();
        $question = $this->model->find($questionId);

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
            $updatedQuestion = $this->model->find($id);
            return $this->respondUpdated($updatedQuestion);
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

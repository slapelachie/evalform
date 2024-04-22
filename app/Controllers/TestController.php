<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TestController extends BaseController
{
    public function index($survey_id)
    {
        $surveysModel = new \App\Models\SurveysModel();

        // TODO: Check for 404
        $data['survey'] = $surveysModel->find($survey_id);


        return view('survey_complete', $data);
    }
}

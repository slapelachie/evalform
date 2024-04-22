<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $userModel = new \App\Models\UsersModel();
        $businessModel = new \App\Models\BusinessesModel();
        $surveyModel = new \App\Models\SurveysModel();
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();

        // TODO: Need to redirect to login if not logged in

        // TODO: Get user info
        $user_id = 1;
        $user_data = $userModel->find($user_id);

        // TODO: Add logic between business and personal
        $data['business_name'] = '';

        if ($user_data['business_id'] != null) {
            $business_data = $businessModel->where('id', $user_data['business_id'])->first();
            $data['business_name'] = $business_data['name'];
        }

        // TODO: Get list of surveys
        $data['surveys'] = $surveyModel->where('owner_id', $user_id)->findAll();

        // TODO: Get insights
        $publish_count = 0;
        $draft_count = 0;
        $survey_response_count = 0;

        foreach ($data['surveys'] as $survey) {
            if($survey['status'] == 'published') {
                $publish_count++;
            } else {
                $draft_count++;
            }

            $survey_response_count += count($surveyResponsesModel->where('survey_id', $survey['id'])->findAll());
        }

        $data['insights'] = [
            'publishes' => [
                'name' => 'Published Surveys',
                'value' => $publish_count,
            ],
            'drafts' => [
                'name' => 'Drafted Surveys',
                'value' => $draft_count,
            ],
            'views' => [
                'name' => 'Survey Views',
                'value' => 0,
            ],
            'answers' => [
                'name' => 'Survey Responses',
                'value' => $survey_response_count,
            ],
        ];

        return view('dashboard', $data);
    }
}

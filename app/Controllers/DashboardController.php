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

        $user_id = auth()->user()->id;
        $user_data = $userModel->find($user_id);

        // TODO: Add logic between business and personal
        $data['business_name'] = '';

        $business_id = $user_data->business_id;
        if ($business_id != null) {
            $business_data = $businessModel->find($business_id);
            $data['business_name'] = $business_data['name'];
        }

        // TODO: Get list of surveys
        $data['surveys'] = $surveyModel->where('owner_id', $user_id)->findAll();

        // TODO: Get insights
        $publish_count = 0;
        $draft_count = 0;
        $survey_response_count = 0;

        foreach ($data['surveys'] as $survey) {
            if ($survey['status'] == 'published') {
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
                'link' => base_url('surveys?status=published'),
            ],
            'drafts' => [
                'name' => 'Drafted Surveys',
                'value' => $draft_count,
                'link' => base_url('surveys?status=draft'),
            ],
            'views' => [
                'name' => 'Survey Views',
                'value' => 0,
                'link' => null,
            ],
            'answers' => [
                'name' => 'Survey Responses',
                'value' => $survey_response_count,
                'link' => null,
            ],
        ];

        return view('dashboard', $data);
    }
}

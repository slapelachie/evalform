<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $surveyModel = new \App\Models\SurveysModel();
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();

        $userId = auth()->user()->id;

        // Get list of surveys
        $data['surveys'] = $surveyModel->where('owner_id', $userId)->findAll();

        // Get insights
        $publishCount = 0;
        $draftCount = 0;
        $surveyResponseCount = 0;

        foreach ($data['surveys'] as $survey) {
            if ($survey['status'] == 'published') {
                $publishCount++;
            } else {
                $draftCount++;
            }

            $surveyResponseCount += count($surveyResponsesModel->where('survey_id', $survey['id'])->findAll());
        }

        // TODO: See if there is a good alternative to survey views
        $data['insights'] = [
            'publishes' => [
                'name' => 'Published Surveys',
                'value' => $publishCount,
                'link' => base_url('surveys?status=published'),
            ],
            'drafts' => [
                'name' => 'Drafted Surveys',
                'value' => $draftCount,
                'link' => base_url('surveys?status=draft'),
            ],
            'views' => [
                'name' => 'Survey Views',
                'value' => 0,
                'link' => null,
            ],
            'answers' => [
                'name' => 'Survey Responses',
                'value' => $surveyResponseCount,
                'link' => null,
            ],
        ];

        return view('dashboard', $data);
    }
}

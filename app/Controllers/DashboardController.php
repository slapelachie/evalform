<?php

namespace App\Controllers;

/**
 * Class DashboardController
 *
 * Handles data aggregation and insights for the user dashboard
 */
class DashboardController extends BaseController
{
    /**
     * Display the dashboard view with insights on surveys.
     *
     * @return string Rendered dashboard view
     */
    public function index(): string
    {
        $surveyModel = new \App\Models\SurveysModel();
        $surveyResponsesModel = new \App\Models\SurveyResponsesModel();

        $userId = auth()->user()->id;

        // Retrieve all surveys owned by the current user
        $data['surveys'] = $surveyModel->where('owner_id', $userId)->findAll();

        // Initialise insight counts
        $publishCount = 0;
        $draftCount = 0;
        $surveyResponseCount = 0;

        // Iterate over surveys to compute insights based on status and responses
        foreach ($data['surveys'] as $survey) {
            if ($survey['status'] == 'published') {
                $publishCount++;
            } else {
                $draftCount++;
            }

            $surveyResponseCount += $surveyResponsesModel
                ->where('survey_id', $survey['id'])
                ->countAllResults();
        }

        // Prepare aggregated insights for the dashboard
        $data['insights'] = [
            'publishes' => [
                'name' => 'Published Surveys',
                'value' => $publishCount,
                'link' => base_url('surveys?status=published'),
                'size' => 'auto',
            ],
            'drafts' => [
                'name' => 'Drafted Surveys',
                'value' => $draftCount,
                'link' => base_url('surveys?status=draft'),
                'size' => 'auto',
            ],
            'answers' => [
                'name' => 'Survey Responses',
                'value' => $surveyResponseCount,
                'link' => null,
                'size' => 12,
            ],
        ];

        return view('dashboard', $data);
    }
}

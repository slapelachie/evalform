<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1>Your Surveys</h1>
            <button class="btn btn-outline-primary" type="button" onclick="refreshSurveys()">Refresh</button>
        </div>
        <div id="alert"></div>
        <div class="my-3">
            <div class="row align-items-end my-3">
                <div class="col-md">
                    <label for="questionTypeFilter" class="form-label">Question Type:</label>
                    <select id="surveyStatusFilter" class="form-select">
                        <option value="any" selected>Any</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="col-md-auto ms-auto">
                    <button type="button" class="btn btn-primary w-100 w-md-auto" onclick="presentSurveys()">Apply Filters</button>
                </div>
            </div>
        </div>
        <table id="surveyTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="width: 10%;">Responses</th>
                    <th class="w-25 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <div id="loadingContainer" class="my-5 d-flex justify-content-center align-items-center w-100" style="height: 20vw;">
            <span class="display-5 ">Loading...</span>
        </div>
        <div id="paginationContainer"></div>
    </div>
</section>

<div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSurveyLabel">Delete Survey</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <button id="confirmSurveyDeleteButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="deleteSurvey()">Yes, delete this survey.</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<template id="surveyRowTemplate">
    <tr>
        <td class="survey-name"></td>
        <td class="survey-responses"></td>
        <td class="survey-actions text-end">
            <a class="manage-button btn btn-primary btn-sm" href="#">Manage</a>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal" data-survey-id="" data-survey-name="">Delete</button>
        </td>
    </tr>
</template>

<?= view('snippets/common_scripts') ?>
<?= view('snippets/api_scripts') ?>

<script>
    async function deleteSurvey(surveyId) {
        const apiUrl = `<?= base_url('/api/surveys/') ?>${surveyId}`;

        try {
            await makeDeleteAPICall(apiUrl);
        } catch (error) {
            appendAlert("Failed to delete the survey! Please try again later.", "danger");
            console.error(error);
            return;
        }

        presentSurveys();
        return;
    }

    async function getSurveys(surveyStatus = null, page = 1) {
        apiUrl = `<?= base_url('/api/surveys?owner_id=') . auth()->user()->id ?>`;

        if (surveyStatus != null) {
            apiUrl += `&status=${surveyStatus}`;
        }

        if (page != 1) {
            apiUrl += `&page=${page}`
        }

        try {
            return await makeGetAPICall(apiUrl);
        } catch (error) {
            throw error;
        }
    }

    async function getSurveyResponseCount(surveyId) {
        apiUrl = `<?= base_url('/api/responses') ?>?survey_id=${surveyId}&count`;

        try {
            var responses = await makeGetAPICall(apiUrl);
        } catch (error) {
            throw error;
        }

        return responses.count;
    }

    async function generateSurveyRow(survey) {
        const template = document.getElementById("surveyRowTemplate");
        const newSurvey = template.content.cloneNode(true);

        const surveyResponseCount = await getSurveyResponseCount(survey["id"]);
        newSurvey.querySelector(".survey-name").textContent = survey['name'];
        newSurvey.querySelector(".survey-responses").textContent = surveyResponseCount;
        newSurvey.querySelector(".manage-button").href = `<?= base_url('surveys') ?>/${survey["id"]}/manage`;

        const deleteButton = newSurvey.querySelector(".delete-button");
        deleteButton.dataset.surveyId = survey["id"];
        deleteButton.dataset.surveyName = survey["name"];

        return newSurvey;
    }

    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    async function presentSurveys() {
        const surveyTable = document.getElementById("surveyTable");
        const paginationContainer = document.getElementById("paginationContainer");
        const loadingContainer = document.getElementById("loadingContainer");
        const surveyTableBody = surveyTable.querySelector("tbody");

        const surveyStatusFilter = document.getElementById('surveyStatusFilter')
        const surveyStatusValue = surveyStatusFilter.value != "any" ? surveyStatusFilter.value : null;
        const pageParam = getQueryParam('page') ?? 1;

        try {
            var paginatedSurveys = await getSurveys(surveyStatusValue, pageParam);
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            return;
        }

        // Show loading element
        loadingContainer.classList.remove('d-none');

        // Clear current surveys if they are already presented
        surveyTableBody.innerHTML = '';

        const surveys = paginatedSurveys['results'];
        const pagination = paginatedSurveys['pagination'];

        // Replace api calls with regular calls
        const paginationLinks = pagination['links'].replace(/\/api/g, '');

        paginationContainer.innerHTML = paginationLinks;

        // Append each survey to the table
        let surveyRows = [];
        for (const survey of surveys) {
            surveyRows.push(await generateSurveyRow(survey));
        }

        loadingContainer.classList.add("d-none");

        for (const surveyRow of surveyRows) {
            surveyTableBody.append(surveyRow);
        }
    }

    async function refreshSurveys() {
        // function exists in case I want to change this
        await presentSurveys();
    }

    document.addEventListener('DOMContentLoaded', async function() {
        // Get the query params
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        // Apply any filters
        if (status) {
            const surveyStatusFilter = document.getElementById("surveyStatusFilter");
            surveyStatusFilter.value = status;
        }

        // Display survey list
        await presentSurveys();

        // Setup buttons
        const surveyTable = document.getElementById("surveyTable");
        const deleteModal = document.getElementById("deleteSurveyModal");
        const deleteButton = document.getElementById("confirmSurveyDeleteButton");
        const deleteModalLabel = document.getElementById("deleteSurveyLabel");

        surveyTable.querySelector('tbody').addEventListener('click', function(event) {
            const target = event.target;
            if (target.classList.contains('delete-button')) {
                const surveyId = target.dataset.surveyId;
                const surveyName = target.dataset.surveyName;

                deleteModalLabel.textContent = `Delete Survey "${surveyName}"`;
                deleteButton.onclick = function() {
                    deleteSurvey(surveyId);
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>
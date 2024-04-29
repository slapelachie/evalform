<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1>Your Surveys</h1>
            <button class="btn btn-outline-primary" type="button" onclick="refreshSurveys()">Refresh</button>
        </div>
        <div id="errorSurveyFetchAlert"></div>
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

<script>
    const alertPlaceHolder = document.getElementById('errorSurveyFetchAlert')

    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('');

        alertPlaceHolder.append(wrapper);
    }

    async function deleteSurvey(surveyId) {
        try {
            const response = await fetch(`<?= base_url('/api/surveys/') ?>${surveyId}`, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            try {
                await response.json();
            } catch (error) {
                throw error;
            }
        } catch (error) {
            appendAlert("Failed to delete the survey! Please try again later.", "danger");
            console.error(error);
            return;
        }

        presentSurveys();
        return;
    }

    async function makeAPICall(apiUrl) {
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
            });

            if (!response.ok) {
                const errorResponse = await response.json();
                console.error(`API request failed with status ${response.status}: ${response.statusText}\n`, errorResponse);
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            throw error;
        }
    }

    async function getSurveys() {
        apiUrl = `<?= base_url('/api/surveys?owner_id=') . auth()->user()->id ?>`;

        try {
            return await makeAPICall(apiUrl)
        } catch (error) {
            throw error;
        }
    }

    async function getSurveyResponseCount(surveyId) {
        apiUrl = `<?= base_url('/api/responses?survey_id=') ?>${surveyId}`;

        try {
            var responses = await makeAPICall(apiUrl);
        } catch (error) {
            throw error;
        }

        return responses.length;
    }

    async function presentSurveys() {
        const surveyTable = document.getElementById("surveyTable");
        const surveyTableBody = surveyTable.querySelector("tbody");

        surveyTableBody.innerHTML = "";

        const template = document.getElementById("surveyRowTemplate");

        try {
            var surveys = await getSurveys();
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            return;
        }

        for (const survey of surveys) {
            const newSurvey = template.content.cloneNode(true);

            const surveyResponseCount = await getSurveyResponseCount(survey["id"]);
            newSurvey.querySelector(".survey-name").textContent = survey['name'];
            newSurvey.querySelector(".survey-responses").textContent = surveyResponseCount;
            newSurvey.querySelector(".manage-button").href = `<?= base_url('surveys') ?>/${survey["id"]}/manage`;

            const deleteButton = newSurvey.querySelector(".delete-button");
            deleteButton.dataset.surveyId = survey["id"];
            deleteButton.dataset.surveyName = survey["name"];

            surveyTableBody.appendChild(newSurvey);
        }
    }

    async function refreshSurveys() {
        await presentSurveys();
    }

    document.addEventListener('DOMContentLoaded', async function() {
        await presentSurveys();

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
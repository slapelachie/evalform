<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1>Your Surveys</h1>
            <button id="refreshButton" class="btn btn-outline-primary" type="button">Refresh</button>
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
                    <button id="applyFiltersButton" type="button" class="btn btn-primary w-100 w-md-auto">Apply Filters</button>
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

<?= view('snippets/qrcode_modal') ?>

<div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSurveyLabel">Delete Survey</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <button id="confirmSurveyDeleteButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Yes, delete this survey.</button>
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
            <button class="share-button btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareSurveyModal">Share</button>
            <a class="manage-button btn btn-primary btn-sm" href="#">Manage</a>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal" data-survey-id="" data-survey-name="">Delete</button>
        </td>
    </tr>
</template>

<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script src="<?= base_url('/js/survey/surveys.js') ?>"></script>

<script src="<?= base_url('/js/utils.js') ?>"></script>
<script src="<?= base_url('/js/api.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const rootUrl = '<?= base_url('/') ?>';
        const userId = '<?= auth()->user()->id ?>';
        let apiUrl = `${rootUrl}/api`

        initialiseEventHandlers(apiUrl, rootUrl, userId);

        applyFiltersFromQueryParams();

        // Display survey list
        await presentSurveys(apiUrl, rootUrl, userId);

        setupSurveyTableButtonListeners(apiUrl, rootUrl, userId);
    });
</script>

<?= $this->endSection() ?>

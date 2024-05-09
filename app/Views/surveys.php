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
                <div class="col-lg mb-2 mb-lg-0">
                    <label for="questionTypeFilter" class="form-label">Question Type:</label>
                    <select id="surveyStatusFilter" class="form-select">
                        <option value="any" selected>Any</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="col-lg-auto ms-auto">
                    <button id="applyFiltersButton" type="button" class="btn btn-primary w-100 w-lg-auto">Apply Filters</button>
                </div>
            </div>
        </div>
        <table id="surveyTable" class="table table-responsive table-hover table-striped" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th scope="col" class="w-50">Name</th>
                    <th scope="col" style="width: 20%;">Responses</th>
                    <th scope="col" class="w-25 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
            </tbody>
        </table>
        <div
            id="loadingContainer"
            class="my-5 d-flex justify-content-center align-items-center w-100 flex-column flex-lg-row"
        >
            <div class="spinner-grow" aria-hidden="true"></div>
            <span class="pt-3 pt-lg-0 ps-0 ps-lg-3 status display-5 ">Loading...</span>
        </div>
        <div class="row">
            <div id="paginationContainer" class="col-lg"></div>
            <div class="input-group col-lg">
                <span class="input-group-text" id="inputGroup-sizing-sm">Results Per Page</span>
                <select class="form-select" aria-label="Results per page">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select> 
            </div>
        </div>
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
        <td class="survey-name text-truncate"></td>
        <td class="survey-responses"></td>
        <td class="survey-actions text-end">
            <!-- Display only the manage button on smaller screen sizes -->
            <button class="share-button btn btn-outline-primary btn-sm d-none d-lg-inline" data-bs-toggle="modal" data-bs-target="#shareSurveyModal">Share</button>
            <a class="manage-button btn btn-primary btn-sm" href="#">Manage</a>
            <button class="delete-button btn btn-danger btn-sm d-none d-lg-inline" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal" data-survey-id="" data-survey-name="">Delete</button>
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

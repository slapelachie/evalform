<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div id="surveyContainer" class="container" data-survey-id='<?= $survey['id'] ?>'>
        <div class="mb-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-2">
                <h1 class="display-5 mb-2">Manage "<?= $survey['name'] ?>"</h1>
                <div>
                    <button 
                        class="btn btn-primary btn-sm dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a
                                type="button"
                                class="dropdown-item"
                                data-bs-toggle="modal"
                                data-bs-target="#shareSurveyModal"
                            >
                                Share
                            </a>
                        </li>
                        <li>
                            <a
                                type="button"
                                class="dropdown-item"
                                href="<?= base_url('/surveys/') . $survey['id'] ?>/export"
                            >
                                Export
                            </a>
                        </li>
                        <li>
                            <a
                                type="button"
                                class="dropdown-item"
                                href="<?= base_url('/surveys/') . $survey['id'] ?>"
                            >
                                View
                            </a>
                        </li>
                    </ul>
                    <a
                        href="<?= base_url('/surveys/') . $survey['id'] ?>/edit"
                        class="btn btn-warning btn-sm"
                    >
                        Edit
                    </a>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteSurveyModal"
                    >
                        Delete
                    </button>
                </div>
            </div>
            <p class="text-muted"><?= $survey['description'] ?></p>
        </div>
        <div class="mb-3">
            <div class="d-flex align-items-center">
                <h2 class="me-3 display-6">Analytics</h2>
            </div>
            <button
                id="filterToggleButton"
                class="btn btn-primary d-block d-lg-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#filtersContainer"
                aria-expanded="false"
                aria-controls="filtersContainer"
            >
                Show filters
            </button>
            <div id="filtersContainer" class="row align-items-end my-3 collapse show">
                <div class="pb-3 pb-lg-0 col-lg-3">
                    <label for="startDateFilter" class="form-label">From:</label>
                    <input type="date" class="form-control" id="startDateFilter">
                </div>
                <div class="pb-3 pb-lg-0 col-lg-3">
                    <label for="endDateFilter" class="form-label">To:</label>
                    <input type="date" class="form-control" id="endDateFilter">
                </div>
                <div class="pb-3 pb-lg-0 col-lg">
                    <label for="questionTypeFilter" class="form-label">Question Type:</label>
                    <select id="questionTypeFilter" class="form-select">
                        <option value="" selected>Any</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="free_text">Free Text</option>
                    </select>
                </div>
                <div class="pb-3 pb-lg-0 d-flex justify-content-between col-lg-auto">
                    <button id="applyFiltersButton" type="button" class="btn btn-primary">
                        Apply Filters
                    </button>
                    <button id="resetFiltersButton" type="button" class="btn btn-outline-danger mx-2">
                        Reset Filters
                    </button>
                    <button id="refreshCountsButton" type="button" class="btn btn-outline-primary">
                        Refresh
                    </button>
                </div>
            </div>
            <div class="my-3">
                <div id="accordionQuestionsContainer" class="accordion mb-3">
                </div>
                <?php if ($survey['status'] == 'draft'): ?>
                    <div class="mb-3 d-grid">
                        <div id="alert"></div>
                        <button
                            id="publishSurveyButton"
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#publishSurveyModal"
                        >
                            Publish Survey
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?= view('snippets/qrcode_modal') ?>

<div
    class="modal fade"
    id="deleteSurveyModal"
    tabindex="-1"
    aria-labelledby="deleteSurveyModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSurveyLabel">
                    Delete Survey "<?= $survey['name'] ?>"?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body d-grid gap-3">
                <p>This cannot be undone!</p>
                <button
                    id="confirmSurveyDeleteButton"
                    type="button"
                    class="btn btn-outline-danger"
                    data-bs-dismiss="modal"
                >
                    Yes, delete this survey.
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="publishSurveyModal"
    tabindex="-1"
    aria-labelledby="publishSurveyModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishSurveyLabel">
                    Publish Survey "<?= $survey['name'] ?>"?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body d-grid gap-3">
                <p>This cannot be undone!</p>
                <button
                    id="confirmSurveyPublishButton"
                    type="button"
                    class="btn btn-outline-danger"
                    data-bs-dismiss="modal"
                >
                    Yes, publish this survey.
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<template id="questionAccordion">
    <div class="question-item accordion-item">
        <h2 class="accordion-header">
            <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                aria-expanded="false"
            >
            </button>
        </h2>
        <div class="accordion-collapse collapse" data-bs-parent="#accordionQuestionsContainer">
            <div class="accordion-body">
            </div>
        </div>
    </div>
</template>

<template id="multipleChoiceAccordion">
    <table class="table table-striped align-middle text-center">
        <thead>
            <tr>
                <th>Answer</th>
                <th>Responses</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</template>

<template id="multipleChoiceAnswerRow">
    <tr class="answer-row">
        <td class="mc-answer text-start"></td>
        <td class="mc-response-count"></td>
        <td class="mc-response-percent"></td>
    </tr>
</template>

<template id="freetextAccordion">
    <table class="table">
        <thead>
            <tr>
                <th>Responses</th>
                <th>Sentiment</th>
                <th>Keywords</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>123</td>
                <td class="text-danger"><i class="bi bi-x"></i> Negative</td>
                <td>Lorem, Ipsum, Dolor</td>
                <td><button class="btn btn-primary btn-sm">Review</button></td>
            </tr>
        </tbody>
    </table>
</template>

<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script src="<?= base_url('/js/utils.js') ?>"></script>
<script src="<?= base_url('/js/api.js') ?>"></script>
<script src="<?= base_url('/js/survey/survey_manage.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const rootUrl = '<?= base_url('/') ?>';
        const apiUrl = `${rootUrl}/api`;

        const surveyContainer = document.getElementById('surveyContainer');
        const surveyId = surveyContainer.dataset.surveyId;

        setupFilterToggleButton();

        try {
            await setupAccordion(apiUrl);
        } catch (error) {
            console.error('Error setting up accordion: ', error);
        }

        try {
            await setupClickListeners(rootUrl, apiUrl, surveyId);
        } catch (error) {
            console.error('Error setting up click listeners: ', error);
        }

        // Setup QRCode
        new QRCode(document.getElementById("qrcode"), `${rootUrl}/surveys/${surveyId}`);
    })
</script>

<?= $this->endSection() ?>

<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3"><?= isset($survey)
            ? "Edit \"" . $survey['name'] . "\""
            : 'Create a Survey' ?></h1>
        <form id="surveyForm" class="needs-validation" data-user-id="<?= $user_id ?>" novalidate>
            <div class="mb-3">
                <label for="surveyTitle">
                    Title of Survey <span class="text-danger">*</span>
                </label>
                <input id="surveyTitle" name="survey-title" type="text" class="form-control" value="" required>
                <div class="invalid-feedback">
                    Please specify a survey title.
                </div>
            </div>
            <div id="questionsContainer">

            </div>
            <div class="mb-3 d-grid">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal" id="addQuestionButton">Add Question</button>
            </div>
            <div class="mb-3 d-grid">
                <div id="alert"></div>
                <button type="submit" id="<?= isset($survey)
                    ? 'edit'
                    : 'create' ?>SurveyButton" type="button" class="btn btn-primary"><?= isset(
    $survey
)
    ? 'Save'
    : 'Create' ?> Survey</button>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuestionLabel">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <button id="addMultipleChoiceQuestionButton" type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Add Multiple Choice Question</button>
                <button id="addFreeTextQuestionButton" type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Add Free Text Question</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<template id="multipleChoiceQuestionTemplate">
    <div class="question-container card mb-3" data-question-type="multiple_choice">
        <div class="card-body">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="questionTitle">
                        Multiple Choice Question Title <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control" required>
                <div class="invalid-feedback">
                    Please specify a question title.
                </div>
            </div>
            <div>
                <label for="answers">
                    Answers
                </label>
                <div class="answers-container">
                </div>
                <div class="d-grid">
                    <button type="button" class="add-answer-button btn btn-outline-primary btn-sm">Add Answer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="answerTemplate">
    <div class="answer-container input-group mb-2">
        <input type="text" class="form-control" placeholder="" required>
        <button type="button" class="delete-answer-button btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        <div class="invalid-feedback">
            Please specify an answer.
        </div>
    </div>
</template>

<template id="freeTextQuestionTemplate">
    <div class="question-container card mb-3" data-question-type="free_text">
        <div class="card-body">
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="questionTitle">
                        Free Text Question Title <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control" required>
                <div class="invalid-feedback">
                    Please specify a question title.
                </div>
            </div>
        </div>
    </div>
</template>

<?= view('snippets/api_scripts') ?>
<?= view('snippets/common_scripts') ?>

<script src="<?= base_url('/js/survey/survey_form.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const apiUrl = "<?= base_url('api/') ?>"

        const addMultipleChoiceQuestionButton = document.getElementById('addMultipleChoiceQuestionButton');
        const addFreeTextQuestionButton = document.getElementById('addFreeTextQuestionButton');

        addMultipleChoiceQuestionButton.addEventListener('click', () => newQuestionButton('multipleChoiceQuestionTemplate'));
        addFreeTextQuestionButton.addEventListener('click', () => newQuestionButton('freeTextQuestionTemplate'));

        // Check if in edit mode
        if (<?= json_encode(isset($survey)) ?>) {
            const surveyData = <?= json_encode($survey) ?>;
            const questions = <?= json_encode($questions) ?>;
            populateSurveyFields(surveyData, questions);
        }

        document.addEventListener('submit', (event) => handleFormSubmission(event, apiUrl));

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-question-button')) {
                deleteQuestion(event);
            } else if (event.target.classList.contains('add-answer-button')) {
                addAnswer(event);
            } else if (event.target.classList.contains('delete-answer-button')) {
                deleteAnswer(event);
            }
        });
    });
</script>

<?= $this->endSection() ?>

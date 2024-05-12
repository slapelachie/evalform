<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3"><?= $survey['name'] ?></h1>
        <p><?= $survey['description'] ?></p>

        <form id="surveyForm" class="bg-light rounded p-3" data-survey-id="<?= $survey['id'] ?>">
            <?php foreach ($questions as $question): ?>
                <div class="mb-3 question-container" data-question-id="<?= $question[
                    'id'
                ] ?>" data-question-type="<?= $question['type'] ?>">
                    <?php if ($question['type'] == 'multiple_choice') { ?>
                        <p class="lead mb-2"><?= $question['question_number'] .
                            '. ' .
                            $question['question'] ?></p>
                        <?php foreach ($question['choices'] as $choice): ?>
                            <div class="form-check mb-3 answer-container" data-answer-id="<?= $choice[
                                'id'
                            ] ?>">
                                <label for="question-<?= $question['question_number'] .
                                    '-' .
                                    $choice['position'] ?>" class="form-check-label">
                                    <?= $choice['answer'] ?>
                                </label>
                                <input type="radio" class="form-check-input answer-choice" id="question-<?= $question[
                                    'question_number'
                                ] .
                                    '-' .
                                    $choice['position'] ?>" value="<?= $choice['id'] ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php } elseif ($question['type'] == 'free_text') { ?>
                        <label for="question-<?= $question[
                            'question_number'
                        ] ?>" class="lead form-label"><?= $question['question_number'] .
    '. ' .
    $question['question'] ?></label>
                        <textarea id="question-<?= $question[
                            'question_number'
                        ] ?>" rows="3" class="form-control"></textarea>
                    <?php } ?>
                </div>
            <?php endforeach; ?>
            <div class="mb-3 d-grid">
                <div id="alert"></div>
                <button id="submitSurveyButton" type="button" class="btn btn-primary">Submit Survey</button>
            </div>
        </form>
    </div>
</section>

<script src="<?= base_url('/js/utils.js') ?>"></script>
<script src="<?= base_url('/js/api.js') ?>"></script>
<script src="<?= base_url('/js/survey/survey.js') ?>"></script>

<script>
    const rootUrl = "<?= base_url('/') ?>";

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('submitSurveyButton').addEventListener('click', async function() {
            await submitSurvey(rootUrl);
        });
    });

</script>

<?= $this->endSection() ?>

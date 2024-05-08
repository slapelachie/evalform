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
                <button id="submitSurveyButton" type="button" class="btn btn-primary" onclick="submitSurvey()">Submit Survey</button>
            </div>
        </form>
    </div>
</section>

<script src="<?= base_url('/js/utils.js') ?>"></script>
<script src="<?= base_url('/js/api.js') ?>"></script>

<script>
    /**
     * Retrieves the selected multiple-choice answer ID from the given question container.
     *
     * @param {HTMLElement} questionContainer - The container element of the multiple-choice question.
     * @returns {string|null} The ID of the selected answer, or null if no answer is selected.
     */
    function getMultipleChoiceAnswer(questionContainer) {
        const answerContainers = questionContainer.querySelectorAll('.answer-container');

        for (const answerContainer of answerContainers) {
            const radioElement = answerContainer.querySelector('.answer-choice');
            if (radioElement.checked) {
                return answerContainer.dataset.answerId;
            }

        }
        return null;
    }

    /**
     * Retrieves the free-text answer from the given question container.
     *
     * @param {HTMLElement} questionContainer - The container element of the free-text question.
     * @returns {string} The free-text answer.
     */
    function getFreeTextAnswer(questionContainer) {
        const textArea = questionContainer.querySelector("textarea");
        return textArea.value;
    }

    /**
     * Retrieves responses to all questions from the survey form.
     *
     * @param {HTMLElement} surveyForm - The form element containing the survey questions.
     * @returns {Array<Object>} Array of objects representing question responses.
     */
    function getQuestionResponses(surveyForm) {
        const questionContainers = surveyForm.querySelectorAll(".question-container");

        let questions = [];
        questionContainers.forEach(questionContainer => {
            const questionId = questionContainer.dataset.questionId;
            const questionType = questionContainer.dataset.questionType;

            if (questionType == 'multiple_choice') {
                var answer = getMultipleChoiceAnswer(questionContainer);
            } else {
                var answer = getFreeTextAnswer(questionContainer);
            }

            questions.push({
                'question_id': questionId,
                'answer': answer
            })
        })

        return questions;
    }

    function getSurveyResponseData() {
        const surveyForm = document.getElementById("surveyForm");
        const surveyId = surveyForm.dataset.surveyId;

        return {
            'survey_id': surveyId,
            'responses': getQuestionResponses(surveyForm),
        }
    }

    /**
     * Retrieves survey response data including the survey ID and responses to questions.
     *
     * @returns {Object} Object containing survey ID and question responses.
     */
    async function submitSurvey() {
        const apiUrl = "<?= base_url('api') ?>"
        const submitSurveyButton = document.getElementById('submitSurveyButton');
        submitSurveyButton.disabled = true;

        const surveyResponseData = getSurveyResponseData();
        console.log(surveyResponseData);

        try {
            await makePostAPICall(`${apiUrl}/responses`, surveyResponseData)
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            submitSurveyButton.disabled = false;
            return;
        }

        window.location.href = `<?= base_url('surveys/thank-you') ?>`;
    }
</script>

<?= $this->endSection() ?>

<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3"><?= isset($survey) ? "Edit \"" . $survey['name'] . "\"" : "Create a Survey" ?></h1>
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
                <button type="submit" id="<?= isset($survey) ? 'edit' : 'create' ?>SurveyButton" type="button" class="btn btn-primary"><?= isset($survey) ? 'Save' : 'Create' ?> Survey</button>
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

<script>
    function getQuestionAnswers(questionContainer) {
        const answersContainer = questionContainer.querySelector('.answers-container');
        const answerContainers = answersContainer.querySelectorAll('.answer-container');

        let answers = [];
        answerContainers.forEach(answerContainer => {
            const answerId = answerContainer.dataset.answerId;

            let answerData = {
                'position': parseInt(answerContainer.dataset.answerNumber),
                'answer': answerContainer.querySelector('input').value.trim(),
            }

            if (answerId !== null) {
                answerData['id'] = answerId;
            }

            answers.push(answerData);
        });

        return answers;
    }

    function getQuestions() {
        const questionsContainer = document.getElementById('questionsContainer');
        const questionContainers = questionsContainer.querySelectorAll('.question-container');

        let questions = [];
        questionContainers.forEach(questionContainer => {
            const questionType = questionContainer.dataset.questionType;
            const questionId = questionContainer.dataset.questionId;

            let answers = questionType == 'multiple_choice' ? getQuestionAnswers(questionContainer) : [];

            let questionData = {
                'question_number': parseInt(questionContainer.dataset.questionNumber),
                'type': questionType,
                'question': questionContainer.querySelector('input').value.trim(),
                'answers': answers
            }

            if (questionId !== null) {
                questionData['id'] = questionId;
            }

            questions.push(questionData);
        });

        return questions;
    }

    function getSurveyData() {
        const surveyTitle = document.getElementById("surveyTitle").value.trim();
        const userId = document.getElementById("surveyForm").dataset.userId;

        data = {
            'name': surveyTitle,
            'description': 'Lorem',
            'owner_id': userId,
            'questions': getQuestions(),
        }

        return data;
    }

    async function submitSurvey(surveyId = null) {
        const apiUrl = "<?= base_url('api') ?>/surveys"

        const surveyData = getSurveyData();

        // Submit question and answer information
        try {
            if (surveyId !== null) {
                var response = await makePutAPICall(`${apiUrl}/${surveyId}`, surveyData);
            } else {
                var response = await makePostAPICall(apiUrl, surveyData);
            }
        } catch (error) {
            throw error;
        }

        return response.id;
    }

    async function createSurvey() {
        // Check validation
        const surveyForm = document.getElementById('surveyForm');

        // Disable Save Button
        const createSurveyButton = document.getElementById('createSurveyButton');
        createSurveyButton.disabled = true;

        // Try submitting the survey
        try {
            var surveyId = await submitSurvey();
        } catch (error) {
            createSurveyButton.disabled = false;
            throw error;
        }

        return surveyId;
    }

    /* Edit survey stuff */

    async function editSurvey() {
        // Check validation
        const surveyForm = document.getElementById('surveyForm');
        const surveyId = surveyForm.dataset.surveyId;

        // Disable Save Button
        const editSurveyButton = document.getElementById('editSurveyButton');
        editSurveyButton.disabled = true;

        // Try submitting the survey
        try {
            await submitSurvey(surveyId);
        } catch (error) {
            editSurveyButton.disabled = false;
            throw error;
        }

        return surveyId;
    }

    function populateSurveyTitle(surveyData) {
        const surveyTitleField = document.getElementById("surveyTitle");
        surveyTitleField.value = surveyData ? surveyData.name ?? '' : '';
    }

    function setCommonQuestionAttributes(questionContainer, question) {
        questionContainer.querySelector('div').dataset.questionNumber = question['question_number'];
        questionContainer.querySelector('div').dataset.questionId = question['id'];
        questionContainer.querySelector('.question-title').value = question['question'];
    }

    function populateMultipleChoiceQuestion(question) {
        const questionTemplate = document.getElementById("multipleChoiceQuestionTemplate");
        const questionContainer = questionTemplate.content.cloneNode(true);

        setCommonQuestionAttributes(questionContainer, question);

        const answersContainer = questionContainer.querySelector('.answers-container');

        for (const answer of question['answers']) {
            const answerTemplate = document.getElementById('answerTemplate');
            const answerContainer = answerTemplate.content.cloneNode(true);
            console.log(answer);

            answerContainer.querySelector('div').dataset.answerNumber = answer['position'];
            answerContainer.querySelector('div').dataset.answerId = answer['id'];
            answerContainer.querySelector('input').value = answer['answer'];

            answersContainer.appendChild(answerContainer);
        }

        return questionContainer;
    }

    function populateFreeTextQuestion(question) {
        const questionTemplate = document.getElementById("freeTextQuestionTemplate");
        const questionContainer = questionTemplate.content.cloneNode(true);

        setCommonQuestionAttributes(questionContainer, question);

        return questionContainer;
    }

    function populateSurveyQuestions(questions) {
        const questionsContainer = document.getElementById('questionsContainer');

        for (const question of questions) {
            if (question['type'] == "multiple_choice") {
                questionContainer = populateMultipleChoiceQuestion(question);
                questionsContainer.appendChild(questionContainer);
            } else if (question['type'] == "free_text") {
                questionContainer = populateFreeTextQuestion(question);
                questionsContainer.appendChild(questionContainer);
            }
        }
    }

    function populateSurveyFields() {
        const surveyForm = document.getElementById("surveyForm");

        const surveyData = <?= json_encode($survey) ?>;
        const questions = <?= json_encode($questions) ?>;

        surveyForm.dataset.surveyId = surveyData['id'];

        populateSurveyTitle(surveyData);
        populateSurveyQuestions(questions);
    }

    /* End edit survey stuff */

    function newQuestionButton(templateName) {
        const questionsContainer = document.getElementById('questionsContainer');

        const existingQuestions = questionsContainer.querySelectorAll('.question-container');
        const newQuestionNumber = existingQuestions.length + 1;

        const template = document.getElementById(templateName);
        const newQuestion = template.content.cloneNode(true);

        newQuestion.querySelector('div').dataset.questionNumber = newQuestionNumber;

        questionsContainer.appendChild(newQuestion);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const surveyForm = document.getElementById("surveyForm");
        const questionsContainer = document.getElementById('questionsContainer');
        const addMultipleChoiceQuestionButton = document.getElementById('addMultipleChoiceQuestionButton');
        const addFreeTextQuestionButton = document.getElementById('addFreeTextQuestionButton');

        const createSurveyButton = document.getElementById("createSurveyButton");
        const editSurveyButton = document.getElementById("editSurveyButton");

        addMultipleChoiceQuestionButton.addEventListener('click', () => newQuestionButton('multipleChoiceQuestionTemplate'));
        addFreeTextQuestionButton.addEventListener('click', () => newQuestionButton('freeTextQuestionTemplate'));

        // Check if in edit mode
        if (<?= json_encode(isset($survey)) ?>) {
            populateSurveyFields();
        }

        document.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formValidity = surveyForm.checkValidity();

            if (!formValidity) {
                event.stopPropagation();
            }

            surveyForm.classList.add('was-validated');

            if (!formValidity) {
                return;
            }

            try {
                if (createSurveyButton !== null) {
                    var surveyId = await createSurvey();
                } else if (editSurveyButton !== null) {
                    var surveyId = await editSurvey();
                }
            } catch (error) {
                appendAlert("Something went wrong! Please try again later.", 'danger');
                console.error(error);
                return;
            }

            window.location.href = `<?= base_url('surveys/') ?>${surveyId}/manage`;
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-question-button')) {
                closestQuestion = event.target.closest('.question-container');
                closestQuestion.remove();

                // Update question numbers
                const existingQuestions = questionsContainer.querySelectorAll('.question-container');
                newQuestionNumber = 0;
                existingQuestions.forEach(question => {
                    question.dataset.questionNumber = ++newQuestionNumber;
                });
            } else if (event.target.classList.contains('add-answer-button')) {
                closestQuestion = event.target.closest('.question-container');
                answersContainer = closestQuestion.querySelector('.answers-container')
                questionNumber = closestQuestion.dataset.questionNumber;

                const existingAnswers = answersContainer.querySelectorAll('.answer-container');
                const newAnswerNumber = existingAnswers.length + 1;

                const template = document.getElementById('answerTemplate');
                const newAnswer = template.content.cloneNode(true);

                newAnswer.querySelector('div').dataset.answerNumber = newAnswerNumber;

                newAnswer.querySelector('input').placeholder = `Answer ${newAnswerNumber}`;

                answersContainer.appendChild(newAnswer);
            } else if (event.target.classList.contains('delete-answer-button')) {
                closestQuestion = event.target.closest('.question-container');
                questionNumber = closestQuestion.dataset.questionNumber;

                closestAnswer = event.target.closest('.answer-container');
                closestAnswer.remove();

                // Update answer numbers
                const existingAnswers = questionsContainer.querySelectorAll('.answer-container');
                newAnswerNumber = 0;
                existingAnswers.forEach(answer => {
                    answer.dataset.answerNumber = ++newAnswerNumber;
                    answer.querySelector('input').placeholder = `Answer ${newAnswerNumber}`;
                });
            }
        });
    });
</script>

<?= $this->endSection() ?>
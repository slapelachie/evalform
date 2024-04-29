<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3">Create a Survey</h1>
        <form id="surveyForm" data-user-id="<?= $user_id ?>">
            <div class="mb-3">
                <label for="surveyTitle">
                    Title of Survey <span class="text-danger">*</span>
                </label>
                <input id="surveyTitle" name="survey-title" type="text" class="form-control" required>
            </div>
            <div id="questionsContainer">

            </div>
            <div class="mb-3 d-grid">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal" id="addQuestionButton">Add Question</button>
            </div>
            <div class="mb-3 d-grid">
                <div id="errorSaveAlert"></div>
                <button id="saveSurveyButton" type="button" class="btn btn-primary" onclick="saveSurvey()">Save Survey</button>
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
            </div>
        </div>
    </div>
</template>

<script>
    const alertPlaceHolder = document.getElementById('errorSaveAlert')

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

    async function submitAPICall(apiUrl, data) {
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
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

    function getQuestionAnswers(questionContainer) {
        const answersContainer = questionContainer.querySelector('.answers-container');
        const answerContainers = answersContainer.querySelectorAll('.answer-container');

        let answers = [];
        answerContainers.forEach(answerContainer => {
            answers.push({
                'position': parseInt(answerContainer.dataset.answerNumber),
                'answer': answerContainer.querySelector('input').value.trim(),
            });
        });

        return answers;
    }

    function getQuestions() {
        const questionsContainer = document.getElementById('questionsContainer');
        const questionContainers = questionsContainer.querySelectorAll('.question-container');

        let questions = [];
        questionContainers.forEach(questionContainer => {
            const questionType = questionContainer.dataset.questionType;

            let answers = questionType == 'multiple_choice' ? getQuestionAnswers(questionContainer) : [];

            questions.push({
                'question_number': parseInt(questionContainer.dataset.questionNumber),
                'type': questionType,
                'question': questionContainer.querySelector('input').value.trim(),
                'answers': answers
            });
        });

        return questions;
    }

    function getSurveyData() {
        const surveyTitle = document.getElementById("surveyTitle").value.trim();
        const userId = document.getElementById("surveyForm").dataset.userId;

        return {
            'name': surveyTitle,
            'description': 'Lorem',
            'owner_id': userId,
            'questions': getQuestions(),
        }
    }

    async function submitSurvey() {
        const apiUrl = "<?= base_url('api') ?>"

        const surveyData = getSurveyData();

        // Submit question and answer information
        try {
            var response = await submitAPICall(`${apiUrl}/surveys`, surveyData)
        } catch (error) {
            throw error;
        }

        return response.id;
    }

    async function saveSurvey() {
        // Check validation
        const surveyForm = document.getElementById('surveyForm');
        if (!surveyForm.checkValidity()) {
            appendAlert('Please fill out all required fields.', 'danger');
            return;
        }

        // Disable Save Button
        const saveSurveyButton = document.getElementById('saveSurveyButton');
        saveSurveyButton.disabled = true;

        // Try submitting the survey
        try {
            var surveyId = await submitSurvey();
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            saveSurveyButton.disabled = false;
            return;
        }

        // Redirect to successful creation page
        // TODO: should be from api not hardcoded!
        window.location.href = `<?= base_url('surveys/') ?>${surveyId}/manage`;
    }

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
        const questionsContainer = document.getElementById('questionsContainer');
        const addMultipleChoiceQuestionButton = document.getElementById('addMultipleChoiceQuestionButton');
        const addFreeTextQuestionButton = document.getElementById('addFreeTextQuestionButton');

        addMultipleChoiceQuestionButton.addEventListener('click', () => newQuestionButton('multipleChoiceQuestionTemplate'));
        addFreeTextQuestionButton.addEventListener('click', () => newQuestionButton('freeTextQuestionTemplate'));

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
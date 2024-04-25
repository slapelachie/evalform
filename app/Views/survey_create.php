<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3">Create a Survey</h1>
        <form>
            <div class="mb-3">
                <label for="surveyTitle">
                    <h5>Title of Survey</h5>
                </label>
                <input id="surveyTitle" name="survey-title" type="text" class="form-control">
            </div>
            <div id="questionsContainer">

            </div>
            <div class="mb-3 d-grid">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal" id="addQuestionButton">Add Question</button>
            </div>
            <div class="mb-3 d-grid">
                <div id="errorSaveAlert"></div>
                <button type="button" class="btn btn-primary" onclick="submitSurvey()">Save Survey</button>
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
                        <h6>Multiple Choice Question Title</h6>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control">
            </div>
            <div>
                <label for="answers">
                    <h6>Answers</h6>
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
        <input type="text" class="form-control" placeholder="">
        <button type="button" class="delete-answer-button btn btn-outline-danger"><i class="bi bi-trash"></i></button>
    </div>
</template>

<template id="freeTextQuestionTemplate">
    <div class="question-container card mb-3" data-question-type="free_text">
        <div class="card-body">
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="questionTitle">
                        <h6>Free Text Question Title</h6>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control">
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
                const errorData = await response.json();
                appendAlert(`Error: ${errorData.messages['name']}`, 'danger');
                return null;
            }

            return await response.json();
        } catch (error) {
            console.error('Error: ', error)
            return null;
        }

    }

    async function submitSurveyData(apiUrl) {
        const surveyTitle = document.getElementById("surveyTitle").value;
        const surveyData = {
            'name': surveyTitle,
            'description': 'Lorem',
            'owner_id': <?= $user_id ?>,
        }

        try {
            const result = await submitAPICall(`${apiUrl}/surveys`, surveyData);
            if (result && result.id) {
                console.log("Survey submitted with ID:", result.id);
                return result.id;
            }

            console.error("Failed to submit survey")
            return null;
        } catch (error) {
            console.error('Error: ', error)
            return null;
        }
    }

    function getQuestionAnswers(questionContainer) {
        const answersContainer = questionContainer.querySelector('.answers-container');
        const answerContainers = answersContainer.querySelectorAll('.answer-container');

        let answers = [];
        answerContainers.forEach(answerContainer => {
            answers.push({
                'position': parseInt(answerContainer.dataset.answerNumber),
                'answer': answerContainer.querySelector('input').value,
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
                'question': questionContainer.querySelector('input').value,
                'answers': answers
            });
        });

        return questions;
    }

    async function submitQuestionData(questionData, questionApiUrl) {
        try {
            const result = await submitAPICall(questionApiUrl, questionData);
            if (result && result.id) {
                console.log("Question submitted with ID:", result.id);
                return result.id
            }

            console.error("Failed to submit question")
            return null;
        } catch (error) {
            console.error('Error: ', error)
            return null;
        }
    }

    async function submitQuestionsWithAnswers(surveyId, apiUrl) {
        let questions = getQuestions();

        for (const question of questions) {
            const questionData = {
                'survey_id': surveyId,
                'type': question['type'],
                'question_number': question['question_number'],
                'question': question['question'],
            }

            try {
                const questionId = await submitQuestionData(questionData, `${apiUrl}/questions`);
                if (!questionId) {
                    console.error("No question ID given!")
                    return null;
                }

                for (const answer of question['answers']) {
                    const answerData = {
                        'question_id': questionId,
                        'position': answer['position'],
                        'answer': answer['answer']
                    }

                    try {
                        const result = await submitAPICall(`${apiUrl}/answers`, answerData);
                        if (result && result.id) {
                            console.log("Answer submitted with ID:", result.id);
                        } else {
                            console.error("Failed to submit answer")
                            return null;
                        }
                    } catch (error) {
                        console.error('Error: ', error)
                        return null;
                    }
                }

            } catch (error) {
                console.error('Error submitting question:', error);
            }
        }
    }

    async function submitSurvey() {
        const apiUrl = "<?= base_url('api') ?>"

        // Submit survey information
        surveyId = await submitSurveyData(apiUrl);
        if (surveyId == null) {
            // TODO
        }

        // Submit question and answer information
        await submitQuestionsWithAnswers(surveyId, apiUrl);
    }

    function newQuestionButton(templateName) {
        const questionsContainer = document.getElementById('questionsContainer');

        const existingQuestions = questionsContainer.querySelectorAll('.question-container');
        const newQuestionNumber = existingQuestions.length + 1;

        const template = document.getElementById(templateName);
        const newQuestion = template.content.cloneNode(true);

        // newQuestion.querySelector('.question-title').name = `question-${newQuestionNumber}-title`
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
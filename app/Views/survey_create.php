<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3">Create a Survey</h1>
        <form method="post">
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
                <input type="submit" class="btn btn-primary" value="Save Survey">
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
    <div class="question-container card mb-3">
        <div class="card-body">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="questionTitle">
                        <h6>Multiple Choice Question Title</h6>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control">
                <input type="hidden" value="multiple-choice" class="question-type">
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
    <div class="question-container card mb-3">
        <div class="card-body">
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="questionTitle">
                        <h6>Free Text Question Title</h6>
                    </label>
                    <button type="button" class="delete-question-button btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="question-title form-control">
                <input type="hidden" value="free-text" class="question-type">
            </div>
        </div>
    </div>
</template>

<script>
    function newQuestionButton(templateName) {
        const questionsContainer = document.getElementById('questionsContainer');

        const existingQuestions = questionsContainer.querySelectorAll('.question-container');
        const newQuestionNumber = existingQuestions.length + 1;

        const template = document.getElementById(templateName);
        const newQuestion = template.content.cloneNode(true);

        newQuestion.querySelector('.question-title').name = `question-${newQuestionNumber}-title`
        newQuestion.querySelector('div').dataset.questionNumber = newQuestionNumber;
        newQuestion.querySelector('.question-type').name = `question-${newQuestionNumber}-type`;

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
                    question.querySelector('.question-number').value = ++newQuestionNumber;
                });
            } else if (event.target.classList.contains('add-answer-button')) {
                closestQuestion = event.target.closest('.question-container');
                answersContainer = closestQuestion.querySelector('.answers-container')
                questionNumber = closestQuestion.dataset.questionNumber;

                const existingAnswers = answersContainer.querySelectorAll('.answer-container');
                const newAnswerNumber = existingAnswers.length + 1;

                const template = document.getElementById('answerTemplate');
                const newAnswer = template.content.cloneNode(true);

                newAnswer.querySelector('input').name = `answer-${questionNumber}-${newAnswerNumber}`;
                newAnswer.querySelector('input').placeholder = `Answer ${newAnswerNumber}`;

                answersContainer.appendChild(newAnswer);
            } else if (event.target.classList.contains('delete-answer-button')) {
                closestQuestion = event.target.closest('.question-container');
                questionNumber = closestQuestion.querySelector('.question-number').value;

                closestAnswer = event.target.closest('.answer-container');
                closestAnswer.remove();

                // Update answer numbers
                const existingAnswers = questionsContainer.querySelectorAll('.answer-container');
                newAnswerNumber = 0;
                existingAnswers.forEach(answer => {
                    answer.querySelector('input').id = `answer-${questionNumber}-${++newAnswerNumber}`;
                    console.log(answer.querySelector('input').id);
                });

            }
        });
    });
</script>

<?= $this->endSection() ?>
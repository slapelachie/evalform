<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="display-5 mb-2">Manage <?= $survey['name'] ?></h1>
                <div class="align-items-center ">
                    <a href="/surveys/<?= $survey["id"] ?>"><button type="button" class="btn btn-primary btn-sm">View</button></a>
                    <a href="/surveys/<?= $survey["id"] ?>/edit"><button type="button" class="btn btn-warning btn-sm">Edit</button></a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal">Delete</button>
                </div>
            </div>
            <p class="text-muted"><?= $survey['description'] ?></p>
            <div class="d-flex align-items-center mb-2">
                <h2 class="me-3 display-6">Analytics</h2>
            </div>
        </div>
        <div id="surveyFilters" class="row align-items-end my-3">
            <div class="col-md-3">
                <label for="startDateFilter" class="form-label">From:</label>
                <input type="date" class="form-control" id="startDateFilter">
            </div>
            <div class="col-md-3">
                <label for="endDateFilter" class="form-label">To:</label>
                <input type="date" class="form-control" id="endDateFilter">
            </div>
            <div class="col-md">
                <label for="questionTypeFilter" class="form-label">Question Type:</label>
                <select id="questionTypeFilter" class="form-select">
                    <option value="" selected>Any</option>
                    <option value="multipleChoice">Multiple Choice</option>
                    <option value="freeText">Free Text</option>
                </select>
            </div>
            <div class="col-md-auto">
                <div class="col-md">
                    <button type="button" class="btn btn-primary" onclick="">Apply Filters</button>
                    <button type="button" class="btn btn-outline-danger mx-2" onclick="resetFilters()">Reset Filters</button>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshCounts()">Refresh</button>
                </div>
            </div>
        </div>
        <div class="my-3">
            <div id="accordionQuestionsContainer" class="accordion mb-3">
            </div>
            <?php if ($survey['status'] == 'draft') : ?>
                <div class="mb-3 d-grid">
                    <div id="alert"></div>
                    <button id="publishSurveyButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#publishSurveyModal">Publish Survey</button>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSurveyLabel">Delete Survey "<?= $survey['name']; ?>"?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <p>This cannot be undone!</p>
                <button id="confirmSurveyDeleteButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="deleteSurvey()">Yes, delete this survey.</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="publishSurveyModal" tabindex="-1" aria-labelledby="publishSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishSurveyLabel">Publish Survey "<?= $survey['name']; ?>"?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <p>This cannot be undone!</p>
                <button id="confirmSurveyPublishButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="publishSurvey()">Yes, publish this survey.</button>
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
            <button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" aria-expanded="false">
                <!-- Question 123: Foo bar -->
            </button>
        </h2>
        <div class="accordion-collapse collapse" data-bs-parent="#accordionQuestionsContainer">
            <div class="accordion-body">
            </div>
        </div>
    </div>
</template>

<template id="multipleChoiceAccordion">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Answer</th>
                <th>Responses</th>
                <th>Response Rate</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</template>

<template id="multipleChoiceAnswerRow">
    <tr class="answer-row">
        <td class="mc-answer"></td>
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

<script>
    function resetFilters() {
        // Reset date inputs
        document.getElementById('startDateFilter').value = '';
        document.getElementById('endDateFilter').value = '';

        // Reset type filter to first option
        document.getElementById('questionTypeFilter').selectedIndex = 0;
    }

    async function getQuestions() {
        try {
            const response = await fetch('<?= base_url('/api/questions?survey_id=') . $survey['id'] ?>', {
                method: 'GET',
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

    async function publishSurvey() {
        try {
            surveyData = {
                "status": "published",
            }

            const response = await fetch('<?= base_url('/api/surveys/') . $survey['id'] ?>', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(surveyData),
            });

            if (!response.ok) {
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            try {
                await response.json();
            } catch (error) {
                throw error;
            }
        } catch (error) {
            appendAlert("Failed to publish this survey! Please try again later.", "danger");
            console.error(error);
            return;
        }
    }

    async function deleteSurvey() {
        try {
            const response = await fetch('<?= base_url('/api/surveys/') . $survey['id'] ?>', {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            try {
                await response.json();
            } catch (error) {
                throw error;
            }
        } catch (error) {
            appendAlert("Failed to delete this survey! Please try again later.", "danger");
            console.error(error);
            return;
        }

        // Redirect to dashboard
        window.location.href = '<?= base_url('/') ?>';
        return;
    }

    async function getAnswers(questionId) {
        try {
            const response = await fetch(`<?= base_url('/api/answers?question_id=') ?>${questionId}`, {
                method: 'GET',
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

    async function getQuestionResponseCount(questionId, answerId = null) {
        let apiUrl = `<?= base_url('/api/question-responses') ?>?question_id=${questionId}&count`;

        if (answerId !== null) {
            apiUrl += `&answer_id=${answerId}`;
        }

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
            });

            if (!response.ok) {
                const errorResponse = await response.json();
                console.error(`API request failed with status ${response.status}: ${response.statusText}\n`, errorResponse);
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return data.count;
        } catch (error) {
            throw error;
        }
    }

    async function refreshCounts() {
        const questionItems = document.querySelectorAll('.question-item');
        for (let questionItem of questionItems) {
            const questionId = questionItem.dataset.questionId;
            const questionResponseCount = await getQuestionResponseCount(questionId);

            const answerRows = questionItem.querySelectorAll('.answer-row');
            for (let answerRow of answerRows) {
                const answerId = answerRow.dataset.answerId;
                const answerResponseCount = await getQuestionResponseCount(questionId, answerId);

                answerRow.querySelector('.mc-response-count').innerHTML = answerResponseCount;

                if (questionResponseCount != 0) {
                    answerRow.querySelector('.mc-response-percent').innerHTML = `${Math.round(answerResponseCount / questionResponseCount * 100)}%`;
                } else {
                    answerRow.querySelector('.mc-response-percent').innerHTML = "N/A";
                }
            }
        }

    }

    function setQuestionAttributes(questionAccordion, question) {
        questionItem = questionAccordion.querySelector('.question-item');
        questionItem.dataset.questionId = question["id"]

        accordionHeader = questionAccordion.querySelector('.accordion-header');
        accordionHeader.id = `questionHeader${question['id']}`;

        accordionHeaderButton = accordionHeader.querySelector('button');
        accordionHeaderButton.dataset.bsTarget = `#questionBody${question['id']}`;
        accordionHeaderButton.setAttribute('aria-controls', `questionBody${question['id']}`);
        accordionHeaderButton.innerHTML = `Question ${question['question_number']}: ${question['question']}`;

        accordionCollapse = questionAccordion.querySelector('.accordion-collapse');
        accordionCollapse.id = `questionBody${question['id']}`;
        accordionCollapse.setAttribute('aria-labelledby', `questionHeader${question['id']}`);

    }

    async function setupMultipleChoiceQuestion(accordionBody, questionId) {
        const multipleChoiceQuestionTemplate = document.getElementById("multipleChoiceAccordion");
        const multipleChoiceAccordion = multipleChoiceQuestionTemplate.content.cloneNode(true);

        accordionBody.append(multipleChoiceAccordion);
        const tableBody = accordionBody.querySelector('tbody');

        const answers = await getAnswers(questionId);
        for (let answer of answers) {
            const tableRowTemplate = document.getElementById("multipleChoiceAnswerRow");
            const tableRow = tableRowTemplate.content.cloneNode(true);

            const answerRow = tableRow.querySelector('.answer-row');
            answerRow.dataset.answerId = answer["id"];
            answerRow.querySelector('.mc-answer').innerHTML = `${answer['answer']}`;

            tableBody.append(tableRow)
        }
    }

    async function setupQuestions(question) {
        const questionAccodionTemplate = document.getElementById("questionAccordion");
        const newQuestionAccordion = questionAccodionTemplate.content.cloneNode(true);

        setQuestionAttributes(newQuestionAccordion, question);

        accordionBody = newQuestionAccordion.querySelector('.accordion-body');

        if (question["type"] == "multiple_choice") {
            await setupMultipleChoiceQuestion(accordionBody, question["id"]);
        } else if (question["type"] == "free_text") {
            // TODO
        }

        accordionQuestionsContainer.append(newQuestionAccordion);
    }

    async function setupAccordion() {
        const accordionQuestionsContainer = document.getElementById("accordionQuestionsContainer");

        try {
            var questions = await getQuestions();
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            return;
        }

        for (let question of questions) {
            await setupQuestions(question);
        }

        refreshCounts();
    }

    document.addEventListener('DOMContentLoaded', async function() {
        await setupAccordion();

    })
</script>

<?= $this->endSection() ?>
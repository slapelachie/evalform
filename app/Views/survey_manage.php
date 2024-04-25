<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="display-5 mb-2">Manage <?= $survey['name'] ?></h1>
                <div class="align-items-center ">
                    <a href="/surveys/<?= $survey["id"] ?>"><button type="button" class="btn btn-primary btn-sm">View <i class="bi bi-eye"></i></button></a>
                    <a href="/surveys/<?= $survey["id"] ?>/edit"><button type="button" class="btn btn-warning btn-sm">Edit <i class="bi bi-pencil"></i></button></a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal">Delete <i class="bi bi-trash"></i></button>
                </div>
            </div>
            <p class="text-muted"><?= $survey['description'] ?></p>
            <h2 class="mb-2 display-6">Analytics</h2>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md mb-1">
                    <select id="dateRange" class="form-select">
                        <option selected>Date Range</option>
                        <option value="previousWeek">Previous Week</option>
                        <option value="previousMonth">Previous Month</option>
                        <option value="previousYear">Previous Year</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-md mb-1">
                    <select id="questionType" class="form-select">
                        <option selected>Type</option>
                        <option value="multipleChoice">Multiple Choice</option>
                        <option value="freeText">Free Text</option>
                    </select>
                </div>
                <div class="col-md mb-1">
                    <select id="questionSentiment" class="form-select">
                        <option selected>Sentiment</option>
                        <option value="negative">Negative</option>
                        <option value="neutral">Neutral</option>
                        <option value="Positive">Positive</option>
                    </select>
                </div>
                <div class="col-md-auto ms-auto">
                    <button class="btn btn-primary w-100 w-md-auto">Apply Filters</button>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div id="accordionQuestions" class="accordion mb-3">
                <?php foreach ($questions as $question) : ?>
                    <div class="accordion-item">
                        <h2 id="questionHeader<?= $question['question_number'] ?>" class="accordion-header">
                            <button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#questionBody<?= $question['question_number'] ?>" aria-expanded="false" aria-controls="questionBody<?= $question['question_number'] ?>">
                                Question <?= $question['question_number'] ?>: <?= $question['question'] ?>
                            </button>
                        </h2>
                        <div id="questionBody<?= $question['question_number'] ?>" class="accordion-collapse collapse" aria-labelledby="questionHeader<?= $question['question_number'] ?>" data-bs-parent="#accordionQuestions">
                            <div class="accordion-body">
                                <?php if ($question['type'] == 'multiple_choice') { ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Answer</th>
                                                <th>Responses</th>
                                                <th>Response Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($question['choices'] as $choice) : ?>
                                                <tr>
                                                    <td><?= $choice['answer'] ?></td>
                                                    <td><?= $responses[$choice['id']]['count'] ?></td>
                                                    <td class=""><i class="bi bi-dash"></i> <?= $responses[$choice['id']]['percent'] ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php } else { ?>
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
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($survey['status'] == 'draft') : ?>
                <div class="mb-3 d-grid">
                    <button id="publish_survey" type="button" class="btn btn-primary">Publish Survey</button>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>

<div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSurveyLabel">Delete Survey "<?= $survey['name']; ?>"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <button id="confirmSurveyDeleteButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="deleteSurvey()">Yes, delete this survey.</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
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
            // TODO show flash message
            console.log(error);
            return;
        }

        // Redirect to dashboard
        window.location.href = '<?= base_url('/') ?>';
        return;
    }
</script>

<?= $this->endSection() ?>
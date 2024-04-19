<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3"><?= $survey['name'] ?></h1>
        <p><?= $survey['description'] ?></p>

        <form id="questionForm" class="bg-light rounded p-3" method="post">
            <?php foreach ($questions as $question) : ?>
                <div class="mb-3">
                    <?php if ($question['type'] == 'multiple_choice') { ?>
                        <p class="lead mb-2"><?= $question['question_number'] . '. ' . $question['question'] ?></p>
                        <?php foreach ($question['choices'] as $choice) : ?>
                            <div class="form-check mb-3">
                                <label for="question-<?= $question['question_number'] . '-' . $choice['position'] ?>" class="form-check-label">
                                    <?= $choice['answer'] ?>
                                </label>
                                <input type="radio" name="<?= $question['id'] ?>" class="form-check-input" id="question-<?= $question['question_number'] . '-' . $choice['position'] ?>" value="<?= $choice['id'] ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php } elseif ($question['type'] == 'free_text') { ?>
                        <label for="question-<?= $question['question_number'] ?>" class="lead form-label"><?= $question['question_number'] . '. ' . $question['question'] ?></label>
                        <textarea name="<?= $question['id'] ?>" id="question-<?= $question['question_number'] ?>" rows="3" class="form-control"></textarea>
                    <?php } ?>
                </div>
            <?php endforeach; ?>
            <button type="button" class="btn btn-primary" onclick="submitAnswers()">Submit</button>
        </form>
    </div>
</section>

<script>
    function submitAnswers() {
        const form = document.getElementById('questionForm');
        const formData = new FormData(form);
        let answers = [];

        for (let [key, value] of formData.entries()) {
            answers.push({
                question_id: key,
                value: value
            });
        }

        // Convert the answers array to JSON format
        const jsonPayload = JSON.stringify(answers);

        // Send data
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: jsonPayload
        })
        .then(response => {
            console.log(response.json());
        })
        .then(data => console.log(data))
        .then(error => console.error(error));
    }
</script>

<?= $this->endSection() ?>
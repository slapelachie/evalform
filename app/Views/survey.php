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
            <input type="submit" class="btn btn-primary">Submit</input>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
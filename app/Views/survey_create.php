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
                <input id="surveyTitle" type="text" class="form-control">
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="questionTitle">
                                <h6>Multiple Choice Question Title</h6>
                            </label>
                            <button type="button" id="deleteQuestion" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </div>
                        <input id="questionTitle" type="text" class="form-control">
                    </div>
                    <div>
                        <label for="answers">
                            <h6>Answers</h6>
                        </label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Answer 1">
                            <button type="button" id="answer1" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Answer 2">
                            <button type="button" id="answer1" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Answer 3">
                            <button type="button" id="answer1" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Answer 4">
                            <button type="button" id="answer1" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="d-grid">
                            <button id="addAnswer" type="button" class="btn btn-outline-primary btn-sm">Add
                                Input</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="questionTitle">
                                <h6>Free Text Question Title</h6>
                            </label>
                            <button type="button" id="deleteQuestion" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </div>
                        <input id="questionTitle" type="text" class="form-control">
                    </div>
                </div>
            </div>
            <div class="mb-3 d-grid">
                <button id="addQuestion" type="button" class="btn btn-outline-primary btn-sm">Add
                    Question</button>
            </div>

            <div class="mb-3 d-grid">
                <button id="saveSurvey" type="button" class="btn btn-primary">Save Survey</button>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
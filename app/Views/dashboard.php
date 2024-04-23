<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <h1 class="display-5 mb-3">Manage <?= (esc($business_name) ?? 'Your') . (!empty($business_name) ? '\'s' : '') ?> Surveys</h1>
        <div class="container d-flex flex-row mb-4">
            <div class="row flex-nowrap overflow-auto">
                <div class="col pe-1 my-2">
                    <a href="<?= base_url('/surveys/create') ?>" style="color: inherit; text-decoration: none;">
                        <div class="card" style="width: 10rem; height: 15rem">
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <h5 class="card-title text-center">Create a New Survey</p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php $focused_surveys = array_slice($surveys, 0, 5);
                foreach ($focused_surveys as $survey) : ?>
                    <div class="col pe-1 my-2">
                        <a href="<?= base_url('survey/manage/' . $survey['id']); ?>" style="color: inherit; text-decoration: none;">
                            <div class="card" style="width: 10rem">
                                <img src="https://via.placeholder.com/50" alt="" class="card-img-top">
                                <div class="card-body">
                                    <p class="card-title" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                        <?= esc($survey['name']) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (count($surveys) > 5) { ?>
                    <div class="col pe-1 my-2">
                        <a href="#" style="color: inherit; text-decoration: none;">
                            <div class="card" style="width: 10rem; height: 100%">
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <h5 class="card-title text-center">View More</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
        <h1 class="display-5 mb-3">Insights</h1>
        <div class="container mb-5">
            <div class="row row-cols-2">
                <?php foreach ($insights as $insight) : ?>
                    <a href="#" style="color: inherit; text-decoration: none;">
                        <div class="col pb-4">
                            <div class="bg-light d-flex flex-column align-items-center justify-content-center rounded border" style="height: 10rem">
                                <h4><?= $insight['name'] ?></h4>
                                <h5 class="lead"><?= $insight['value'] ?></h5>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
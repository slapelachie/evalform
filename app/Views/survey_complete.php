<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="mb-5 text-center">
            <h1 class="display-5 mb-3"><?= $survey["name"] ?></h1>
        </div>

        <div class="container d-flex flex-column align-items-center text-center mt-5">
            <div class="container my-5">
                <h2 class="display-6">Thank you!</h2>
                <p class="text-body-secondary">Custom thank you message. Lorem ipsum dolor sit amet consectetur
                    adipisicing elit. At, unde!</p>
                <div class="container">
                    <!-- TODO -->
                    <button class="btn btn-primary" type="button">Share this survey</button>
                </div>
            </div>
            <p>Click <a href="<?= base_url("/survey/create") ?>">here</a> to create your own survey.</p>
        </div>

    </div>
</section>

<?= $this->endSection() ?>
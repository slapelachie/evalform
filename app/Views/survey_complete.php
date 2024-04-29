<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <!--
        <div class="mb-5 text-center">
        </div>
        -->

        <div class="container d-flex flex-column align-items-center text-center mt-5">
            <div class="container my-5">
                <div class="container mb-3">
                    <h2 class="display-6">Thank you!</h2>
                    <p>We appreciate you taking the time to complete our survey.</p>
                </div>
            </div>
            <p>Click <a href="<?= base_url("/survey/create") ?>">here</a> to create your own survey.</p>
        </div>

    </div>
</section>

<?= $this->endSection() ?>
<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="">
    <div class="container">
        <div class="row">
            <!-- To be used if more items are added for admin use
            <div class="col-md-3">
                <ul class="nav nav-pills justify-content-center flex-lg-column bg-body-tertiary">
                    <li class="nav-item">
                        <a
                            class="nav-link <?= service('router')->getMatchedRoute()[0] == 'admin'
                                ? 'active'
                                : '' ?>"
                            aria-current="page"
                            href="<?= base_url('/admin') ?>"
                        >
                            General
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link <?= service('router')->getMatchedRoute()[0] ==
                            'admin/users'
                                ? 'active'
                                : '' ?>"
                            href="<?= base_url('/admin/users') ?>"
                            >
                                Users
                            </a>
                    </li>
                </ul>

            </div>
            -->
            <div class="col-md-9 py-3 container">
                <?= $this->renderSection('content') ?>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>

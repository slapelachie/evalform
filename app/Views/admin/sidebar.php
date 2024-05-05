<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="py-3">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <ul class="nav nav-pills justify-content-center flex-lg-column bg-body-tertiary">
                    <li class="nav-item">
                        <a class="nav-link <?= service('router')->getMatchedRoute()[0] == 'admin' ? 'active' : ''; ?>" aria-current="page" href="<?= base_url('/admin') ?>">General</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= service('router')->getMatchedRoute()[0] == 'admin/users' ? 'active' : ''; ?>" href="<?= base_url('/admin/users') ?>">Users</a>
                    </li>
                </ul>

            </div>
            <div class="col-md-9 py-3 container bg-body-tertiary rounded-2">
                <?= $this->renderSection('content') ?>
            </div>

        </div>

    </div>
</section>

<?= $this->endSection() ?>
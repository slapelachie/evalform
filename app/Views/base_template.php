<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EvalForm - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container">
                <a href="<?= base_url() ?>" class="navbar-brand">EvalForm</a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (auth()->loggedIn()): ?>
                            <li class="nav-item">
                                <?php
                                $isActive = service('router')->getMatchedRoute()[0] == '/';
                                $activeClass = $isActive ? 'active' : '';
                                ?>
                                <a href="<?= base_url() ?>" class="nav-link <?= $activeClass ?>">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item vr d-none d-lg-block"></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center py-0 py-lg-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="d-flex align-items-center bi bi-person-circle d-none d-lg-block"></i>
                                    <span class="ms-lg-2 d-lg-none">Account</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <span class="dropdown-item">
                                            Signed in as <b><?= auth()->user()->username ?></b>
                                        </span>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('/profile') ?>">
                                            Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url(
                                            '/user/settings'
                                        ) ?>">
                                            Settings
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <?php if (auth()->user()->can('admin.access')): ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url(
                                                '/admin'
                                            ) ?>">
                                                Site Admin
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('/logout') ?>">
                                            Sign Out
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="<?= base_url('/login') ?>" class="nav-link">
                                    Log In
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="container">
                <div class="my-3 alert alert-danger alert-dismissible" role="alert">
                    <div><?= session()->getFlashdata('error') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

            </div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 EvalForm. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
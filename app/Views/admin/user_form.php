<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="my-3">
    <div class="bg-light container py-3 rounded-3">
        <h1 class="display-5 mb-3"><?= $formTitle ?></h1>
        <form class="container needs-validation" id="userForm" data-user-id="<?= isset($user) ? $user->id : '' ?>" novalidate>

            <div class="mb-3 row">
                <div class="col-12 col-md-6 me-3 me-md-0">
                    <label class="form-label" for="usernameInput">
                        Username: <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="usernameInput" class="form-control" value="<?= isset($user) ? $user->username : '' ?>" placeholder="jdoe" required>
                    <div class="invalid-feedback">
                        Please choose a username.
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="emailInput">
                        Email: <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="emailInput" class="form-control" value="<?= isset($user) ? $user->email : '' ?>" placeholder="jdoe@example.com" required>
                    <div class="invalid-feedback">
                        Please provide an email.
                    </div>
                </div>
            </div>
            <?php if (!isset($user)) : ?>
                <div class="mb-3 row">
                    <div class="col-12 col-md-6 me-3 me-md-0">
                        <label class="form-label" for="passwordInput">
                            Password: <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="passwordInput" class="form-control" required>
                        <div class="invalid-feedback password-mismatch-feedback">
                            Please provide a password.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="confirmPasswordInput">
                            Confirm Password: <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="confirmPasswordInput" class="form-control" required>
                        <div class="invalid-feedback password-mismatch-feedback">
                            Please provide a password.
                        </div>
                    </div>

                </div>
            <?php else : ?>
                <!-- TODO: Implement password change feature? -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enabledCheck" <?= $user->active ? "checked" : "" ?>>
                    <label class="form-check-label" for="enabledCheck">
                        Enabled
                    </label>
                </div>
            <?php endif ?>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="administratorCheck" <?= isset($user) ? ($user->admin ? "checked" : "") : "" ?>>
                <label class="form-check-label" for="administratorCheck">
                    Adminstrator
                </label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="resetPasswordCheck">
                <label class="form-check-label" for="resetPasswordCheck">
                    Reset Password
                </label>
            </div>
            <div class="mb-2 d-grid">
                <div id="alert"></div>
                <button id="<?= isset($user) ? "edit" : "create" ?>UserButton" type="submit" class="btn btn-primary"><?= isset($user) ? "Modify" : "Create" ?> User</button>
            </div>
        </form>
    </div>
</section>

<?= view('snippets/common_scripts') ?>
<?= view('snippets/api_scripts') ?>

<script>
    async function handleUserCreate() {
        const apiUrl = `<?= base_url('api/users') ?>`;

        const userForm = document.getElementById("userForm");
        const username = document.getElementById("usernameInput").value;
        const email = document.getElementById("emailInput").value;
        const password = document.getElementById("passwordInput").value;
        const administrator = document.getElementById("administratorCheck").checked;
        const resetPassword = document.getElementById("resetPasswordCheck").checked;

        const data = {
            'username': username,
            'email': email,
            'password': password,
            'admin': administrator,
            'reset_password': resetPassword,
        }

        try {
            return await makePostAPICall(apiUrl, data);
        } catch (error) {
            throw error;
        }
    }

    async function handleUserEdit() {
        const userForm = document.getElementById("userForm");

        const userId = userForm.dataset.userId;
        const apiUrl = `<?= base_url('api/users') ?>/${userId}`;

        const username = document.getElementById("usernameInput").value;
        const email = document.getElementById("emailInput").value;
        const enabled = document.getElementById("enabledCheck").checked;
        const administrator = document.getElementById("administratorCheck").checked;
        const resetPassword = document.getElementById("resetPasswordCheck").checked;

        const data = {
            'username': username,
            'email': email,
            'active': enabled,
            'admin': administrator,
            'reset_password': resetPassword,
        }

        try {
            return await makePutAPICall(apiUrl, data);
        } catch (error) {
            throw error;
        }
    }

    function handlePasswordValidity() {
        const userForm = document.getElementById("userForm");
        const passwordInput = document.getElementById("passwordInput");
        const confirmPasswordInput = document.getElementById("confirmPasswordInput");
        const passwordMismatchFeedbacks = userForm.querySelectorAll(".password-mismatch-feedback");

        const passwordsMatch = passwordInput.value === confirmPasswordInput.value;

        if (!passwordsMatch) {
            passwordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.add('is-invalid');

            passwordInput.setCustomValidity('lorem');
            confirmPasswordInput.setCustomValidity('lorem');

            for (const passwordMismatchFeedback of passwordMismatchFeedbacks) {
                passwordMismatchFeedback.innerHTML = "Passwords do not match."
            }
        } else {
            passwordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.remove('is-invalid');
            passwordInput.setCustomValidity('');
            confirmPasswordInput.setCustomValidity('');

            for (const passwordMismatchFeedback of passwordMismatchFeedbacks) {
                passwordMismatchFeedback.innerHTML = "Please provide a password."
            }
        }

    }

    document.addEventListener('DOMContentLoaded', function() {
        const userForm = document.getElementById("userForm");
        const createUserButton = document.getElementById("createUserButton");
        const editUserButton = document.getElementById("editUserButton");

        userForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formValidity = userForm.checkValidity();
            let passwordsMatch = true;

            if (createUserButton !== null) {
                passwordsMatch = handlePasswordValidity();
            }

            if (!formValidity || !passwordsMatch) {
                event.stopPropagation();
            }

            userForm.classList.add('was-validated')

            if (!formValidity || !passwordsMatch) {
                return;
            }

            try {
                if (createUserButton !== null) {
                    await handleUserCreate();
                } else if (editUserButton !== null) {
                    await handleUserEdit();
                }
            } catch (error) {
                appendAlert("Something went wrong! Please try again later.", 'danger');
                console.error(error);
                return;
            }

            window.location.href = "<?= base_url('admin/users') ?>";
        });

    });
</script>

<?= $this->endSection() ?>
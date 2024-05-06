<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="my-3">
    <div class="bg-light container py-3 rounded-3">
        <h1 class="display-5 mb-3">Create a New User</h1>
        <form class="container needs-validation" id="userForm" novalidate>

            <div class="mb-3 row">
                <div class="col-12 col-md-6 me-3 me-md-0">
                    <label class="form-label" for="usernameInput">
                        Username: <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="usernameInput" class="form-control" placeholder="jdoe" required>
                    <div class="invalid-feedback">
                        Please choose a username.
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="emailInput">
                        Email: <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="emailInput" class="form-control" placeholder="jdoe@example.com" required>
                    <div class="invalid-feedback">
                        Please provide an email.
                    </div>
                </div>
            </div>
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
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="administratorCheck">
                <label class="form-check-label" for="flexCheckDefault">
                    Adminstrator
                </label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="resetPasswordCheck" checked>
                <label class="form-check-label" for="flexCheckDefault">
                    Reset Password
                </label>
            </div>
            <div class="mb-2 d-grid">
                <div id="alert"></div>
                <button id="createUserButton" type="submit" class="btn btn-primary">Create User</button>
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

    document.addEventListener('DOMContentLoaded', function() {
        const userForm = document.getElementById("userForm");
        const passwordInput = document.getElementById("passwordInput");
        const confirmPasswordInput = document.getElementById("confirmPasswordInput");
        const passwordMismatchFeedbacks = userForm.querySelectorAll(".password-mismatch-feedback");

        userForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formValidity = userForm.checkValidity();
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

            if (!formValidity || !passwordsMatch) {
                event.stopPropagation();
            }

            userForm.classList.add('was-validated')

            if (!formValidity || !passwordsMatch) {
                return;
            }

            try {
                await handleUserCreate();
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
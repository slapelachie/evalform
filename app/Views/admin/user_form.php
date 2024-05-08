<?= $this->extend('base_template') ?>
<?= $this->section('content') ?>

<section class="my-3">
    <div class="bg-light container py-3 rounded-3">
        <h1 class="display-5 mb-3"><?= $formTitle ?></h1>
        <form class="container needs-validation" id="userForm" data-user-id="<?= isset($user)
            ? $user->id
            : '' ?>" novalidate>

            <div class="mb-3 row">
                <div class="col-12 col-md-6 me-3 me-md-0">
                    <label class="form-label" for="usernameInput">
                        Username: <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="usernameInput" class="form-control" value="<?= isset(
                        $user
                    )
                        ? $user->username
                        : '' ?>" placeholder="jdoe" required>
                    <div class="invalid-feedback">
                        Please choose a username.
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="emailInput">
                        Email: <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="emailInput" class="form-control" value="<?= isset($user)
                        ? $user->email
                        : '' ?>" placeholder="jdoe@example.com" required>
                    <div class="invalid-feedback">
                        Please provide an email.
                    </div>
                </div>
            </div>
            <?php if (!isset($user)): ?>
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
            <?php else: ?>
                <!-- TODO: Implement password change feature? -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enabledCheck" <?= $user->active
                        ? 'checked'
                        : '' ?>>
                    <label class="form-check-label" for="enabledCheck">
                        Enabled
                    </label>
                </div>
            <?php endif; ?>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="administratorCheck" <?= isset(
                    $user
                )
                    ? ($user->admin
                        ? 'checked'
                        : '')
                    : '' ?>>
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
                <button id="<?= isset($user)
                    ? 'edit'
                    : 'create' ?>UserButton" type="submit" class="btn btn-primary"><?= isset($user)
    ? 'Modify'
    : 'Create' ?> User</button>
            </div>
        </form>
    </div>
</section>

<script src="<?= base_url('/js/utils.js') ?>"></script>
<script src="<?= base_url('/js/api.js') ?>"></script>

<script>
    /**
     * Handles user action to create or edit a user based on form data and action type.
     * 
     * @param {string} action - The action to perform ('create' or 'edit').
     * @returns {Promise<any>} A promise with the server response or error.
     */
    async function handleUserAction(action) {
        // Retrieve form and user information
        const userForm = document.getElementById("userForm");
        const userId = userForm.dataset.userId;
        const apiUrl = `<?= base_url('api/users') ?>${action === 'edit' ? '/' + userId : ''}`;

        // Collect form data for API request
        const data = {
            'username': document.getElementById("usernameInput").value,
            'email': document.getElementById("emailInput").value,
            'password': action === 'create' ? document.getElementById("passwordInput").value : undefined,
            'active': action === 'edit' ? document.getElementById("enabledCheck").checked : undefined,
            'admin': document.getElementById("administratorCheck").checked,
            'reset_password': document.getElementById("resetPasswordCheck").checked,
        };

        // Determine API method based on action type
        const method = action === 'create' ? 'makePostAPICall' : 'makePutAPICall';
        try {
            return await window[method](apiUrl, data);
        } catch (error) {
            throw error;
        }
    }

    /**
     * Validates password and confirmation inputs, providing user feedback.
     * 
     * @returns {boolean} True if passwords match, false otherwise.
     */
    function handlePasswordValidity() {
        // Retrieve form and password input elements
        const userForm = document.getElementById("userForm");
        const passwordInput = document.getElementById("passwordInput");
        const confirmPasswordInput = document.getElementById("confirmPasswordInput");
        const passwordMismatchFeedbacks = userForm.querySelectorAll(".password-mismatch-feedback");

        const passwordsMatch = passwordInput.value === confirmPasswordInput.value;

        // Update validity state classes based on password match
        passwordInput.classList.toggle('is-invalid', !passwordsMatch);
        confirmPasswordInput.classList.toggle('is-invalid', !passwordsMatch);

        const validityMessage = passwordsMatch ? "" : "Passwords do not match.";
        passwordInput.setCustomValidity(validityMessage);
        confirmPasswordInput.setCustomValidity(validityMessage);

        // Update user feedback messages
        for (const passwordMismatchFeedback of passwordMismatchFeedbacks) {
            passwordMismatchFeedback.innerHTML = passwordsMatch
                ? "Please provide a password."
                : "Passwords do not match.";
        }

        return passwordsMatch;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const userForm = document.getElementById("userForm");
        const createUserButton = document.getElementById("createUserButton");
        const editUserButton = document.getElementById("editUserButton");

        userForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            let passwordsMatch = createUserButton !== null ? handlePasswordValidity() : true;

            // Validate entire form
            const formValidity = userForm.checkValidity();
            userForm.classList.add('was-validated')

            // Prevent further processing if validation fails
            if (!formValidity || !passwordsMatch) {
                event.stopPropagation();
                return;
            }

            try {
                await handleUserAction(createUserButton !== null ? 'create' : 'edit');
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

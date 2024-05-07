<?= $this->extend('admin/sidebar') ?>
<?= $this->section('content') ?>


<div class="d-flex justify-content-between align-items-center mb-2">
    <h1>User Management</h1>
    <div>
        <a class="btn btn-outline-primary" href="<?= base_url('admin/users/create') ?>">New User</a>
        <button class="btn btn-outline-primary" type="button" onclick="refreshUsers()">Refresh</button>
    </div>
</div>

<div id="alert"></div>

<div id="usersContainer">
    <table id="userTable" class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Last Active</th>
                <th>Status</th>
                <th>Administrator</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                <button id="confirmUserDeleteButton" type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="deleteUser()">Yes, delete this user.</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<template id="userRowTemplate">
    <tr class="user-row">
        <td class="username"></td>
        <td class="last-active"></td>
        <td class="status"></td>
        <td class="is-admin"></td>
        <td class="user-actions text-end">
            <a class="manage-button btn btn-primary btn-sm" href="#">Edit</a>
            <button type="button" class="status-toggle-button btn btn-warning btn-sm">Disable</button>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal">Delete</button>
        </td>
    </tr>
</template>

<?= view('snippets/common_scripts') ?>
<?= view('snippets/api_scripts') ?>

<script>
    /**
     * Fetches a list of users from the API endpoint
     * 
     * @returns {Promise<Object[]>} List of users
     * @throws Will re-throw any errors encountered during the API call
    */
    async function getUsers() {
        const apiUrl = `<?= base_url('/api/users') ?>`;

        try {
            return await makeGetAPICall(apiUrl)
        } catch (error) {
            throw error;
        }
    }

    /**
     * Generates a human-readable "time ago" string based on a timestamp
     * 
     * Source: ChatGPT4
     * 
     * @param {string} timestamp - Timestamp in the format expected by `Date`
     * @returns {string} Readable "time ago" string
     */
    function generateTimeAgo(timestamp) {
        const now = new Date();
        const then = new Date(timestamp + 'Z');
        const seconds = Math.floor((now - then) / 1000);

        // Helper function to calculate the appropriate interval
        function calculateInterval(value, label) { 
            const interval = Math.floor(seconds / value);
            return interval >= 1 ? `${interval} ${label} ago` : null;
        }

        const intervals = [
            { value: 31536000, label: 'years' },
            { value: 2592000, label: 'months' },
            { value: 86400, label: 'days' },
            { value: 3600, label: 'hours' },
            { value: 60, label: 'minutes' }
        ];

        // Iterate through intervals to determine the most appropriate "time ago"
        for (const { value, label } of intervals) {
            const result = calculateInterval(value, label);
            if (result) return result;
        }

        return seconds > 30 ? `${Math.floor(seconds)} seconds ago` : 'Moments ago';
    }

    /**
     * Generates a table row for a user
     * 
     * @param {Object} user - User object containing user data
     * @returns {HTMLTableRowElement} Populated table row for the user
     */
    function generateUserRow(user) {
        const template = document.getElementById("userRowTemplate");
        const newUserRow = template.content.cloneNode(true).querySelector('tr');

        // Extract target elements in the new row to populate
        const usernameElement = newUserRow.querySelector('.username')
        const lastActiveElement = newUserRow.querySelector('.last-active')
        const statusElement = newUserRow.querySelector('.status')
        const adminElement = newUserRow.querySelector('.is-admin')

        const manageButton = newUserRow.querySelector('.manage-button');
        const statusToggleButton = newUserRow.querySelector('.status-toggle-button');
        const deleteButton = newUserRow.querySelector('.delete-button');

        // Attach user data attributes to the row for later use
        newUserRow.dataset.userId = user['id'];
        newUserRow.dataset.username = user['username'];
        newUserRow.dataset.status = true;

        // If user is disabled, update UI accordingly
        if (!user["active"]) {
            usernameElement.classList.add("text-muted")
            lastActiveElement.classList.add("text-muted")
            statusElement.classList.add("text-muted")
            adminElement.classList.add("text-muted")

            statusToggleButton.classList.replace("btn-warning", "btn-primary");
            statusToggleButton.textContent = "Enable";

            newUserRow.dataset.status = false;
        }

        // Set user-specific information in the new row
        usernameElement.textContent = user['username'];
        statusElement.textContent = user["active"] ? "Enabled" : "Disabled";
        adminElement.textContent = user["admin"] ? "Yes" : "No";

        // Handle last active time display
        if (user['last_active'] != null) {
            lastActiveElement.textContent = generateTimeAgo(user['last_active']['date']);
        } else {
            lastActiveElement.textContent = "Never"
        }

        manageButton.href = `<?= base_url('admin/users') ?>/${user['id']}`;

        return newUserRow;
    }

    /**
     * Populates the user table by fetching user data and adding each user row
     */
    async function presentUsers() {
        const userTable = document.getElementById("userTable");
        const userTableBody = userTable.querySelector("tbody");

        try {
            const users = await getUsers();
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            return;
        }

        users.forEach(user => userTableBody.appendChild(generateUserRow(user)));
    }

    /**
     * Refreshes the user table by clearing and re-populating it
     */
    async function refreshUsers() {
        const userTable = document.getElementById("userTable");
        const userTableBody = userTable.querySelector("tbody");

        // Clear the table body
        userTableBody.innerHTML = '';

        await presentUsers();
    }

    /**
     * Toggles a user's status (active/inactive)
     * 
     * @param {string} userId - The ID of the user to toggle status for
     * @param {boolean} status - The current status to toggle from
     * @returns {Promise<Object>} Updated user data
     */
    async function toggleUserStatus(userId, status) {
        const data = {
            'active': !status
        }

        const apiUrl = `<?= base_url('api/users') ?>/${userId}`;
        return await makePutAPICall(apiUrl, data);
    }

    /**
     * Deletes a user by ID via an API call
     * 
     * @param {string} userId - The ID of the user to delete
     * @returns {Promise<void>}
     */
    async function deleteUser(userId) {
        const apiUrl = `<?= base_url('api/users') ?>/${userId}`;
        const deleteButton = document.getElementById("confirmUserDeleteButton");

        try {
            await makeDeleteAPICall(apiUrl);
        } catch (error) {
            appendAlert("Failed to delete the user! Please try again later.");
            console.error(error);
            return;
        }

        await refreshUsers();
    }

    document.addEventListener('DOMContentLoaded', async function() {
        // Get the query params
        const urlParams = new URLSearchParams(window.location.search);

        // Display users list
        await presentUsers();

        document.addEventListener('click', async function(event) {
            const target = event.target;
            if (target.classList.contains('status-toggle-button')) {
                // Handle status toggle button clicks
                const statusToggleButton = target;
                const closestUserRow = statusToggleButton.closest('.user-row');
                const userId = closestUserRow.dataset.userId;
                const status = closestUserRow.dataset.status === "true";

                // Disable the button to prevent duplicate clicks
                statusToggleButton.disabled = true;

                try {
                    await toggleUserStatus(userId, status);
                } catch (error) {
                    appendAlert("Failed to change the status of the user! Please try again later.", "danger")
                    console.error(error);
                    statusToggleButton.disabled = false;
                    return;
                }

                refreshUsers();
            } else if (target.classList.contains('delete-button')) {
                const closestUserRow = target.closest('.user-row');

                // Set up modal dialog for confirming deletion
                const deleteModalLabel = document.getElementById("deleteUserLabel");
                const deleteModal = document.getElementById("deleteUserModal");
                const deleteButton = document.getElementById("confirmUserDeleteButton");

                deleteModalLabel.textContent = `Delete User "${closestUserRow.dataset.username}"`;
                deleteButton.onclick = function() {
                    deleteUser(closestUserRow.dataset.userId);
                }
            }

        });
    });
</script>

<?= $this->endSection() ?>

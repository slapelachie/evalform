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
            <button type="button" class="status-toggle-button btn btn-warning btn-sm">Deactivate</button>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal">Delete</button>
        </td>
    </tr>
</template>

<?= view('snippets/common_scripts') ?>
<?= view('snippets/api_scripts') ?>

<script>
    async function getUsers() {
        apiUrl = `<?= base_url('/api/users') ?>`;

        try {
            return await makeGetAPICall(apiUrl)
        } catch (error) {
            throw error;
        }
    }

    // Generated using ChatGPT4
    function generateTimeAgo(timestamp) {
        const now = new Date();
        const then = new Date(timestamp + 'Z');
        const seconds = Math.floor((now - then) / 1000);
        let interval = Math.floor(seconds / 31536000);

        if (interval > 1) {
            return interval + " years ago";
        }
        interval = Math.floor(seconds / 2592000);
        if (interval > 1) {
            return interval + " months ago";
        }
        interval = Math.floor(seconds / 86400);
        if (interval > 1) {
            return interval + " days ago";
        }
        interval = Math.floor(seconds / 3600);
        if (interval > 1) {
            return interval + " hours ago";
        }
        interval = Math.floor(seconds / 60);
        if (interval > 1) {
            return interval + " minutes ago";
        }
        if (seconds > 30) {
            return Math.floor(seconds) + " seconds ago";
        }
        return "Moments ago";
    }

    function generateUserRow(user) {
        const template = document.getElementById("userRowTemplate");
        const newUserRow = template.content.cloneNode(true).querySelector('tr');

        const usernameElement = newUserRow.querySelector('.username')
        const lastActiveElement = newUserRow.querySelector('.last-active')
        const statusElement = newUserRow.querySelector('.status')
        const adminElement = newUserRow.querySelector('.is-admin')
        const manageButton = newUserRow.querySelector('.manage-button');
        const statusToggleButton = newUserRow.querySelector('.status-toggle-button');
        const deleteButton = newUserRow.querySelector('.delete-button');

        newUserRow.dataset.userId = user['id'];
        newUserRow.dataset.username = user['username'];
        newUserRow.dataset.status = true;

        if (!user["active"]) {
            usernameElement.classList.add("text-muted")
            lastActiveElement.classList.add("text-muted")
            statusElement.classList.add("text-muted")
            adminElement.classList.add("text-muted")

            statusToggleButton.classList.remove("btn-warning");
            statusToggleButton.classList.add("btn-primary");
            statusToggleButton.textContent = "Activate";

            newUserRow.dataset.status = false;
        }

        usernameElement.textContent = user['username'];
        statusElement.textContent = user["active"] ? "Enabled" : "Disabled";
        adminElement.textContent = user["admin"] ? "Yes" : "No";

        if (user['last_active'] != null) {
            lastActiveElement.textContent = generateTimeAgo(user['last_active']['date']);
        } else {
            lastActiveElement.textContent = "Never"
        }

        manageButton.href = `<?= base_url('admin/users') ?>/${user['id']}`;

        return newUserRow;
    }

    async function presentUsers() {
        const userTable = document.getElementById("userTable");
        const userTableBody = userTable.querySelector("tbody");

        try {
            var users = await getUsers();
        } catch (error) {
            appendAlert("Something went wrong! Please try again later.", 'danger');
            console.error(error);
            return;
        }

        for (const user of users) {
            const userRow = generateUserRow(user);
            userTableBody.appendChild(userRow);
        }
    }

    async function refreshUsers() {
        const userTable = document.getElementById("userTable");
        const userTableBody = userTable.querySelector("tbody");

        userTableBody.innerHTML = '';

        await presentUsers();
    }

    async function toggleUserStatus(userId, status) {
        data = {
            'active': !status
        }

        const apiUrl = `<?= base_url('api/users') ?>/${userId}`;
        return await makePutAPICall(apiUrl, data);
    }

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
        // const status = urlParams.get('page'); // Will be useful for pagination?

        // Display users list
        await presentUsers();

        document.addEventListener('click', async function(event) {
            const target = event.target;
            if (target.classList.contains('status-toggle-button')) {
                const statusToggleButton = target;
                const closestUserRow = statusToggleButton.closest('.user-row');
                const userId = closestUserRow.dataset.userId;
                const status = closestUserRow.dataset.status === "true";

                statusToggleButton.disabled = true;

                try {
                    var updatedUser = await toggleUserStatus(userId, status)
                } catch (error) {
                    appendAlert("Failed to change the status of the user! Please try again later.", "danger")
                    console.error(error);
                    statusToggleButton.disabled = false;
                    return;
                }

                refreshUsers();
            } else if (target.classList.contains('delete-button')) {
                const closestUserRow = target.closest('.user-row');

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
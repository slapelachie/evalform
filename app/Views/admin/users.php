<?= $this->extend('admin/sidebar') ?>
<?= $this->section('content') ?>

<h1>User Management</h1>

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

<template id="userRowTemplate">
    <tr class="user-row">
        <td class="username"></td>
        <td class="last-active"></td>
        <td class="status"></td>
        <td class="is-admin"></td>
        <td class="user-actions text-end">
            <a class="manage-button btn btn-primary btn-sm" href="#">Manage</a>
            <button type="button" class="status-toggle-button btn btn-warning btn-sm">Deactivate</button>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal">Delete</button>
        </td>
    </tr>
</template>

<script>
    async function makeAPIGetCall(apiUrl) {
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
            });

            if (!response.ok) {
                const errorResponse = await response.json();
                console.error(`API request failed with status ${response.status}: ${response.statusText}\n`, errorResponse);
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            throw error;
        }
    }

    async function getUsers() {
        apiUrl = `<?= base_url('/api/users') ?>`;

        try {
            return await makeAPIGetCall(apiUrl)
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
        const statusToggleButton = newUserRow.querySelector('.status-toggle-button');

        newUserRow.dataset.userId = user['id'];
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

        // TODO: Add last active row with content like "3 hours ago" etc
        usernameElement.textContent = user['username'];
        lastActiveElement.textContent = generateTimeAgo(user['last_active']['date']);
        statusElement.textContent = user["active"] ? "Enabled" : "Disabled";
        adminElement.textContent = user["admin"] ? "Yes" : "No";

        // TODO: Add functionality to buttons

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

    async function toggleUserStatus(userId, status) {
        data = {
            'active': !status
        }

        console.log(userId, status, data)

        try {
            const response = await fetch(`<?= base_url('api/users') ?>/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorResponse = await response.json();
                console.error(`API request failed with status ${response.status}: ${response.statusText}\n`, errorResponse);
                throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            throw error;
        }
    }

    document.addEventListener('DOMContentLoaded', async function() {
        // Get the query params
        const urlParams = new URLSearchParams(window.location.search);
        // const status = urlParams.get('page'); // Will be useful for pagination?

        // Display users list
        await presentUsers();

        document.addEventListener('click', async function(event) {
            if (event.target.classList.contains('status-toggle-button')) {
                const statusToggleButton = event.target;
                const closestUserRow = statusToggleButton.closest('.user-row');
                const userId = closestUserRow.dataset.userId;
                const status = closestUserRow.dataset.status === "true";

                console.log(status, closestUserRow.dataset.status)

                statusToggleButton.disabled = true;

                try {
                    var updatedUser = await toggleUserStatus(userId, status)
                } catch (error) {
                    console.error(error);
                    statusToggleButton.disabled = false;
                    return;
                }

                console.log(updatedUser);
            }

        });
    });
</script>

<?= $this->endSection() ?>
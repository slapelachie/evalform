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
                <th>Is an Admin?</th>
                <th class="w-25 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<template id="userRowTemplate">
    <tr>
        <td class="username"></td>
        <td class="last-active"></td>
        <td class="is-admin"></td>
        <td class="user-actions text-end">
            <a class="manage-button btn btn-primary btn-sm" href="#">Manage</a>
            <button class="delete-button btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSurveyModal" data-survey-id="" data-survey-name="">Delete</button>
        </td>
    </tr>
</template>

<script>
    async function makeAPICall(apiUrl) {
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
            return await makeAPICall(apiUrl)
        } catch (error) {
            throw error;
        }
    }

    function generateUserRow(user) {
        const template = document.getElementById("userRowTemplate");
        const newUserRow = template.content.cloneNode(true);

        console.log(user)

        newUserRow.querySelector(".username").textContent = user['username'];
        newUserRow.querySelector(".last-active").textContent = "Lorem Ipsum.";
        newUserRow.querySelector(".is-admin").textContent = user["admin"] ? "Yes" : "No";

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

    document.addEventListener('DOMContentLoaded', async function() {
        // Get the query params
        const urlParams = new URLSearchParams(window.location.search);
        // const status = urlParams.get('page'); // Will be useful for pagination?

        // Display users list
        await presentUsers();
    });
</script>

<?= $this->endSection() ?>
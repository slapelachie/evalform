<script>
    async function handleFetchResponse(response) {
        if (!response.ok) {
            const errorResponse = await response.json();
            console.error(`API request failed with status ${response.status}: ${response.statusText}\n`, errorResponse);
            throw new Error(`API request failed with status ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    async function makeAPICall(apiUrl, method = 'GET', data = null) {
        try {
            const fetchOptions = {
                method,
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            if (data && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
                fetchOptions.body = JSON.stringify(data);
            }

            // Make the request
            const response = await fetch(apiUrl, fetchOptions);

            // Handle and return the response
            return await handleFetchResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async function makePostAPICall(apiUrl, data) {
        return await makeAPICall(apiUrl, 'POST', data);
    }

    async function makeGetAPICall(apiUrl) {
        return await makeAPICall(apiUrl, 'GET');
    }

    async function makePutAPICall(apiUrl, data) {
        return await makeAPICall(apiUrl, 'PUT', data);
    }

    async function makeDeleteAPICall(apiUrl) {
        return await makeAPICall(apiUrl, 'DELETE');
    }
</script>
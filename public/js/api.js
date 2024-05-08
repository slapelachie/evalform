/**
 * Handles the response from a fetch request, checking for errors and parsing JSON data.
 *
 * @param {Response} response - The response object from the fetch request.
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON data from the response.
 * @throws {Error} If the response indicates an error.
 */
async function handleFetchResponse(response) {
    if (!response.ok) {
        const errorResponse = await response.json();
        console.error(
            `API request failed with status ${response.status}: ${response.statusText}\n`,
            errorResponse,
        );
        throw new Error(
            `API request failed with status ${response.status}: ${response.statusText}`,
        );
    }

    return await response.json();
}

/**
 * Makes an API call to the specified URL with the given HTTP method and data.
 *
 * @param {string} apiUrl - The URL of the API endpoint.
 * @param {string} [method='GET'] - The HTTP method for the request (default is 'GET').
 * @param {Object|null} [data=null] - The data to be sent with the request body (optional).
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response from the API.
 * @throws {Error} If an error occurs during the API call.
 */
async function makeAPICall(apiUrl, method = 'GET', data = null) {
    try {
        const fetchOptions = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
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

/**
 * Makes a POST request to the specified API URL with the provided data.
 *
 * @param {string} apiUrl - The URL of the API endpoint.
 * @param {Object} data - The data to be sent with the request body.
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response from the API.
 * @throws {Error} If an error occurs during the API call.
 */
async function makePostAPICall(apiUrl, data) {
    return await makeAPICall(apiUrl, 'POST', data);
}

/**
 * Makes a GET request to the specified API URL.
 *
 * @param {string} apiUrl - The URL of the API endpoint.
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response from the API.
 * @throws {Error} If an error occurs during the API call.
 */
async function makeGetAPICall(apiUrl) {
    return await makeAPICall(apiUrl, 'GET');
}

/**
 * Makes a PUT request to the specified API URL with the provided data.
 *
 * @param {string} apiUrl - The URL of the API endpoint.
 * @param {Object} data - The data to be sent in the PUT request.
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response from the API.
 * @throws {Error} If an error occurs during the API call.
 */
async function makePutAPICall(apiUrl, data) {
    return await makeAPICall(apiUrl, 'PUT', data);
}

/**
 * Makes a DELETE request to the specified API URL.
 *
 * @param {string} apiUrl - The URL of the API endpoint to be deleted.
 * @returns {Promise<Object>} A promise that resolves to the parsed JSON response from the API.
 * @throws {Error} If an error occurs during the API call.
 */
async function makeDeleteAPICall(apiUrl) {
    return await makeAPICall(apiUrl, 'DELETE');
}

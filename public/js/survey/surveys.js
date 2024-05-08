/**
 * Deletes a survey via an API call and presents the updated list of surveys.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} surveyId - The ID of the survey to delete.
 * @param {string} userId - The ID of the user associated with the survey.
 * @throws {Error} If an error occurs while making the API call.
 */
async function deleteSurvey(apiUrl, rootUrl, surveyId, userId) {
    let endpointUrl = `${apiUrl}/surveys/${surveyId}`;

    try {
        await makeDeleteAPICall(endpointUrl);
    } catch (error) {
        appendAlert('Failed to delete the survey! Please try again later.', 'danger');
        console.error(error);
        return;
    }

    presentSurveys(apiUrl, rootUrl, userId);
    return;
}

/**
 * Retrieves surveys from the API for a specific user ID and optional status and pagination.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} userId - The ID of the user whose surveys to retrieve.
 * @param {string|null} surveyStatus - (Optional) The status of surveys to retrieve.
 * @param {number} page - (Optional) The page number for pagination.
 * @returns {Promise<Array<Object>>} A promise that resolves with an array of survey objects.
 * @throws {Error} If an error occurs while making the API call.
 */
async function getSurveys(apiUrl, userId, surveyStatus = null, page = 1) {
    let endPointUrl = `${apiUrl}/surveys?owner_id=${userId}`;

    if (surveyStatus != null) {
        endPointUrl += `&status=${surveyStatus}`;
    }

    if (page != 1) {
        endPointUrl += `&page=${page}`;
    }

    try {
        return await makeGetAPICall(endPointUrl);
    } catch (error) {
        throw error;
    }
}

/**
 * Retrieves the count of responses for a survey via an API call.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} surveyId - The ID of the survey for which to retrieve response count.
 * @returns {Promise<number>} A promise that resolves with the count of responses for the survey.
 * @throws {Error} If an error occurs while making the API call.
 */
async function getSurveyResponseCount(apiUrl, surveyId) {
    let endpointUrl = `${apiUrl}/responses?survey_id=${surveyId}&count`;

    let responses;
    try {
        responses = await makeGetAPICall(endpointUrl);
    } catch (error) {
        throw error;
    }

    return responses.count;
}

/**
 * Generates a survey row based on the provided survey data.
 * Retrieves the count of responses for the survey and populates the row accordingly.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {Object} survey - The survey object containing survey data.
 * @returns {Promise<DocumentFragment>} A promise that resolves with the generated survey row as a DocumentFragment.
 * @throws {Error} If an error occurs while retrieving the response count.
 */
async function generateSurveyRow(apiUrl, rootUrl, survey) {
    const template = document.getElementById('surveyRowTemplate');
    const newSurvey = template.content.cloneNode(true);

    const surveyResponseCount = await getSurveyResponseCount(apiUrl, survey['id']);
    newSurvey.querySelector('.survey-name').textContent = survey['name'];
    newSurvey.querySelector('.survey-responses').textContent = surveyResponseCount;
    newSurvey.querySelector('.manage-button').href = `${rootUrl}/surveys/${survey['id']}/manage`;

    const deleteButton = newSurvey.querySelector('.delete-button');
    deleteButton.dataset.surveyId = survey['id'];
    deleteButton.dataset.surveyName = survey['name'];

    const shareButton = newSurvey.querySelector('.share-button');
    shareButton.dataset.surveyId = survey['id'];
    shareButton.dataset.surveyName = survey['name'];

    return newSurvey;
}

/**
 * Retrieves the value of a query parameter from the current URL.
 *
 * @param {string} param - The name of the query parameter to retrieve.
 * @returns {string|null} The value of the query parameter, or null if not found.
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Retrieves surveys from the API for a specific user, presents them in a table, and handles pagination.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} userId - The ID of the user whose surveys to present.
 * @throws {Error} If an error occurs while retrieving surveys.
 */
async function presentSurveys(apiUrl, rootUrl, userId) {
    const surveyTable = document.getElementById('surveyTable');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingContainer = document.getElementById('loadingContainer');
    const surveyTableBody = surveyTable.querySelector('tbody');

    // Get survey status filter value and page parameter from URL
    const surveyStatusFilter = document.getElementById('surveyStatusFilter');
    const surveyStatusValue = surveyStatusFilter.value != 'any' ? surveyStatusFilter.value : null;
    const pageParam = getQueryParam('page') ?? 1;

    try {
        var paginatedSurveys = await getSurveys(apiUrl, userId, surveyStatusValue, pageParam);
    } catch (error) {
        appendAlert('Something went wrong! Please try again later.', 'danger');
        console.error(error);
        return;
    }

    // Show loading element
    loadingContainer.classList.remove('d-none');

    // Clear current surveys if they are already presented
    surveyTableBody.innerHTML = '';

    const surveys = paginatedSurveys['results'];
    const pagination = paginatedSurveys['pagination'];

    // Replace API links with regular links in pagination
    const paginationLinks = pagination['links'].replace(/\/api/g, '');

    paginationContainer.innerHTML = paginationLinks;

    // Generate survey rows for each survey
    let surveyRows = [];
    for (const survey of surveys) {
        surveyRows.push(await generateSurveyRow(apiUrl, rootUrl, survey));
    }

    loadingContainer.classList.add('d-none');

    // Append survey rows to the survey table body
    for (const surveyRow of surveyRows) {
        surveyTableBody.append(surveyRow);
    }
}

/**
 * Refreshes the presentation of surveys in the survey table.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} userId - The ID of the user whose surveys to refresh.
 */
async function refreshSurveys(apiUrl, rootUrl, userId) {
    // function exists in case I want to change this
    await presentSurveys(apiUrl, rootUrl, userId);
}

/**
 * Initializes event handlers for UI elements to trigger actions.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} userId - The ID of the user.
 */
async function initialiseEventHandlers(apiUrl, rootUrl, userId) {
    document.getElementById('applyFiltersButton').addEventListener('click', async function () {
        presentSurveys(apiUrl, rootUrl, userId);
    });

    document.getElementById('refreshButton').addEventListener('click', async function () {
        refreshSurveys(apiUrl, rootUrl, userId);
    });
}

/**
 * Applies filters to the UI elements based on query parameters in the URL.
 */
function applyFiltersFromQueryParams() {
    // Get the query params
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    // Apply any filters
    if (status) {
        const surveyStatusFilter = document.getElementById('surveyStatusFilter');
        surveyStatusFilter.value = status;
    }
}

/**
 * Handles the click event of a delete button to delete the corresponding survey.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} userId - The ID of the user.
 * @param {HTMLElement} target - The target element triggering the event.
 */
function handleDeleteButton(apiUrl, rootUrl, userId, target) {
    const deleteModalLabel = document.getElementById('deleteSurveyLabel');
    const deleteButton = document.getElementById('confirmSurveyDeleteButton');

    const surveyId = target.dataset.surveyId;
    const surveyName = target.dataset.surveyName;

    deleteModalLabel.textContent = `Delete Survey "${surveyName}"`;
    deleteButton.onclick = function () {
        deleteSurvey(apiUrl, rootUrl, surveyId, userId);
    };
}

/**
 * Handles the click event of a share button to display the share modal and generate a QR code for the survey.
 *
 * @param {string} rootUrl - The root URL of the application.
 * @param {HTMLElement} target - The target element triggering the event.
 */
function handleShareButton(rootUrl, target) {
    const shareModalLabel = document.getElementById('shareSurveyLabel');
    const qrcodeElement = document.getElementById('qrcode');

    const surveyId = target.dataset.surveyId;
    const surveyName = target.dataset.surveyName;

    qrcodeElement.innerHTML = '';

    // Setup QRCode
    new QRCode(qrcodeElement, `${rootUrl}/surveys/${surveyId}`);

    shareModalLabel.textContent = `Share Survey "${surveyName}"`;
}

/**
 * Sets up event listeners for buttons within the survey table.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} userId - The ID of the user.
 */
function setupSurveyTableButtonListeners(apiUrl, rootUrl, userId) {
    const surveyTable = document.getElementById('surveyTable');

    surveyTable.querySelector('tbody').addEventListener('click', function (event) {
        const target = event.target;
        if (target.classList.contains('delete-button')) {
            handleDeleteButton(apiUrl, rootUrl, userId, target);
        } else if (target.classList.contains('share-button')) {
            handleShareButton(rootUrl, target);
        }
    });
}

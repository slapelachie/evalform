/*
 * Converts the given date to a Unix timestamp.
 *
 * @param {number} date - The date object to convert.
 * @returns {number} The Unix timestamp representing the given date.
 */
function convertToUnixTime(date) {
    return Math.floor(date / 1000);
}

/**
 * Converts the given local date string to the Unix timestamp representing the start of the UTC day.
 *
 * @param {string} dateStr - The local date string to convert.
 * @returns {number|null} The Unix timestamp representing the start of the UTC day, or null if
 * conversion fails.
 */
function getUTCDayStartUnix(dateStr) {
    const localDate = new Date(dateStr);

    if (isNaN(localDate.getTime())) {
        return null;
    }

    const utcTime = new Date(
        localDate.getUTCFullYear(),
        localDate.getUTCMonth(),
        localDate.getUTCDate(),
        0,
        0,
        0,
        0,
    );

    return convertToUnixTime(utcTime.getTime());
}

/**
 * Converts the given local date string to the Unix timestamp representing the end of the UTC day.
 *
 * @param {string} dateStr - The local date string to convert.
 * @returns {number|null} The Unix timestamp representing the end of the UTC day, or null if
 * conversion fails.
 */
function getUTCDayEndUnix(dateStr) {
    const localDate = new Date(dateStr);

    if (isNaN(localDate.getTime())) {
        return null;
    }

    const utcTime = new Date(
        localDate.getUTCFullYear(),
        localDate.getUTCMonth(),
        localDate.getUTCDate(),
        23,
        59,
        59,
        999,
    );

    return convertToUnixTime(utcTime.getTime());
}

/**
 * Applies filters to the survey data and updates the displayed results accordingly.
 *
 * @param {string} apiUrl - The base URL of the API.
 */
async function applyFilters(apiUrl) {
    const questionTypeFilter = document.getElementById('questionTypeFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');

    // Extract filter values, converting empty values to null
    const questionTypeValue = questionTypeFilter.value !== '' ? questionTypeFilter.value : null;
    const startDateUnix = !isNaN(startDateFilter.valueAsNumber)
        ? getUTCDayStartUnix(startDateFilter.value)
        : null;
    const endDateUnix = !isNaN(endDateFilter.valueAsNumber)
        ? getUTCDayEndUnix(endDateFilter.value)
        : null;

    await setupAccordion(apiUrl, questionTypeValue, startDateUnix, endDateUnix);
}

/**
 * Sets attributes for a question within an accordion item.
 *
 * @param {HTMLElement} questionAccordion - The question accordion item.
 * @param {Object} question - An object containing data for the question.
 * Expected to include 'id', 'question_number', and 'question'.
 */
function setQuestionAttributes(questionAccordion, question) {
    const questionItem = questionAccordion.querySelector('.question-item');
    questionItem.dataset.questionId = question['id'];

    const accordionHeader = questionAccordion.querySelector('.accordion-header');
    accordionHeader.id = `questionHeader${question['id']}`;

    const accordionHeaderButton = accordionHeader.querySelector('button');
    accordionHeaderButton.dataset.bsTarget = `#questionBody${question['id']}`;
    accordionHeaderButton.setAttribute('aria-controls', `questionBody${question['id']}`);
    accordionHeaderButton.innerHTML = `Question ${question['question_number']}: ${question['question']}`;

    const accordionCollapse = questionAccordion.querySelector('.accordion-collapse');
    accordionCollapse.id = `questionBody${question['id']}`;
    accordionCollapse.setAttribute('aria-labelledby', `questionHeader${question['id']}`);
}

/**
 * Retrieves answers from the API for a specific question ID.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} questionId - The ID of the question for which answers are requested.
 * @returns {Promise<any>} A promise that resolves with the retrieved answers from the API.
 * @throws {Error} If an error occurs while making the API call.
 */
async function getAnswers(apiUrl, questionId) {
    const endpointUrl = `${apiUrl}/answers?question_id=${questionId}`;

    try {
        return await makeGetAPICall(endpointUrl);
    } catch (error) {
        throw error;
    }
}

/**
 * Sets up the accordion layout for displaying answers to a multiple-choice question.
 * Retrieves answers from the API for the specified question ID and populates the accordion.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {HTMLElement} accordionBody - The body of the accordion where answers will be displayed.
 * @param {string} questionId - The ID of the multiple-choice question.
 * @throws {Error} If an error occurs while retrieving answers from the API.
 */
async function setupMultipleChoiceQuestion(apiUrl, accordionBody, questionId) {
    const multipleChoiceQuestionTemplate = document.getElementById('multipleChoiceAccordion');
    const multipleChoiceAccordion = multipleChoiceQuestionTemplate.content.cloneNode(true);

    accordionBody.append(multipleChoiceAccordion);
    const tableBody = accordionBody.querySelector('tbody');

    const answers = await getAnswers(apiUrl, questionId);
    for (let answer of answers) {
        const tableRowTemplate = document.getElementById('multipleChoiceAnswerRow');
        const tableRow = tableRowTemplate.content.cloneNode(true);

        const answerRow = tableRow.querySelector('.answer-row');
        answerRow.dataset.answerId = answer['id'];
        answerRow.querySelector('.mc-answer').innerHTML = `${answer['answer']}`;

        tableBody.append(tableRow);
    }
}

/**
 * Sets up the accordion layout for displaying questions and their answers.
 * Retrieves answers from the API for multiple-choice questions and populates the accordions.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {Object} question - An object containing data for the question.
 * @param {HTMLElement} accordionQuestionsContainer - The container for accordion questions.
 * @throws {Error} If an error occurs while retrieving answers from the API.
 */
async function setupQuestions(apiUrl, question, accordionQuestionsContainer) {
    const questionAccordionTemplate = document.getElementById('questionAccordion');
    const newQuestionAccordion = questionAccordionTemplate.content.cloneNode(true);

    setQuestionAttributes(newQuestionAccordion, question);

    const accordionBody = newQuestionAccordion.querySelector('.accordion-body');

    if (question['type'] == 'multiple_choice') {
        await setupMultipleChoiceQuestion(apiUrl, accordionBody, question['id']);
    } else if (question['type'] == 'free_text') {
        // TODO: implement this
    }

    accordionQuestionsContainer.append(newQuestionAccordion);
}

/**
 * Retrieves the count of responses for a specific question from the API.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {Object} options - An object containing parameters for the API call.
 * @param {string} options.questionId - The ID of the question for which responses are counted.
 * @param {string|null} options.answerId - (Optional) The ID of the answer to filter responses.
 * @param {number|null} options.startDateUnix - (Optional) The Unix timestamp representing the
 * start date for response filtering.
 * @param {number|null} options.endDateUnix - (Optional) The Unix timestamp representing the end
 * date for response filtering.
 * @returns {Promise<number>} A promise that resolves with the count of responses for the specified
 * question.
 * @throws {Error} If an error occurs while making the API call.
 */
async function getQuestionResponseCount(
    apiUrl,
    { questionId, answerId = null, startDateUnix = null, endDateUnix = null },
) {
    let endPointUrl = `${apiUrl}/question-responses?question_id=${questionId}&count`;

    if (answerId !== null) {
        endPointUrl += `&answer_id=${answerId}`;
    }

    if (startDateUnix !== null) {
        endPointUrl += `&start_date=${startDateUnix}`;
    }

    if (endDateUnix !== null) {
        endPointUrl += `&end_date=${endDateUnix}`;
    }

    try {
        const data = await makeGetAPICall(endPointUrl);
        return data.count;
    } catch (error) {
        throw error;
    }
}

/**
 * Resets all filters to their default values.
 */
function resetFilters() {
    // Reset date inputs
    document.getElementById('startDateFilter').value = '';
    document.getElementById('endDateFilter').value = '';

    // Reset type filter to first option
    document.getElementById('questionTypeFilter').selectedIndex = 0;
}

/**
 * Retrieves questions from the API for a specific survey ID and optional question type.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string|null} questionType - (Optional) The type of questions to retrieve.
 * @returns {Promise<Array<Object>>} A promise that resolves with an array of question objects.
 * @throws {Error} If an error occurs while making the API call.
 */
async function getQuestions(apiUrl, questionType = null) {
    const surveyContainer = document.getElementById('surveyContainer');
    const surveyId = surveyContainer.dataset.surveyId;

    let endPointUrl = `${apiUrl}/questions?survey_id=${surveyId}`;

    if (questionType != null) {
        endPointUrl += `&type=${questionType}`;
    }

    try {
        return await makeGetAPICall(endPointUrl);
    } catch (error) {
        throw error;
    }
}

/**
 * Publishes a survey by updating its status to 'published' via an API call.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} surveyId - The ID of the survey to publish.
 * @throws {Error} If an error occurs while making the API call.
 */
async function publishSurvey(apiUrl, surveyId) {
    const publishSurveyButton = document.getElementById('publishSurveyButton');
    let endpointUrl = `${apiUrl}/surveys/${surveyId}`;

    publishSurveyButton.disabled = true;

    try {
        let surveyData = {
            status: 'published',
        };

        await makePutAPICall(endpointUrl, surveyData);
    } catch (error) {
        appendAlert('Failed to publish this survey! Please try again later.', 'danger');
        console.error(error);
        publishSurveyButton.disabled = false;
        return;
    }

    publishSurveyButton.style.display = 'none';
    appendAlert('Successfully published this survey.', 'success');
}

/**
 * Deletes a survey via an API call and redirects to the dashboard upon success.
 *
 * @param {string} rootUrl - The root URL of the application.
 * @param {string} apiUrl - The base URL of the API.
 * @param {string} surveyId - The ID of the survey to delete.
 * @throws {Error} If an error occurs while making the API call.
 */
async function deleteSurvey(rootUrl, apiUrl, surveyId) {
    let endpointUrl = `${apiUrl}/surveys/${surveyId}`;

    try {
        await makeDeleteAPICall(endpointUrl);
    } catch (error) {
        appendAlert('Failed to delete this survey! Please try again later.', 'danger');
        console.error(error);
        return;
    }

    // Redirect to dashboard
    window.location.href = rootUrl;
    return;
}

/**
 * Refreshes response counts and percentages for questions and their answers.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {number|null} startDateUnix - (Optional) The Unix timestamp representing the start date
 * for response filtering.
 * @param {number|null} endDateUnix - (Optional) The Unix timestamp representing the end date for
 * response filtering.
 * @throws {Error} If an error occurs while fetching response counts.
 */
async function refreshCounts(apiUrl, startDateUnix = null, endDateUnix = null) {
    const questionItems = document.querySelectorAll('.question-item');
    for (let questionItem of questionItems) {
        const questionId = questionItem.dataset.questionId;
        const questionResponseCount = await getQuestionResponseCount(apiUrl, {
            questionId: questionId,
            startDateUnix: startDateUnix,
            endDateUnix: endDateUnix,
        });

        const answerRows = questionItem.querySelectorAll('.answer-row');
        for (let answerRow of answerRows) {
            const answerId = answerRow.dataset.answerId;
            const answerResponseCount = await getQuestionResponseCount(apiUrl, {
                questionId: questionId,
                answerId: answerId,
                startDateUnix: startDateUnix,
                endDateUnix: endDateUnix,
            });

            answerRow.querySelector('.mc-response-count').innerHTML = answerResponseCount;

            if (questionResponseCount != 0) {
                answerRow.querySelector('.mc-response-percent').innerHTML =
                    `${Math.round((answerResponseCount / questionResponseCount) * 100)}%`;
            } else {
                answerRow.querySelector('.mc-response-percent').innerHTML = 'N/A';
            }
        }
    }
}

/**
 * Sets up the accordion layout for displaying questions and their answers.
 * Retrieves questions from the API based on specified filters and populates the accordions.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string|null} questionType - (Optional) The type of questions to retrieve.
 * @param {number|null} startDateUnix - (Optional) The Unix timestamp representing the start date
 * for response filtering.
 * @param {number|null} endDateUnix - (Optional) The Unix timestamp representing the end date for
 * response filtering.
 * @throws {Error} If an error occurs while fetching questions or refreshing response counts.
 */
async function setupAccordion(
    apiUrl,
    questionType = null,
    startDateUnix = null,
    endDateUnix = null,
) {
    const accordionQuestionsContainer = document.getElementById('accordionQuestionsContainer');
    accordionQuestionsContainer.innerHTML = '';

    try {
        var questions = await getQuestions(apiUrl, questionType);
    } catch (error) {
        appendAlert('Something went wrong! Please try again later.', 'danger');
        console.error(error);
        return;
    }

    for (let question of questions) {
        await setupQuestions(apiUrl, question, accordionQuestionsContainer);
    }

    refreshCounts(apiUrl, startDateUnix, endDateUnix);
}

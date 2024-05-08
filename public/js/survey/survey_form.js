/**
 * Extracts the answers from the provided question container.
 *
 * @param {HTMLElement} questionContainer - The container element holding all answers.
 * @returns {Array<Object>} An array of answer objects containing position, answer text, and optionally an ID.
 */
function getQuestionAnswers(questionContainer) {
    const answersContainer = questionContainer.querySelector('.answers-container');
    const answerContainers = answersContainer.querySelectorAll('.answer-container');

    // Map through each answer container and gather the answer data
    const answers = Array.from(answerContainers, (answerContainer) => {
        const answerId = answerContainer.dataset.answerId;
        const { answerNumber } = answerContainer.dataset;

        let answerData = {
            position: parseInt(answerNumber),
            answer: answerContainer.querySelector('input').value.trim(),
        };

        if (answerId !== undefined) {
            answerData['id'] = answerId;
        }

        return answerData;
    });

    return answers;
}

/**
 * Retrieves all questions from the DOM and returns them as an array of objects.
 * Each question object contains its number, type, text, and optional answers.
 *
 * @returns {Array<Object>} Array of question objects
 */
function getQuestions() {
    const questionsContainer = document.getElementById('questionsContainer');
    const questionContainers = questionsContainer.querySelectorAll('.question-container');

    // Map through each question container and gather the question data
    const questions = Array.from(questionContainers, (questionContainer) => {
        const { questionType, questionId, questionNumber } = questionContainer.dataset;

        // Retrieve answers if the question type is multiple-choice
        let answers =
            questionType === 'multiple_choice' ? getQuestionAnswers(questionContainer) : [];

        let questionData = {
            question_number: parseInt(questionNumber),
            type: questionType,
            question: questionContainer.querySelector('input').value.trim(),
            answers: answers,
        };

        if (questionId !== undefined) {
            questionData['id'] = questionId;
        }

        return questionData;
    });

    return questions;
}

/**
 * Retrieves survey data from the DOM, including its title, user ID, and associated questions.
 *
 * @returns {Object} An object containing the survey data, including the name, description, owner ID, and questions
 */
function getSurveyData() {
    const surveyTitle = document.getElementById('surveyTitle').value.trim();
    const userId = document.getElementById('surveyForm').dataset.userId;

    const surveyData = {
        name: surveyTitle,
        description: 'Lorem',
        owner_id: userId,
        questions: getQuestions(),
    };

    return surveyData;
}

/**
 * Submits survey data to the specified API endpoint. If a survey ID is provided, it updates the
 * survey; otherwise, it creates a new one.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @param {string|null} [surveyId=null] - The ID of the survey to update.
 * If null, creates a new survey.
 * @returns {Promise<string>} The ID of the submitted or updated survey.
 * @throws Will throw an error if the API request fails.
 */
async function submitSurvey(apiUrl, surveyId = null) {
    const endpointUrl = `${apiUrl}/surveys`;
    const surveyData = getSurveyData();
    let response;

    try {
        if (surveyId !== null) {
            response = await makePutAPICall(`${endpointUrl}/${surveyId}`, surveyData);
        } else {
            response = await makePostAPICall(endpointUrl, surveyData);
        }
    } catch (error) {
        throw error;
    }

    return response.id;
}

/**
 * Disables the "Create Survey" button and attempts to submit a new survey using the provided API
 * URL.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @returns {Promise<string>} The ID of the newly created survey.
 * @throws Will throw an error if the survey submission fails.
 */
async function createSurvey(apiUrl) {
    const createSurveyButton = document.getElementById('createSurveyButton');

    createSurveyButton.disabled = true;

    let surveyId;
    // Try submitting the survey
    try {
        surveyId = await submitSurvey(apiUrl);
    } catch (error) {
        createSurveyButton.disabled = false;
        throw error;
    }

    return surveyId;
}

/**
 * Populates the survey form fields with existing survey data.
 *
 * @param {Object} surveyData - The data of the survey to populate. Expected to include an 'id'.
 * @param {Array<Object>} questions - An array of questions to populate in the form.
 */
function populateSurveyFields(surveyData, questions) {
    const surveyForm = document.getElementById('surveyForm');

    surveyForm.dataset.surveyId = surveyData['id'];

    populateSurveyTitle(surveyData);
    populateSurveyQuestions(questions);
}

/* Edit survey stuff */

/**
 * Disables the "Edit Survey" button and attempts to submit updates to an existing survey.
 *
 * @param {string} apiUrl - The base URL of the API.
 * @returns {Promise<string>} The ID of the updated survey.
 * @throws Will throw an error if the survey submission fails.
 */
async function editSurvey(apiUrl) {
    const surveyForm = document.getElementById('surveyForm');
    const editSurveyButton = document.getElementById('editSurveyButton');
    const surveyId = surveyForm.dataset.surveyId;

    editSurveyButton.disabled = true;

    try {
        await submitSurvey(apiUrl, surveyId);
    } catch (error) {
        editSurveyButton.disabled = false;
        throw error;
    }

    return surveyId;
}

/**
 * Populates the survey title field with the given survey data.
 *
 * @param {Object|null} surveyData - The data of the survey to populate.
 * Expected to include a 'name'.
 */
function populateSurveyTitle(surveyData) {
    const surveyTitleField = document.getElementById('surveyTitle');
    surveyTitleField.value = surveyData ? surveyData.name ?? '' : '';
}

/**
 * Sets common attributes for a question container based on the given question data.
 *
 * @param {HTMLElement} questionContainer - The container element where the question attributes
 * should be set.
 * @param {Object} question - An object containing the data for the question,
 * expected to include 'question_number', 'id', and 'question'.
 */
function setCommonQuestionAttributes(questionContainer, question) {
    questionContainer.querySelector('div').dataset.questionNumber = question['question_number'];
    questionContainer.querySelector('div').dataset.questionId = question['id'];
    questionContainer.querySelector('.question-title').value = question['question'];
}

/**
 * Populates a multiple-choice question container using the provided question data.
 *
 * @param {Object} question - An object containing data for the multiple-choice question.
 * Expected to include 'question_number', 'id', 'question', and 'choices'.
 * @returns {DocumentFragment} A populated multiple-choice question container as a document
 * fragment.
 */
function populateMultipleChoiceQuestion(question) {
    const questionTemplate = document.getElementById('multipleChoiceQuestionTemplate');
    const questionContainer = questionTemplate.content.cloneNode(true);

    setCommonQuestionAttributes(questionContainer, question);

    const answersContainer = questionContainer.querySelector('.answers-container');
    const answerTemplate = document.getElementById('answerTemplate');

    // Loop through each answer choice and add it to the answers container
    for (const answer of question['choices']) {
        const answerContainer = answerTemplate.content.cloneNode(true);

        answerContainer.querySelector('div').dataset.answerNumber = answer['position'];
        answerContainer.querySelector('div').dataset.answerId = answer['id'];
        answerContainer.querySelector('input').value = answer['answer'].trim();

        answersContainer.appendChild(answerContainer);
    }

    return questionContainer;
}

/**
 * Populates a free-text question container using the provided question data.
 *
 * @param {Object} question - An object containing data for the free-text question.
 * Expected to include 'question_number', 'id', and 'question'.
 * @returns {DocumentFragment} A populated free-text question container as a document fragment.
 */
function populateFreeTextQuestion(question) {
    const questionTemplate = document.getElementById('freeTextQuestionTemplate');
    const questionContainer = questionTemplate.content.cloneNode(true);

    setCommonQuestionAttributes(questionContainer, question);

    return questionContainer;
}

/**
 * Populates the survey with the provided list of questions.
 * Determines the appropriate type (multiple-choice or free-text) and fills the survey form.
 *
 * @param {Array<Object>} questions - An array of question objects to populate.
 * Each object is expected to contain 'type', 'question_number', 'id', and 'question'.
 */
function populateSurveyQuestions(questions) {
    const questionsContainer = document.getElementById('questionsContainer');

    // Loop through each question and determine its type to populate accordingly
    for (const question of questions) {
        let questionContainer = null;

        if (question['type'] === 'multiple_choice') {
            questionContainer = populateMultipleChoiceQuestion(question);
        } else if (question['type'] === 'free_text') {
            questionContainer = populateFreeTextQuestion(question);
        }

        if (questionContainer !== null) {
            questionsContainer.appendChild(questionContainer);
        }
    }
}

/* End edit survey stuff */

/**
 * Adds a new question to the survey using a specified template.
 * The new question is assigned an incremented number based on the existing count.
 *
 * @param {string} templateName - The ID of the template to use for the new question.
 */
function newQuestionButton(templateName) {
    const questionsContainer = document.getElementById('questionsContainer');

    const existingQuestions = questionsContainer.querySelectorAll('.question-container');
    const newQuestionNumber = existingQuestions.length + 1;

    const template = document.getElementById(templateName);
    const newQuestion = template.content.cloneNode(true);

    newQuestion.querySelector('div').dataset.questionNumber = newQuestionNumber;

    questionsContainer.appendChild(newQuestion);
}

/**
 * Handles form submission for creating or editing a survey.
 *
 * @param {Event} event - The form submission event.
 * @param {string} apiUrl - The base URL of the API.
 */
async function handleFormSubmission(event, rootUrl, apiUrl) {
    const surveyForm = document.getElementById('surveyForm');
    const createSurveyButton = document.getElementById('createSurveyButton');
    const editSurveyButton = document.getElementById('editSurveyButton');

    event.preventDefault();

    // Check form validity and display validation feedback if invalid
    const formValidity = surveyForm.checkValidity();
    if (!formValidity) {
        event.stopPropagation();
        surveyForm.classList.add('was-validated');
        return;
    }

    surveyForm.classList.add('was-validated');

    let surveyId;
    try {
        // Determine whether to create or edit the survey based on button availability
        if (createSurveyButton !== null) {
            surveyId = await createSurvey(apiUrl);
        } else if (editSurveyButton !== null) {
            surveyId = await editSurvey(apiUrl);
        }
    } catch (error) {
        appendAlert('Something went wrong! Please try again later.', 'danger');
        console.error(error);
        return;
    }

    window.location.href = `${rootUrl}/surveys/${surveyId}/manage`;
}

/**
 * Updates the question numbers for all questions in the survey.
 * Resets the question numbers based on their order in the DOM.
 */
function updateQuestionNumbers() {
    const questionsContainer = document.getElementById('questionsContainer');
    const existingQuestions = questionsContainer.querySelectorAll('.question-container');

    let newQuestionNumber = 0;

    // Loop through each question container and update its question number
    existingQuestions.forEach((question) => {
        question.dataset.questionNumber = ++newQuestionNumber;
    });
}

/**
 * Deletes a question from the survey.
 * Removes the closest question container to the target element and updates question numbers.
 *
 * @param {Event} event - The event object representing the triggering of the delete action.
 */
function deleteQuestion(event) {
    closestQuestion = event.target.closest('.question-container');
    closestQuestion.remove();

    updateQuestionNumbers();
}

/**
 * Adds a new answer to a question in the survey.
 * Retrieves the closest question container to the target element and appends a new answer
 * container.
 *
 * @param {Event} event - The event object representing the triggering of the add answer action.
 */
function addAnswer(event) {
    const closestQuestion = event.target.closest('.question-container');
    const answersContainer = closestQuestion.querySelector('.answers-container');
    const existingAnswers = answersContainer.querySelectorAll('.answer-container');

    const newAnswerNumber = existingAnswers.length + 1;

    const template = document.getElementById('answerTemplate');
    const newAnswer = template.content.cloneNode(true);

    newAnswer.querySelector('div').dataset.answerNumber = newAnswerNumber;
    newAnswer.querySelector('input').placeholder = `Answer ${newAnswerNumber}`;

    answersContainer.appendChild(newAnswer);
}

/**
 * Updates the answer numbers for all answers in a question.
 * Resets the answer numbers based on their order in the DOM.
 *
 * @param {HTMLElement} closestQuestion - The closest question container to the target element.
 */
function updateAnswerNumbers(closestQuestion) {
    const answersContainer = closestQuestion.querySelector('.answers-container');
    const existingAnswers = answersContainer.querySelectorAll('.answer-container');
    let newAnswerNumber = 0;

    // Loop through each answer container and update its answer number and placeholder
    existingAnswers.forEach((answer) => {
        answer.dataset.answerNumber = ++newAnswerNumber;
        answer.querySelector('input').placeholder = `Answer ${newAnswerNumber}`;
    });
}

/**
 * Deletes an answer from a question in the survey.
 * Removes the closest answer container to the target element and updates answer numbers.
 *
 * @param {Event} event - The event object representing the triggering of the delete action.
 */
function deleteAnswer(event) {
    const closestAnswer = event.target.closest('.answer-container');
    const closestQuestion = event.target.closest('.question-container');
    closestAnswer.remove();

    updateAnswerNumbers(closestQuestion);
}

/**
 * Sets up the event listeners for the survey page, including click and submit
 */
function setupEventListeners(rootUrl, apiUrl) {
    const addMultipleChoiceQuestionButton = document.getElementById(
        'addMultipleChoiceQuestionButton',
    );
    const addFreeTextQuestionButton = document.getElementById('addFreeTextQuestionButton');

    addMultipleChoiceQuestionButton.addEventListener('click', () =>
        newQuestionButton('multipleChoiceQuestionTemplate'),
    );
    addFreeTextQuestionButton.addEventListener('click', () =>
        newQuestionButton('freeTextQuestionTemplate'),
    );

    document.addEventListener('submit', (event) => handleFormSubmission(event, rootUrl, apiUrl));

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('delete-question-button')) {
            deleteQuestion(event);
        } else if (event.target.classList.contains('add-answer-button')) {
            addAnswer(event);
        } else if (event.target.classList.contains('delete-answer-button')) {
            deleteAnswer(event);
        }
    });
}

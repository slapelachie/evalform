/**
 * Retrieves the selected multiple-choice answer ID from the given question container.
 *
 * @param {HTMLElement} questionContainer - The container element of the multiple-choice question.
 * @returns {string|null} The ID of the selected answer, or null if no answer is selected.
 */
function getMultipleChoiceAnswer(questionContainer) {
    const answerContainers = questionContainer.querySelectorAll('.answer-container');

    for (const answerContainer of answerContainers) {
        const radioElement = answerContainer.querySelector('.answer-choice');
        if (radioElement.checked) {
            return answerContainer.dataset.answerId;
        }
    }
    return null;
}

/**
 * Retrieves the free-text answer from the given question container.
 *
 * @param {HTMLElement} questionContainer - The container element of the free-text question.
 * @returns {string} The free-text answer.
 */
function getFreeTextAnswer(questionContainer) {
    const textArea = questionContainer.querySelector('textarea');
    return textArea.value;
}

/**
 * Retrieves responses to all questions from the survey form.
 *
 * @param {HTMLElement} surveyForm - The form element containing the survey questions.
 * @returns {Array<Object>} Array of objects representing question responses.
 */
function getQuestionResponses(surveyForm) {
    const questionContainers = surveyForm.querySelectorAll('.question-container');

    let questions = [];
    questionContainers.forEach((questionContainer) => {
        const questionId = questionContainer.dataset.questionId;
        const questionType = questionContainer.dataset.questionType;

        if (questionType == 'multiple_choice') {
            var answer = getMultipleChoiceAnswer(questionContainer);
        } else {
            var answer = getFreeTextAnswer(questionContainer);
        }

        questions.push({
            question_id: questionId,
            answer: answer,
        });
    });

    return questions;
}

/**
 * Retrieves the survey response data from the survey form.
 *
 * @returns {Object} An object containing the survey ID and its responses.
 */
function getSurveyResponseData() {
    const surveyForm = document.getElementById('surveyForm');
    const surveyId = surveyForm.dataset.surveyId;

    return {
        survey_id: surveyId,
        responses: getQuestionResponses(surveyForm),
    };
}

/*
 * Submits the survey responses to the API endpoint for processing.
 *
 * @param {string} rootUrl - The root URL of the application.
 */
async function submitSurvey(rootUrl) {
    const apiUrl = `${rootUrl}/api`;

    const submitSurveyButton = document.getElementById('submitSurveyButton');
    submitSurveyButton.disabled = true;

    const surveyResponseData = getSurveyResponseData();
    console.log(surveyResponseData);

    try {
        await makePostAPICall(`${apiUrl}/responses`, surveyResponseData);
    } catch (error) {
        appendAlert('Something went wrong! Please try again later.', 'danger');
        console.error(error);
        submitSurveyButton.disabled = false;
        return;
    }

    window.location.href = `${rootUrl}/surveys/thank-you`;
}

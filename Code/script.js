let currentQuestion = 1;
const totalQuestions = 10;
const scores = {
    IE: 0,  // Information Enthusiast
    DN: 0,  // Digital Native
    SO: 0,  // Skeptical Observer
    IP: 0,  // Information Paradox
    date: '' // Selected date
};

// Function to show next question
function showNextQuestion() {
    // Hide all questions first
    for (let i = 1; i <= totalQuestions; i++) {
        document.getElementById(`question${ i}`).style.display = 'none';
    }
    
    // Show current question if we're not done
    if (currentQuestion <= totalQuestions) {
        document.getElementById(`question${ currentQuestion}`).style.display = 'block';
    }
}

function updateScores(questionNumber, answer) {
    switch(questionNumber) {
        case 1:
            // Date question
            scores.date = answer;
            break;
        case 2:
            // Secret question
            if (answer === 'insist') scores.IE += 1;
            else if (answer === 'hear_secret') scores.DN += 1;
            else if (answer === 'dont_tell') scores.SO += 1;
            break;
        case 3:
            // Sleep question
            if (answer === 'great_sleep') scores.IE += 1;
            else if (answer === 'bad_sleep') scores.DN += 1;
            else if (answer === 'terrible_sleep') scores.SO += 1;
            break;
        case 4:
            // TV question
            if (answer === 'news') scores.IE += 2;
            else if (answer === 'sitcoms') scores.DN += 1;
            else if (answer === 'no_cable') {
                scores.SO += 1;
                scores.DN += 1;
            }
            break;
        case 5:
            // Dreams question
            if (answer === 'creepy') scores.SO += 2;
            else if (answer === 'cant_track') scores.DN += 1;
            else if (answer === 'dream_journal') scores.IE += 2;
            break;
        case 6:
            // Dinner question
            if (answer === 'taken') scores.IE += 1;
            else if (answer === 'yes_pay') scores.DN += 2;
            else if (answer === 'dont_know') scores.SO += 2;
            break;
        case 7:
            // Decision making
            if (answer === 'coin_flip') {
                scores.IE += 1;
                scores.DN += 1;
            }
            else if (answer === 'no_decisions') scores.DN += 2;
            else if (answer === 'yolo') {
                scores.SO += 1;
                scores.IE += 1;
            }
            break;
        case 8:
            // Ex in grocery store
            if (answer === 'cry') scores.IE += 2;
            else if (answer === 'run') {
                scores.DN += 1;
                scores.SO += 1;
            }
            else if (answer === 'sweater') scores.SO += 2;
            break;
        case 9:
            // Message sharing
            if (answer === 'pigeon') scores.IE += 2;
            else if (answer === 'dm') scores.DN += 2;
            else if (answer === 'no_talk') scores.SO += 2;
            break;
        case 10:
            // Fate question
            if (answer === 'tickets') scores.DN += 1;
            else if (answer === 'peer_pressure') scores.DN += 2;
            else if (answer === 'theatre_fan') scores.IE += 2;
            break;
    }
}

function calculateInformationParadox() {
    // Check if any scores are within 2 points of each other
    const paradox = (
        Math.abs(scores.IE - scores.DN) <= 2 ||
        Math.abs(scores.IE - scores.SO) <= 2 ||
        Math.abs(scores.DN - scores.SO) <= 2
    );
    
    scores.IP = paradox ? 1 : 0;
}

function showFinalDialogue() {
    document.getElementById('quizContent').innerHTML = `
        <div class="terminal-box">
            <div id="dialogueText">
                <div class="dialogue-line">Z: I think that's enough information for now.</div>
                <div class="dialogue-line">Z: Know that your answers may have consequences...</div>
            </div>
            <div class="dialogue-line">
                <button onclick="window.location.href='https://www.queensu.ca/theisabel/whats-on/love-and-information-caryl-churchill'" class="terminal-btn">Purchase Tickets</button>
            </div>
        </div>
    `;
}

function submitToDatabase() {
    calculateInformationParadox();
    
    fetch('save_choice.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(scores)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showFinalDialogue();
        } else {
            console.error('Submission error:', data.error);
            alert('There was an error submitting your answers. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error submitting your answers. Please try again.');
    });
}

// Handle form submissions
for (let i = 1; i <= totalQuestions; i++) {
    document.getElementById(`quizForm${ i}`).addEventListener('submit', function(e) {
        e.preventDefault();

        let answer;
        if (i === 1) {
            // Modified date handling for select element
            answer = document.getElementById('playDate').value;
            if (!answer) {
                alert('Please select a date before continuing.');
                return;
            }
        } else {
            const selectedOption = document.querySelector(`input[name="question${ i}"]:checked`);
            if (selectedOption) {
                answer = selectedOption.value;
            }
        }

        if (answer) {
            updateScores(i, answer);

            if (i === totalQuestions) {
                submitToDatabase();
            } else {
                currentQuestion++;
                showNextQuestion();
            }
        } else {
            alert('Please select an answer before continuing.');
        }
    });
}

// Initialize first question
document.getElementById('question1').style.display = 'block';
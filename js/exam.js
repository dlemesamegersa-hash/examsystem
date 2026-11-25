const EXAM_DURATION_SECONDS = 30 * 60;
const ATTEMPT_STORAGE_KEY = "examsphere:exam:computer-security:CS001:attempt:v1";
const RESULT_STORAGE_KEY = "examsphere:exam:computer-security:CS001:result:v1";
const questions = [
  {
    question: "What is computer security?",
    options: [
      "Protection of computer systems and information",
      "Creating computer games",
      "Designing websites",
      "Installing applications"
    ],
    answer: 0
  },
  {
    question: "Which security principle ensures information is available when needed?",
    options: [
      "Confidentiality",
      "Integrity",
      "Availability",
      "Authentication"
    ],
    answer: 2
  },
  {
    question: "What does encryption do?",
    options: [
      "Deletes information",
      "Converts readable data into an encoded form",
      "Prints information",
      "Copies files"
    ],
    answer: 1
  },
  {
    question: "Which of the following is a strong authentication method?",
    options: [
      "Using no password",
      "Sharing passwords",
      "Multi-factor authentication",
      "Using the same password everywhere"
    ],
    answer: 2
  },
  {
    question: "What is malware?",
    options: [
      "A type of computer hardware",
      "Malicious software",
      "A programming language",
      "A network cable"
    ],
    answer: 1
  }
];

const examApp = document.getElementById("examApp");
const questionTitle = document.getElementById("questionTitle");
const questionText = document.getElementById("questionText");
const optionsContainer = document.getElementById("optionsContainer");
const navigation = document.getElementById("questionNavigation");
const timerElement = document.getElementById("timer");
const previousButton = document.getElementById("previousBtn");
const nextButton = document.getElementById("nextBtn");
const submitButton = document.getElementById("submitBtn");

let currentQuestion = 0;
let intervalId = null;
let submissionPending = false;
let attempt = loadAttempt();
let submitted = attempt.status === "submitted";

function createAttempt() {
  const startedAt = Date.now();
  return {
    startedAt: startedAt,
    deadline: startedAt + EXAM_DURATION_SECONDS * 1000,
    status: "in-progress",
    answers: {}
  };
}

function isValidAttempt(value) {
  return value !== null
    && typeof value === "object"
    && !Array.isArray(value)
    && Number.isFinite(value.startedAt)
    && Number.isFinite(value.deadline)
    && (value.status === "in-progress" || value.status === "submitted")
    && value.answers !== null
    && typeof value.answers === "object"
    && !Array.isArray(value.answers);
}

function loadAttempt() {
  const saved = localStorage.getItem(ATTEMPT_STORAGE_KEY);
  if (saved === null) {
    const newAttempt = createAttempt();
    localStorage.setItem(ATTEMPT_STORAGE_KEY, JSON.stringify(newAttempt));
    return newAttempt;
  }

  let parsed;
  try {
    parsed = JSON.parse(saved);
  } catch (error) {
    if (!(error instanceof SyntaxError)) {
      throw error;
    }

    const newAttempt = createAttempt();
    localStorage.setItem(ATTEMPT_STORAGE_KEY, JSON.stringify(newAttempt));
    Swal.fire({
      title: "Saved Progress Could Not Be Read",
      text: "A new demonstration attempt has started because the saved browser data was invalid.",
      icon: "warning",
      confirmButtonColor: "#123d70"
    });
    return newAttempt;
  }

  if (!isValidAttempt(parsed)) {
    const newAttempt = createAttempt();
    localStorage.setItem(ATTEMPT_STORAGE_KEY, JSON.stringify(newAttempt));
    Swal.fire({
      title: "Saved Progress Is Invalid",
      text: "A new demonstration attempt has started because the saved browser data was invalid.",
      icon: "warning",
      confirmButtonColor: "#123d70"
    });
    return newAttempt;
  }

  const validAnswers = {};
  Object.keys(parsed.answers).forEach(function (questionIndex) {
    const questionNumber = Number(questionIndex);
    const answerIndex = parsed.answers[questionIndex];
    if (
      Number.isInteger(questionNumber)
      && questionNumber >= 0
      && questionNumber < questions.length
      && Number.isInteger(answerIndex)
      && answerIndex >= 0
      && answerIndex < questions[questionNumber].options.length
    ) {
      validAnswers[questionNumber] = answerIndex;
    }
  });
  parsed.answers = validAnswers;
  return parsed;
}

function saveAttempt() {
  localStorage.setItem(ATTEMPT_STORAGE_KEY, JSON.stringify(attempt));
}

function displayQuestion() {
  const question = questions[currentQuestion];
  questionTitle.textContent = "Question " + (currentQuestion + 1) + " of " + questions.length;
  questionText.textContent = question.question;
  document.getElementById("questionProgress").textContent =
    (currentQuestion + 1) + " of " + questions.length;
  optionsContainer.replaceChildren();

  question.options.forEach(function (option, index) {
    const label = document.createElement("label");
    label.className = "option";
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.name = "answer";
    radio.value = String(index);
    radio.checked = attempt.answers[currentQuestion] === index;
    radio.addEventListener("change", function () {
      if (submitted || Date.now() >= attempt.deadline) {
        updateTimer();
        return;
      }

      attempt.answers[currentQuestion] = index;
      saveAttempt();
      updateNavigation();
    });
    label.append(radio, document.createTextNode(option));
    optionsContainer.appendChild(label);
  });

  previousButton.disabled = currentQuestion === 0;
  nextButton.disabled = currentQuestion === questions.length - 1;
  updateNavigation();
}

function updateNavigation() {
  navigation.replaceChildren();

  questions.forEach(function (_, index) {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = String(index + 1);
    button.className = "question-number";
    button.setAttribute("aria-label", "Go to question " + (index + 1));

    if (attempt.answers[index] !== undefined) {
      button.classList.add("answered");
      button.setAttribute("aria-label", "Go to question " + (index + 1) + ", answered");
    }

    if (index === currentQuestion) {
      button.classList.add("current");
      button.setAttribute("aria-current", "step");
    }

    button.addEventListener("click", function () {
      currentQuestion = index;
      displayQuestion();
    });
    navigation.appendChild(button);
  });

  document.getElementById("answeredCount").textContent =
    "Answered: " + Object.keys(attempt.answers).length + " / " + questions.length;
}

function updateTimer() {
  const remainingSeconds = Math.max(0, Math.ceil((attempt.deadline - Date.now()) / 1000));
  const minutes = Math.floor(remainingSeconds / 60);
  const seconds = remainingSeconds % 60;
  timerElement.textContent =
    String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0");
  timerElement.classList.toggle("is-low", remainingSeconds <= 5 * 60);

  if (remainingSeconds <= 0) {
    submitExam(true);
  }
}

function calculateResult() {
  const answeredCount = Object.keys(attempt.answers).length;
  const correctCount = questions.filter(function (question, index) {
    return attempt.answers[index] === question.answer;
  }).length;
  return {
    total: questions.length,
    correct: correctCount,
    wrong: answeredCount - correctCount,
    skipped: questions.length - answeredCount,
    marks: correctCount * 20
  };
}

function finishSubmission(automatic) {
  if (submitted) {
    return;
  }

  submitted = true;
  attempt.status = "submitted";
  attempt.submittedAt = Date.now();
  attempt.automatic = automatic;
  clearInterval(intervalId);
  submissionPending = false;
  Swal.close();

  const score = calculateResult();
  const totalMarks = questions.length * 20;
  const result = {
    studentName: "Student Name",
    rollNumber: "CS001",
    university: "Sample University",
    department: "Computer Science",
    examName: "Computer Security",
    totalQuestions: questions.length,
    correctAnswers: score.correct,
    wrongAnswers: score.wrong,
    skippedQuestions: score.skipped,
    totalMarks: totalMarks,
    obtainedMarks: score.marks,
    percentage: totalMarks > 0 ? (score.marks / totalMarks) * 100 : 0,
    submissionType: automatic ? "Automatic" : "Manual",
    examDate: new Date().toLocaleDateString(),
    submittedAt: attempt.submittedAt
  };

  localStorage.setItem(RESULT_STORAGE_KEY, JSON.stringify(result));
  saveAttempt();
  window.location.href = "result.html";
}

function submitExam(automatic) {
  if (submitted) {
    return;
  }

  if (automatic) {
    finishSubmission(true);
    return;
  }

  if (submissionPending) {
    return;
  }

  submissionPending = true;
  Swal.fire({
    title: "Submit Examination?",
    text: "Are you sure you want to submit your answers?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Submit",
    confirmButtonColor: "#123d70"
  }).then(function (result) {
    submissionPending = false;
    if (result.isConfirmed) {
      finishSubmission(false);
    }
  });
}

previousButton.addEventListener("click", function () {
  if (currentQuestion > 0) {
    currentQuestion--;
    displayQuestion();
  }
});

nextButton.addEventListener("click", function () {
  if (currentQuestion < questions.length - 1) {
    currentQuestion++;
    displayQuestion();
  }
});

submitButton.addEventListener("click", function () {
  submitExam(false);
});

if (submitted) {
  window.location.replace("result.html");
} else {
  displayQuestion();
  updateTimer();
  if (!submitted) {
    intervalId = window.setInterval(updateTimer, 1000);
  }
}

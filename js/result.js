const RESULT_STORAGE_KEY = "examsphere:exam:computer-security:CS001:result:v1";
const resultContent = document.getElementById("resultContent");
const noResult = document.getElementById("noResult");
const studentPhoto = document.getElementById("studentPhoto");
const photoPlaceholder = document.getElementById("photoPlaceholder");
const photoNote = document.getElementById("photoNote");
const downloadButton = document.getElementById("downloadBtn");

function isValidResult(result) {
  return result !== null
    && typeof result === "object"
    && !Array.isArray(result)
    && typeof result.studentName === "string"
    && typeof result.rollNumber === "string"
    && typeof result.university === "string"
    && typeof result.department === "string"
    && typeof result.examName === "string"
    && typeof result.examDate === "string"
    && (result.submissionType === "Automatic" || result.submissionType === "Manual")
    && Number.isInteger(result.totalQuestions)
    && result.totalQuestions > 0
    && Number.isInteger(result.correctAnswers)
    && Number.isInteger(result.wrongAnswers)
    && Number.isInteger(result.skippedQuestions)
    && result.correctAnswers >= 0
    && result.wrongAnswers >= 0
    && result.skippedQuestions >= 0
    && result.correctAnswers + result.wrongAnswers + result.skippedQuestions === result.totalQuestions
    && Number.isFinite(result.totalMarks)
    && result.totalMarks > 0
    && Number.isFinite(result.obtainedMarks)
    && result.obtainedMarks >= 0
    && result.obtainedMarks <= result.totalMarks
    && Number.isFinite(result.percentage)
    && result.percentage >= 0
    && result.percentage <= 100
    && Number.isFinite(result.submittedAt);
}

function showMissingResult(message) {
  resultContent.hidden = true;
  noResult.hidden = false;
  if (message) {
    Swal.fire({
      title: "Result Unavailable",
      text: message,
      icon: "warning",
      confirmButtonColor: "#123d70"
    });
  }
}

function setPhotoAvailability() {
  if (!studentPhoto || !photoPlaceholder || !photoNote) {
    return;
  }
  const photoIsAvailable = studentPhoto.complete && studentPhoto.naturalWidth > 0;
  studentPhoto.hidden = !photoIsAvailable;
  photoPlaceholder.hidden = photoIsAvailable;
  photoNote.hidden = photoIsAvailable;
}

function displayResult(result) {
  document.getElementById("studentName").textContent = result.studentName;
  document.getElementById("rollNumber").textContent = result.rollNumber;
  document.getElementById("university").textContent = result.university;
  document.getElementById("department").textContent = result.department;
  document.getElementById("examName").textContent = result.examName;
  document.getElementById("examDate").textContent = result.examDate;
  document.getElementById("totalQuestions").textContent = String(result.totalQuestions);
  document.getElementById("correctAnswers").textContent = String(result.correctAnswers);
  document.getElementById("wrongAnswers").textContent = String(result.wrongAnswers);
  document.getElementById("skippedQuestions").textContent = String(result.skippedQuestions);
  document.getElementById("totalMarks").textContent = String(result.totalMarks);
  document.getElementById("submissionType").textContent = result.submissionType;
  document.getElementById("obtainedMarks").textContent = String(result.obtainedMarks);
  document.getElementById("maximumMarks").textContent = String(result.totalMarks);
  document.getElementById("percentage").textContent = result.percentage.toFixed(2) + "%";
  resultContent.hidden = false;
}

function addPdfText(pdf, text, x, y, maxWidth) {
  const lines = pdf.splitTextToSize(text, maxWidth);
  pdf.text(lines, x, y);
  return y + lines.length * 7;
}

function generatePDF(result) {
  if (!window.jspdf || typeof window.jspdf.jsPDF !== "function") {
    Swal.fire({
      title: "PDF Library Unavailable",
      text: "The PDF library could not be loaded. Check your internet connection and try again.",
      icon: "error",
      confirmButtonColor: "#123d70"
    });
    return false;
  }

  const pdf = new window.jspdf.jsPDF();
  const availablePhoto = studentPhoto.complete && studentPhoto.naturalWidth > 0;

  pdf.setFillColor(16, 43, 80);
  pdf.roundedRect(18, 14, 15, 15, 3, 3, "F");
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(14);
  pdf.setFont("helvetica", "bold");
  pdf.text("N", 23.2, 24.5);

  pdf.setTextColor(16, 43, 80);
  pdf.setFontSize(12);
  pdf.text("National-level Ethiopian Education Platform", 39, 23);
  pdf.setFontSize(14);
  pdf.text("Examination Result Report", 20, 39);
  pdf.setDrawColor(205, 215, 228);
  pdf.line(20, 45, 190, 45);

  if (availablePhoto) {
    const canvas = document.createElement("canvas");
    canvas.width = studentPhoto.naturalWidth;
    canvas.height = studentPhoto.naturalHeight;
    const context = canvas.getContext("2d");
    context.drawImage(studentPhoto, 0, 0);
    pdf.addImage(canvas.toDataURL("image/jpeg", .9), "JPEG", 158, 52, 30, 36);
  } else {
    pdf.setFillColor(220, 234, 250);
    pdf.roundedRect(158, 52, 30, 36, 2, 2, "F");
    pdf.setTextColor(49, 87, 127);
    pdf.setFontSize(17);
    pdf.text("CS", 168, 72);
    pdf.setFontSize(7);
    pdf.text("Photo not provided", 159, 93);
  }

  pdf.setTextColor(35, 52, 77);
  pdf.setFontSize(11);
  pdf.setFont("helvetica", "normal");
  let y = 55;
  [
    ["Student Name", result.studentName],
    ["Roll Number", result.rollNumber],
    ["University", result.university],
    ["Department", result.department],
    ["Examination", result.examName],
    ["Exam Date", result.examDate],
    ["Submission Type", result.submissionType]
  ].forEach(function (item) {
    y = addPdfText(pdf, item[0] + ": " + item[1], 20, y, 132);
  });

  y = Math.max(y + 9, 111);
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(16, 43, 80);
  pdf.setFontSize(14);
  pdf.text("Examination Performance", 20, y);
  y += 12;

  pdf.setFont("helvetica", "normal");
  pdf.setTextColor(35, 52, 77);
  pdf.setFontSize(11);
  [
    ["Total Questions", result.totalQuestions],
    ["Correct Answers", result.correctAnswers],
    ["Wrong Answers", result.wrongAnswers],
    ["Skipped Questions", result.skippedQuestions],
    ["Total Marks", result.totalMarks],
    ["Obtained Marks", result.obtainedMarks],
    ["Percentage", result.percentage.toFixed(2) + "%"]
  ].forEach(function (item) {
    y = addPdfText(pdf, item[0] + ": " + item[1], 20, y, 165);
  });

  y += 5;
  pdf.setDrawColor(205, 215, 228);
  pdf.line(20, y, 190, y);
  y += 12;
  pdf.setFont("helvetica", "bold");
  pdf.setTextColor(16, 43, 80);
  pdf.setFontSize(15);
  pdf.text("Final Result: " + result.obtainedMarks + " / " + result.totalMarks, 20, y);
  pdf.setFont("helvetica", "normal");
  pdf.setTextColor(100, 112, 128);
  pdf.setFontSize(9);
  pdf.text("Demonstration report. Official results require server verification.", 20, 280);

  const safeRollNumber = result.rollNumber.replace(/[^a-zA-Z0-9_-]/g, "_");
  pdf.save("National-level_Ethiopian_Education_Platform_Result_" + safeRollNumber + ".pdf");
  return true;
}

function notifyResultReady(photoIsAvailable, automaticPdf) {
  const message = photoIsAvailable
    ? (automaticPdf ? "Your result PDF is downloading." : "Your result and PDF are ready.")
    : "Your result is ready. Add images/student-photo.jpg to include your photo in the PDF.";
  Swal.fire({
    title: "Result Calculated",
    text: message,
    icon: "success",
    confirmButtonColor: "#123d70"
  });
}

let resultData = null;
const storedResult = localStorage.getItem(RESULT_STORAGE_KEY);

if (storedResult !== null) {
  try {
    resultData = JSON.parse(storedResult);
  } catch (error) {
    if (!(error instanceof SyntaxError)) {
      throw error;
    }
    showMissingResult("The saved result data is invalid. Complete the examination again to generate a new result.");
  }
}

if (resultData !== null && !isValidResult(resultData)) {
  showMissingResult("The saved result data is incomplete or invalid. Complete the examination again to generate a new result.");
  resultData = null;
}

if (resultData === null && noResult.hidden) {
  showMissingResult("Please complete an examination first.");
}

if (resultData !== null) {
  displayResult(resultData);
  studentPhoto.addEventListener("load", setPhotoAvailability);
  studentPhoto.addEventListener("error", setPhotoAvailability);
  if (studentPhoto.complete) {
    setPhotoAvailability();
  }

  downloadButton.addEventListener("click", function () {
    if (generatePDF(resultData)) {
      notifyResultReady(studentPhoto.complete && studentPhoto.naturalWidth > 0, false);
    }
  });

  const autoDownloadKey = "examsphere:result-pdf:CS001:" + resultData.submittedAt;
  if (sessionStorage.getItem(autoDownloadKey) === null) {
    sessionStorage.setItem(autoDownloadKey, "true");
    window.setTimeout(function () {
      if (generatePDF(resultData)) {
        notifyResultReady(studentPhoto.complete && studentPhoto.naturalWidth > 0, true);
      }
    }, 700);
  }
}

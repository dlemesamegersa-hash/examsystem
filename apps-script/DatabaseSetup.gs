const EXAMSPHERE_DATABASE_NAME = "ExamSphere_Database";
const EXAMSPHERE_DATABASE_ID_PROPERTY = "EXAMSPHERE_DATABASE_ID";

const EXAMSPHERE_SHEETS = [
  {
    name: "Universities",
    headers: ["UniversityID", "UniversityName", "Location", "Email", "Phone", "Status"]
  },
  {
    name: "Centers",
    headers: ["CenterID", "UniversityID", "CenterName", "Location", "Capacity", "Status"]
  },
  {
    name: "Users",
    headers: ["UserID", "FullName", "Username", "PasswordHash", "Role", "UniversityID", "CenterID", "Status"]
  },
  {
    name: "Students",
    headers: ["StudentID", "RollNumber", "FullName", "Gender", "DepartmentID", "UniversityID", "PhotoURL", "Status"]
  },
  {
    name: "Departments",
    headers: ["DepartmentID", "UniversityID", "DepartmentName", "Status"]
  },
  {
    name: "Courses",
    headers: ["CourseID", "DepartmentID", "CourseCode", "CourseName", "CreditHours"]
  },
  {
    name: "Examinations",
    headers: ["ExamID", "UniversityID", "CourseID", "ExamTitle", "ExamDate", "StartTime", "Duration", "TotalMarks", "Status"]
  },
  {
    name: "Questions",
    headers: ["QuestionID", "ExamID", "QuestionText", "Marks", "QuestionType"]
  },
  {
    name: "Options",
    headers: ["OptionID", "QuestionID", "OptionText", "IsCorrect"]
  },
  {
    name: "Assignments",
    headers: ["AssignmentID", "ExamID", "InvigilatorID", "CenterID", "Status"]
  },
  {
    name: "Attendance",
    headers: ["AttendanceID", "ExamID", "StudentID", "CenterID", "AttendanceStatus", "RecordedBy", "Date"]
  },
  {
    name: "ExamAttempts",
    headers: ["AttemptID", "ExamID", "StudentID", "StartTime", "EndTime", "Status", "Score"]
  },
  {
    name: "StudentAnswers",
    headers: ["AnswerID", "AttemptID", "QuestionID", "SelectedOptionID", "SubmittedAt"]
  },
  {
    name: "Results",
    headers: ["ResultID", "AttemptID", "StudentID", "ExamID", "CorrectCount", "WrongCount", "SkippedCount", "TotalMarks", "ObtainedMarks", "Percentage", "PDFURL"]
  },
  {
    name: "Incidents",
    headers: ["IncidentID", "ExamID", "InvigilatorID", "IncidentType", "Description", "ReportDate", "Status"]
  },
  {
    name: "ActivityLogs",
    headers: ["LogID", "UserID", "Action", "Description", "DateTime"]
  }
];

/**
 * Run this once from the Apps Script editor and authorize spreadsheet access.
 * The spreadsheet ID is stored in this script's properties so later runs reuse it.
 * Existing rows are preserved; conflicting headers cause an error instead of being overwritten.
 */
function initializeExamSphereDatabase() {
  const properties = PropertiesService.getScriptProperties();
  const savedId = properties.getProperty(EXAMSPHERE_DATABASE_ID_PROPERTY);
  let spreadsheet;

  if (savedId) {
    spreadsheet = SpreadsheetApp.openById(savedId);
  } else {
    spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    if (!spreadsheet) {
      spreadsheet = SpreadsheetApp.create(EXAMSPHERE_DATABASE_NAME);
    }
    properties.setProperty(EXAMSPHERE_DATABASE_ID_PROPERTY, spreadsheet.getId());
  }

  const firstSheet = spreadsheet.getSheets()[0];
  if (
    firstSheet
    && firstSheet.getName() === "Sheet1"
    && firstSheet.getLastRow() === 0
    && spreadsheet.getSheets().length === 1
  ) {
    firstSheet.setName(EXAMSPHERE_SHEETS[0].name);
  }

  EXAMSPHERE_SHEETS.forEach(function (definition) {
    let sheet = spreadsheet.getSheetByName(definition.name);
    if (!sheet) {
      sheet = spreadsheet.insertSheet(definition.name);
    }

    const headerRange = sheet.getRange(1, 1, 1, definition.headers.length);
    const existingHeaders = headerRange.getValues()[0];
    const sheetIsEmpty = sheet.getLastRow() === 0
      && existingHeaders.every(function (value) {
        return value === "" || value === null;
      });

    if (sheetIsEmpty) {
      headerRange.setValues([definition.headers]);
      headerRange
        .setBackground("#102b50")
        .setFontColor("#ffffff")
        .setFontWeight("bold");
      sheet.setFrozenRows(1);
      sheet.autoResizeColumns(1, definition.headers.length);
      return;
    }

    const headersMatch = definition.headers.every(function (header, index) {
      return existingHeaders[index] === header;
    });

    if (!headersMatch) {
      throw new Error(
        'The "' + definition.name + '" sheet already contains data or different headers. '
        + "No existing values were overwritten. Resolve the header conflict and run the initializer again."
      );
    }
  });

  const databaseUrl = spreadsheet.getUrl();
  Logger.log("National-level Ethiopian Education Platform database ready: " + databaseUrl);
  return databaseUrl;
}

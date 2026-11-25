const incidentForm = document.getElementById("incidentForm");
const sidebar = document.getElementById("sidebar");
const menuToggle = document.getElementById("menuToggle");

document.getElementById("currentYear").textContent = new Date().getFullYear();

document.getElementById("logoutBtn").addEventListener("click", function () {
  Swal.fire({
    title: "Sign Out?",
    text: "Are you sure you want to sign out?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, Sign Out",
    confirmButtonColor: "#123d70"
  }).then(function (result) {
    if (result.isConfirmed) {
      window.location.href = "index.html";
    }
  });
});

menuToggle.addEventListener("click", function () {
  const isOpen = sidebar.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", String(isOpen));
  menuToggle.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
});

document.querySelectorAll(".nav-link").forEach(function (link) {
  link.addEventListener("click", function () {
    document.querySelectorAll(".nav-link").forEach(function (item) {
      item.classList.remove("active");
    });
    this.classList.add("active");

    if (window.innerWidth <= 700) {
      sidebar.classList.remove("open");
      menuToggle.setAttribute("aria-expanded", "false");
      menuToggle.setAttribute("aria-label", "Open navigation");
    }
  });
});

document.querySelectorAll("[data-action]").forEach(function (button) {
  button.addEventListener("click", function () {
    const action = this.dataset.action;

    if (action === "start") {
      Swal.fire({
        title: "Start Examination Session?",
        text: "This is a demonstration action. Real session control requires a backend.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Continue",
        confirmButtonColor: "#123d70"
      });
      return;
    }

    const examRow = this.closest("tr");
    const examName = examRow
      ? examRow.cells[1].textContent
      : this.closest(".session-card").querySelector("h3").textContent;
    Swal.fire({
      title: "Examination Details",
      text: `${examName} uses demonstration data.`,
      icon: "info",
      confirmButtonColor: "#123d70"
    });
  });
});

document.getElementById("attendanceBtn").addEventListener("click", function () {
  Swal.fire({
    title: "Record Student Attendance",
    text: "Attendance recording will be connected to the database in the backend stage.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

incidentForm.addEventListener("submit", function (event) {
  event.preventDefault();

  const exam = document.getElementById("examName").value;
  const type = document.getElementById("incidentType").value;
  const description = document.getElementById("incidentDescription").value.trim();

  if (!exam || !type || !description) {
    Swal.fire({
      title: "Required Fields",
      text: "Please complete all fields.",
      icon: "warning",
      confirmButtonColor: "#123d70"
    });
    return;
  }

  Swal.fire({
    title: "Incident Report Submitted",
    text: "Your demonstration incident report has been received.",
    icon: "success",
    confirmButtonColor: "#123d70"
  }).then(function (result) {
    if (result.isConfirmed) {
      incidentForm.reset();
    }
  });
});

document.getElementById("reportBtn").addEventListener("click", function () {
  const table = document.getElementById("attendanceTable");
  const rows = Array.from(table.rows).map(function (row) {
    return Array.from(row.cells).map(function (cell) {
      return `"${cell.textContent.trim().replace(/"/g, '""')}"`;
    }).join(",");
  });
  const blob = new Blob([rows.join("\r\n")], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const downloadLink = document.createElement("a");

  downloadLink.href = url;
  downloadLink.download = "invigilator-attendance-report.csv";
  downloadLink.click();
  window.setTimeout(function () {
    URL.revokeObjectURL(url);
  }, 1000);
});

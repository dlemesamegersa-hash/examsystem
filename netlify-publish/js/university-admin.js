const universityChartCanvas = document.getElementById("universityChart");
const analyticsPeriod = document.getElementById("analyticsPeriod");
const sidebar = document.getElementById("sidebar");
const menuToggle = document.getElementById("menuToggle");

document.getElementById("currentYear").textContent = new Date().getFullYear();

const universityChart = new Chart(universityChartCanvas, {
  type: "line",
  data: {
    labels: ["June", "July", "August", "September", "October", "November"],
    datasets: [
      {
        label: "Student Registrations",
        data: [250, 320, 380, 410, 450, 520],
        borderColor: "#2360a5",
        backgroundColor: "rgba(35, 96, 165, .10)",
        fill: true,
        tension: .4
      },
      {
        label: "Examinations",
        data: [4, 6, 5, 8, 7, 10],
        borderColor: "#24a36a",
        backgroundColor: "rgba(36, 163, 106, .08)",
        fill: true,
        tension: .4
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom"
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

analyticsPeriod.addEventListener("change", function () {
  if (this.value === "yearly") {
    universityChart.data.labels = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    universityChart.data.datasets[0].data = [120, 150, 180, 210, 250, 270, 300, 340, 380, 410, 450, 520];
    universityChart.data.datasets[1].data = [2, 3, 3, 4, 4, 5, 5, 6, 7, 8, 9, 10];
  } else {
    universityChart.data.labels = ["June", "July", "August", "September", "October", "November"];
    universityChart.data.datasets[0].data = [250, 320, 380, 410, 450, 520];
    universityChart.data.datasets[1].data = [4, 6, 5, 8, 7, 10];
  }

  universityChart.update();
});

menuToggle.addEventListener("click", function () {
  const isOpen = sidebar.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", String(isOpen));
  menuToggle.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
});

document.querySelectorAll(".nav-link").forEach(function (link) {
  link.addEventListener("click", function (event) {
    const moduleName = this.dataset.module;
    if (moduleName) {
      event.preventDefault();
      Swal.fire({
        title: moduleName,
        text: "This module will be developed in a later step.",
        icon: "info",
        confirmButtonColor: "#123d70"
      });
      return;
    }

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

document.querySelectorAll(".quick-action").forEach(function (button) {
  button.addEventListener("click", function () {
    Swal.fire({
      title: this.dataset.action,
      text: "This university management module will be connected to the backend in a later step.",
      icon: "info",
      confirmButtonColor: "#123d70"
    });
  });
});

document.getElementById("notificationButton").addEventListener("click", function () {
  Swal.fire({
    title: "University Notifications",
    text: "You have 3 pending results in the demonstration data.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("reportButton").addEventListener("click", function () {
  const rows = [
    ["University Statistic", "Value"],
    ["Departments", document.getElementById("departmentCount").textContent],
    ["Courses", document.getElementById("courseCount").textContent],
    ["Students", document.getElementById("studentCount").textContent.replace(",", "")],
    ["Active Examinations", document.getElementById("examCount").textContent]
  ];
  const csv = rows.map(function (row) {
    return row.map(function (value) {
      return `"${String(value).replace(/"/g, '""')}"`;
    }).join(",");
  }).join("\r\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const downloadLink = document.createElement("a");

  downloadLink.href = url;
  downloadLink.download = "National-level-Ethiopian-Education-Platform-University-Report.csv";
  downloadLink.click();
  window.setTimeout(function () {
    URL.revokeObjectURL(url);
  }, 1000);
});

document.getElementById("allExamsButton").addEventListener("click", function () {
  Swal.fire({
    title: "University Examinations",
    text: "The full examination management page will be developed later.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("logoutButton").addEventListener("click", function () {
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

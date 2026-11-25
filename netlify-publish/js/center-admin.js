const centerChartCanvas = document.getElementById("centerChart");
const analyticsPeriod = document.getElementById("analyticsPeriod");
const sidebar = document.getElementById("sidebar");
const menuToggle = document.getElementById("menuToggle");

document.getElementById("currentYear").textContent = new Date().getFullYear();

const centerChart = new Chart(centerChartCanvas, {
  type: "line",
  data: {
    labels: ["June", "July", "August", "September", "October", "November"],
    datasets: [
      {
        label: "Present Candidates",
        data: [180, 220, 260, 280, 310, 350],
        borderColor: "#2360a5",
        backgroundColor: "rgba(35, 96, 165, .10)",
        fill: true,
        tension: .4
      },
      {
        label: "Absent Candidates",
        data: [15, 12, 18, 10, 8, 10],
        borderColor: "#d97706",
        fill: false,
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
    centerChart.data.labels = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    centerChart.data.datasets[0].data = [120, 150, 180, 200, 220, 240, 260, 280, 300, 320, 335, 350];
    centerChart.data.datasets[1].data = [20, 18, 17, 15, 14, 13, 12, 11, 10, 10, 9, 10];
  } else {
    centerChart.data.labels = ["June", "July", "August", "September", "October", "November"];
    centerChart.data.datasets[0].data = [180, 220, 260, 280, 310, 350];
    centerChart.data.datasets[1].data = [15, 12, 18, 10, 8, 10];
  }

  centerChart.update();
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
      text: "This center management module will be connected to the backend later.",
      icon: "info",
      confirmButtonColor: "#123d70"
    });
  });
});

document.getElementById("notificationButton").addEventListener("click", function () {
  Swal.fire({
    title: "Center Notifications",
    text: "You have pending center activities in the demonstration data.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("reportButton").addEventListener("click", function () {
  const rows = [
    ["Center Statistic", "Value"],
    ["Total Computers", document.getElementById("computerCount").textContent],
    ["Available Computers", document.getElementById("availableComputerCount").textContent],
    ["Today's Candidates", document.getElementById("candidateCount").textContent],
    ["Active Sessions", document.getElementById("sessionCount").textContent],
    ["Total Invigilators", "18"]
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
  downloadLink.download = "National-level-Ethiopian-Education-Platform-Center-Report.csv";
  downloadLink.click();
  window.setTimeout(function () {
    URL.revokeObjectURL(url);
  }, 1000);
});

document.getElementById("allExamsButton").addEventListener("click", function () {
  Swal.fire({
    title: "Examination Sessions",
    text: "The complete examination session management page will be developed later.",
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

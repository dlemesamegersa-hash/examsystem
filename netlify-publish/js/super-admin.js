const SUPABASE_URL = "https://vsblrzejgnfkdlrvlxtq.supabase.co";
const SUPABASE_PUBLISHABLE_KEY = "sb_publishable_lvlI-uQM4Kkjc5MhwSVwAw_sXE-Eo6U";

const supabaseClient = window.supabase.createClient(
  SUPABASE_URL,
  SUPABASE_PUBLISHABLE_KEY
);

async function verifySuperAdminSession() {
  try {
    const {
      data: { session },
      error: sessionError
    } = await supabaseClient.auth.getSession();

    if (sessionError) {
      throw sessionError;
    }

    if (!session) {
      window.location.replace("index.html");
      return;
    }

    const {
      data: { user },
      error: userError
    } = await supabaseClient.auth.getUser();

    if (userError || !user) {
      throw userError || new Error("Unable to verify authenticated user.");
    }

    console.log("Authenticated Supabase user:", user.id);

    if (user.id !== "f2f97e4b-1512-49fe-b585-911d8eb48c9e") {
      await Swal.fire({
        title: "Access Denied",
        text: "This dashboard is restricted to the Super Administrator.",
        icon: "error"
      });

      window.location.replace("index.html");
      return;
    }

    console.log("Super Admin Auth identity verified.");
  } catch (error) {
    console.error("Dashboard authentication error:", error);

    await Swal.fire({
      title: "Authentication Error",
      text: "Unable to verify your account. Please log in again.",
      icon: "error"
    });
  }
}

verifySuperAdminSession();

const chartCanvas = document.getElementById("platformChart");
const analyticsPeriod = document.getElementById("analyticsPeriod");
const sidebar = document.getElementById("sidebar");
const menuToggle = document.getElementById("menuToggle");

document.getElementById("currentYear").textContent = new Date().getFullYear();

const chart = new Chart(chartCanvas, {
  type: "line",
  data: {
    labels: ["June", "July", "August", "September", "October", "November"],
    datasets: [
      {
        label: "Universities",
        data: [10, 13, 15, 18, 21, 24],
        borderColor: "#2360a5",
        backgroundColor: "rgba(35, 96, 165, .10)",
        fill: true,
        tension: .4
      },
      {
        label: "Examinations",
        data: [5, 8, 7, 12, 10, 16],
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
    chart.data.labels = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    chart.data.datasets[0].data = [3, 5, 7, 8, 10, 12, 13, 15, 18, 21, 23, 24];
    chart.data.datasets[1].data = [2, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16];
  } else {
    chart.data.labels = ["June", "July", "August", "September", "October", "November"];
    chart.data.datasets[0].data = [10, 13, 15, 18, 21, 24];
    chart.data.datasets[1].data = [5, 8, 7, 12, 10, 16];
  }

  chart.update();
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
      text: "This module will be connected to the system backend in a later development step.",
      icon: "info",
      confirmButtonColor: "#123d70"
    });
  });
});

document.getElementById("notificationButton").addEventListener("click", function () {
  Swal.fire({
    title: "Notifications",
    text: "You have 5 pending approvals in the demonstration data.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("reportButton").addEventListener("click", function () {
  const rows = [
    ["Platform Statistic", "Value"],
    ["Total Universities", document.getElementById("universityCount").textContent],
    ["Examination Centers", document.getElementById("centerCount").textContent],
    ["Registered Students", document.getElementById("studentCount").textContent.replace(",", "")],
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
  downloadLink.download = "National-level-Ethiopian-Education-Platform-Report.csv";
  downloadLink.click();
  window.setTimeout(function () {
    URL.revokeObjectURL(url);
  }, 1000);
});

document.getElementById("allExamsButton").addEventListener("click", function () {
  Swal.fire({
    title: "Examinations",
    text: "The complete examination management page will be developed in a later step.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("activitiesButton").addEventListener("click", function () {
  Swal.fire({
    title: "Activity Logs",
    text: "The complete activity log page will be developed in a later step.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.getElementById("logoutButton").addEventListener("click", function () {
  Swal.fire({
    title: "Sign Out?",
    text: "Are you sure you want to leave the dashboard?",
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

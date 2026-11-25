const sidebar = document.getElementById("sidebar");
const menuButton = document.getElementById("menuBtn");

document.getElementById("currentYear").textContent = new Date().getFullYear();

menuButton.addEventListener("click", function () {
  const isOpen = sidebar.classList.toggle("open");
  menuButton.setAttribute("aria-expanded", String(isOpen));
  menuButton.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
});

document.querySelectorAll(".nav-link").forEach(function (link) {
  link.addEventListener("click", function () {
    document.querySelectorAll(".nav-link").forEach(function (item) {
      item.classList.remove("active");
    });
    this.classList.add("active");

    if (window.innerWidth <= 700) {
      sidebar.classList.remove("open");
      menuButton.setAttribute("aria-expanded", "false");
      menuButton.setAttribute("aria-label", "Open navigation");
    }
  });
});

document.getElementById("viewProfileBtn").addEventListener("click", function () {
  Swal.fire({
    title: "Student Profile",
    html: "<p><strong>Roll Number:</strong> CS001</p><p><strong>Department:</strong> Computer Science</p><p><strong>University:</strong> Sample University</p>",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

document.querySelectorAll(".start-exam-btn").forEach(function (button) {
  button.addEventListener("click", function () {
    Swal.fire({
      title: "Start Examination?",
      text: "Are you ready to begin your examination?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Continue",
      confirmButtonColor: "#123d70"
    }).then(function (result) {
      if (result.isConfirmed) {
        window.location.href = "exam.html";
      }
    });
  });
});

document.querySelectorAll(".result-btn").forEach(function (button) {
  button.addEventListener("click", function () {
    Swal.fire({
      title: "Sample Result Record",
      text: "This row is demonstration data. A downloadable report is generated for the Computer Security demonstration exam after you submit it.",
      icon: "info",
      confirmButtonColor: "#123d70"
    });
  });
});

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

const SUPABASE_URL = "https://vsblrzejgnfkdlrvlxtq.supabase.co";
const SUPABASE_PUBLISHABLE_KEY = "sb_publishable_lvlI-uQM4Kkjc5MhwSVwAw_sXE-Eo6U";

const supabaseClient = window.supabase.createClient(
  SUPABASE_URL,
  SUPABASE_PUBLISHABLE_KEY
);

const loginForm = document.getElementById("loginForm");
const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const role = document.getElementById("role");
const username = document.getElementById("username");
const forgotPassword = document.getElementById("forgotPassword");

document.getElementById("currentYear").textContent =
  new Date().getFullYear();

// Update username placeholder
role.addEventListener("change", function () {
  const isStudent = role.value === "student";

  username.placeholder = isStudent
    ? "Enter your student roll number"
    : "Enter your username";
});

// Show or hide password
togglePassword.addEventListener("click", function () {
  const showPassword = password.type === "password";

  password.type = showPassword ? "text" : "password";
  togglePassword.textContent = showPassword ? "Hide" : "Show";

  togglePassword.setAttribute(
    "aria-label",
    showPassword ? "Hide password" : "Show password"
  );

  togglePassword.setAttribute("aria-pressed", String(showPassword));
});

// Login using Supabase Edge Function
loginForm.addEventListener("submit", async function (event) {
  event.preventDefault();

  const enteredUsername = username.value.trim();
  const enteredPassword = password.value;
  const selectedRole = role.value;

  if (!enteredUsername || !enteredPassword || !selectedRole) {
    Swal.fire({
      title: "Missing Information",
      text: "Please complete all login fields.",
      icon: "warning",
      confirmButtonColor: "#123d70"
    });
    return;
  }

  const submitButton = loginForm.querySelector(
    'button[type="submit"]'
  );

  if (submitButton) {
    submitButton.disabled = true;
  }

  try {
    const { data, error } = await supabaseClient.functions.invoke(
      "username-login",
      {
        body: {
          username: enteredUsername,
          password: enteredPassword,
          role: selectedRole
        }
      }
    );

    if (error) {
      throw error;
    }

    if (!data || !data.success || !data.session) {
      throw new Error(data?.error || "Login failed.");
    }

    const { error: sessionError } =
      await supabaseClient.auth.setSession({
        access_token: data.session.access_token,
        refresh_token: data.session.refresh_token
      });

    if (sessionError) {
      throw sessionError;
    }

    await Swal.fire({
      title: "Login Successful",
      text: "Welcome to the National-level Ethiopian Education Platform!",
      icon: "success",
      confirmButtonColor: "#123d70"
    });

    const dashboards = {
      super_admin: "super-admin.html",
      university_admin: "university-admin.html",
      center_admin: "center-admin.html",
      invigilator: "invigilator.html",
      student: "student.html"
    };

    const dashboard = dashboards[data.role];

    if (!dashboard) {
      throw new Error("Dashboard not configured for this role.");
    }

    window.location.href = dashboard;

  } catch (error) {
    console.error("Login error:", error);

    let errorMessage = error.message || "Unable to connect.";

    if (error.context instanceof Response) {
      const response = error.context.clone();
      const responseBody = await response.text();

      console.error("HTTP Status:", response.status);
      console.error("Server Response:", responseBody);

      try {
        const result = JSON.parse(responseBody);
        errorMessage =
          result.error || result.message || responseBody;
      } catch {
        errorMessage = responseBody || errorMessage;
      }
    }

    Swal.fire({
      title: "Login Failed",
      text: errorMessage,
      icon: "error",
      confirmButtonColor: "#123d70"
    });

  } finally {
    if (submitButton) {
      submitButton.disabled = false;
    }
  }
});

// Password recovery
forgotPassword.addEventListener("click", function (event) {
  event.preventDefault();

  Swal.fire({
    title: "Password Recovery",
    text: "Please contact your system administrator for password recovery.",
    icon: "info",
    confirmButtonColor: "#123d70"
  });
});

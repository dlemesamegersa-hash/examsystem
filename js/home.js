const roleSelect = document.getElementById("role");

document.querySelectorAll("[data-role-shortcut]").forEach((shortcut) => {
  shortcut.addEventListener("click", () => {
    roleSelect.value = shortcut.dataset.roleShortcut;
    roleSelect.dispatchEvent(new Event("change", { bubbles: true }));
  });
});

const requestForm = document.getElementById("requestForm");

requestForm.addEventListener("submit", (event) => {
  event.preventDefault();

  if (!requestForm.reportValidity()) {
    return;
  }

  const office = document.getElementById("requestOffice").value;
  const recipient = document.getElementById("recipientEmail").value.trim();
  const message = document.getElementById("requestMessage").value.trim();
  const subject = `National-level Ethiopian Education Platform ${office} Request`;
  const body = `Hello ${office},\n\n${message}\n\nSent from the National-level Ethiopian Education Platform portal.`;

  window.location.href =
    `mailto:${recipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
});

const activationForm = document.getElementById("activationForm");

activationForm.addEventListener("submit", (event) => {
  event.preventDefault();

  if (!activationForm.reportValidity()) {
    return;
  }

  const name = document.getElementById("activationName").value.trim();
  const username = document.getElementById("activationUsername").value.trim();
  const role = document.getElementById("activationRole").value;
  const recipient = document.getElementById("activationEmail").value.trim();
  const subject = `National-level Ethiopian Education Platform Account Activation Request - ${name}`;
  const body =
    `Hello,\n\nI would like to request account activation.\n\nName: ${name}\nUsername / Roll Number: ${username}\nPortal role: ${role}`;

  window.location.href =
    `mailto:${recipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
});

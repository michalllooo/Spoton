const themeToggle = document.getElementById("themeToggle");
const fontSizeSelect = document.getElementById("fontSize");
const languageSelect = document.getElementById("language");
const accentColorSelect = document.getElementById("accentColor");
const notificationsToggle = document.getElementById("notificationsToggle");
const privacyToggle = document.getElementById("privacyToggle");
const resetButton = document.getElementById("resetButton");

// Load saved settings
document.addEventListener("DOMContentLoaded", () => {
  const darkTheme = localStorage.getItem("darkTheme") === "true";
  const largeFont = localStorage.getItem("largeFont") === "true";
  const language = localStorage.getItem("language") || "pl";
  const accentColor = localStorage.getItem("accentColor") || "blue";
  const notifications = localStorage.getItem("notifications") === "true";
  const privacy = localStorage.getItem("privacy") === "true";

  themeToggle.checked = darkTheme;
  fontSizeSelect.value = largeFont ? "large" : "normal";
  languageSelect.value = language;
  accentColorSelect.value = accentColor;
  notificationsToggle.checked = notifications;
  privacyToggle.checked = privacy;

  if (darkTheme) {
    document.body.classList.add("dark-theme");
  }
  if (largeFont) {
    document.body.classList.add("large-font");
  }
  document.body.style.setProperty("--accent-color", accentColor);
});

// Save settings
themeToggle.addEventListener("change", () => {
  document.body.classList.toggle("dark-theme", themeToggle.checked);
  localStorage.setItem("darkTheme", themeToggle.checked);
});

fontSizeSelect.addEventListener("change", () => {
  document.body.classList.toggle(
    "large-font",
    fontSizeSelect.value === "large"
  );
  localStorage.setItem("largeFont", fontSizeSelect.value === "large");
});

languageSelect.addEventListener("change", () => {
  localStorage.setItem("language", languageSelect.value);
  alert(`Język zmieniony na: ${languageSelect.value}`);
});

accentColorSelect.addEventListener("change", () => {
  const selectedColor = accentColorSelect.value;
  document.body.style.setProperty("--accent-color", selectedColor);
  localStorage.setItem("accentColor", selectedColor);
});

notificationsToggle.addEventListener("change", () => {
  localStorage.setItem("notifications", notificationsToggle.checked);
});

privacyToggle.addEventListener("change", () => {
  localStorage.setItem("privacy", privacyToggle.checked);
});

// Reset settings
resetButton.addEventListener("click", () => {
  localStorage.clear();
  location.reload();
});

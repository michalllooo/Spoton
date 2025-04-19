function togglePasswordVisibility() {
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.querySelector(".eye");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    eyeIcon.textContent = "🙈"; // closed eye emoji
  } else {
    passwordInput.type = "password";
    eyeIcon.textContent = "👀"; // open eye emoji
  }
}

document.addEventListener("DOMContentLoaded", function () {
  var flpPathInput = document.getElementById("flp_path");

  // Load the path from localStorage if it exists
  var savedPath = localStorage.getItem("flp_path");
  if (savedPath && !flpPathInput.value) {
    flpPathInput.value = savedPath;
  }

  // Save the path to localStorage when the form is submitted
  var flpForm = document.getElementById("flp-form");
  flpForm.addEventListener("submit", function () {
    localStorage.setItem("flp_path", flpPathInput.value);
  });
});

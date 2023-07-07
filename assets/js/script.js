const myTimeout = setTimeout(hideSuccess, 3000);

function hideSuccess() {
  document.getElementById("success").style.display = "none";
  myStopFunction();
}

function myStopFunction() {
  clearTimeout(myTimeout);
}
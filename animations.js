function menuChange(x) {
  x.classList.toggle("change");
}

function menuToggle() {
  if (document.getElementById("sidebar").classList.contains("open")) {
    document.getElementById("sidebar").style.left = "-200px";
    document.getElementById("main-text").style.marginLeft = "0px";

  } else {
    document.getElementById("sidebar").style.left = "0px";
    document.getElementById("main-text").style.marginLeft = "200px";

  }

  document.getElementById("sidebar").classList.toggle("open");
}

function spin() {
  document.getElementById('settings').classList.toggle('w3-spin');
}

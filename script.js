window.addEventListener("scroll", function () {
  let navbar = document.getElementById("navbar");

  if (window.scrollY > 300) {
    navbar.style.display = "flex";
  } else {
    navbar.style.display = "none";
  }
});

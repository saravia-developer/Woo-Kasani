document.addEventListener("DOMContentLoaded", function () {
  const x = document.querySelector(".close-flash-warning");
    
    x.addEventListener("click", function () {
      const flashWarningContainer = document.querySelector(
        ".flash-warning-container",
      );
      if (flashWarningContainer) {
        flashWarningContainer.style.display = "none";
        x.style.display = "none";
      } else {
        return;
      }
    });
});
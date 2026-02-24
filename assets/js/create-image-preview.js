// Image preview functionality for create.php
const fileInput = document.getElementById("gambar");
const imagePreview = document.getElementById("imagePreview");
if (fileInput && imagePreview) {
  fileInput.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px;">`;
      };
      reader.readAsDataURL(file);
    }
  });
}

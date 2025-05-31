document.getElementById('productImage').addEventListener('change', function (event) {
  const file = event.target.files[0];
  const preview = document.getElementById('imagePreview');

  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview" width="100">`;
    };
    reader.readAsDataURL(file);
  } else {
    preview.innerHTML = '';
  }
});

document.getElementById('productForm').addEventListener('submit', function (e) {
  e.preventDefault(); // Prevent the default form submission

  const formData = new FormData(this); // Create FormData object from the form

  fetch('product.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json()) // Expect JSON response
  .then(data => {
    const messageDiv = document.getElementById('message');
    if (data.success) {
      messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
      // Clear the form and preview
      document.getElementById('productForm').reset();
      document.getElementById('imagePreview').innerHTML = '';
      // Refresh the product list (optional)
      setTimeout(() => {
        window.location.reload();
      }, 1000); // Reload after 1 second
    } else {
      messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    document.getElementById('message').innerHTML = `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
  });
});
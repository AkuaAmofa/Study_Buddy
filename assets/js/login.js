document.getElementById('LogIn').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const errorElement = document.getElementById('errorMessage');

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'home.php';
        } else {
            if (errorElement) {
                errorElement.textContent = data.message || 'An error occurred';
            } else {
                alert(data.message || 'An error occurred');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorElement) {
            errorElement.textContent = 'An error occurred. Please try again.';
        } else {
            alert('An error occurred. Please try again.');
        }
    });
});

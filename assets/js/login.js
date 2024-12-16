document.getElementById('LogIn').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    
    const formData = new FormData(this);

    console.log('Attempting login with:', {
        loginInput: formData.get('loginInput'),
        password: '[REDACTED]'
    });

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            document.getElementById('errorMessage').textContent = data.message || 'Login failed';
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
    })
    .finally(() => {
        submitButton.disabled = false;
    });
});

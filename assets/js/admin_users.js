function resetPassword(userId) {
    if (!confirm('Are you sure you want to reset this user\'s password?')) return;
    
    fetch('reset_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password has been reset and sent to user\'s email');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while resetting password');
    });
} 
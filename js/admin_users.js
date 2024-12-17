function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('user_id', userId);
    
    fetch('delete_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete user'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    });
}

function handleSearch(value) {
    window.location.href = '?search=' + encodeURIComponent(value);
} 
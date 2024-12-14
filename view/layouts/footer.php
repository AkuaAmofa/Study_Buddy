<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Study Buddy</h3>
            <p>Connecting students for better learning</p>
        </div>
        
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="study-buddies.php">Study Network</a></li>
                <li><a href="resources.php">Resources</a></li>
                <li><a href="assignments.php">Assignments</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Contact</h4>
            <ul>
                <li><i class='bx bx-envelope'></i> support@studybuddy.com</li>
                <li><i class='bx bx-phone'></i> (123) 456-7890</li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Study Buddy. All rights reserved.</p>
    </div>
</footer>

<style>
.footer {
    background: #f8f9fa;
    padding: 2rem 0;
    margin-top: 2rem;
    border-top: 1px solid #eee;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    padding: 0 1rem;
}

.footer-section h3 {
    color: #2196F3;
    margin-bottom: 1rem;
}

.footer-section h4 {
    color: #333;
    margin-bottom: 1rem;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 0.5rem;
}

.footer-section ul li a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: #2196F3;
}

.footer-section ul li i {
    margin-right: 0.5rem;
    color: #2196F3;
}

.footer-bottom {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
    color: #666;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .footer-section {
        margin-bottom: 1.5rem;
    }
}
</style> 
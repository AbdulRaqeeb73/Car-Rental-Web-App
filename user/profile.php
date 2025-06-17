<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">My Profile</h1>
    
    <?php
    // Check for success message
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    
    // Check for error message
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    
    // Get user information
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Process form submission for profile update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $error = "";
        
        if (empty($name) || empty($email)) {
            $error = "Name and email are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email";
        } else {
            // Check if email exists for a different user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already in use by another account";
            } else {
                // Update user information
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['name'] = $name;
                    $_SESSION['success'] = "Profile updated successfully";
                    header("Location: profile.php");
                    exit();
                } else {
                    $error = "Failed to update profile: " . $conn->error;
                }
            }
        }
    }
    
    // Process form submission for password update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $error = "";
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required";
        } elseif ($new_password != $confirm_password) {
            $error = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long";
        } else {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $error = "Current password is incorrect";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Password updated successfully";
                    header("Location: profile.php");
                    exit();
                } else {
                    $error = "Failed to update password: " . $conn->error;
                }
            }
        }
    }
    ?>
    
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><i class="fas fa-calendar"></i> Joined on <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="profile-actions">
                <div class="action-tab active" data-tab="edit-profile">Edit Profile</div>
                <div class="action-tab" data-tab="change-password">Change Password</div>
                <div class="action-tab" data-tab="booking-stats">Booking Statistics</div>
            </div>
            
            <div class="profile-content">
                <!-- Edit Profile Tab -->
                <div class="tab-content active" id="edit-profile">
                    <h3>Update Profile Information</h3>
                    
                    <?php if (isset($error) && !empty($error) && isset($_POST['update_profile'])): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="profile.php" method="post">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group text-right">
                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-content" id="change-password">
                    <h3>Change Password</h3>
                    
                    <?php if (isset($error) && !empty($error) && isset($_POST['update_password'])): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="profile.php" method="post">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <small>Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group text-right">
                            <input type="hidden" name="update_password" value="1">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
                
                <!-- Booking Statistics Tab -->
                <div class="tab-content" id="booking-stats">
                    <h3>Your Booking Statistics</h3>
                    
                    <?php
                    // Get booking statistics
                    $stmt = $conn->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $total_bookings = $stmt->get_result()->fetch_assoc()['total_bookings'];
                    
                    $stmt = $conn->prepare("SELECT COUNT(*) as active_bookings FROM bookings WHERE user_id = ? AND status = 'active'");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $active_bookings = $stmt->get_result()->fetch_assoc()['active_bookings'];
                    
                    $stmt = $conn->prepare("SELECT SUM(total_cost) as total_spent FROM bookings WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $total_spent_data = $result->fetch_assoc();
                    $total_spent = $total_spent_data['total_spent'] ? $total_spent_data['total_spent'] : 0;
                    
                    $stmt = $conn->prepare("SELECT b.car_id, c.brand, c.model, COUNT(*) as rent_count 
                                           FROM bookings b 
                                           JOIN cars c ON b.car_id = c.id 
                                           WHERE b.user_id = ? 
                                           GROUP BY b.car_id 
                                           ORDER BY rent_count DESC 
                                           LIMIT 1");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $favorite_car = $result->num_rows > 0 ? $result->fetch_assoc() : null;
                    ?>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-details">
                                <h4>Total Rentals</h4>
                                <p class="stat-number"><?php echo $total_bookings; ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-details">
                                <h4>Active Rentals</h4>
                                <p class="stat-number"><?php echo $active_bookings; ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-details">
                                <h4>Total Spent</h4>
                                <p class="stat-number">$<?php echo number_format($total_spent, 2); ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-details">
                                <h4>Favorite Car</h4>
                                <p class="stat-text">
                                    <?php if ($favorite_car): ?>
                                        <?php echo htmlspecialchars($favorite_car['brand'] . ' ' . $favorite_car['model']); ?>
                                        <span class="rental-count">(<?php echo $favorite_car['rent_count']; ?> rentals)</span>
                                    <?php else: ?>
                                        None yet
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 800px;
    margin: 0 auto;
}

.profile-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.profile-header {
    padding: 30px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 20px;
}

.profile-avatar {
    font-size: 4rem;
    color: var(--primary-color);
}

.profile-info h2 {
    margin-bottom: 10px;
}

.profile-info p {
    margin-bottom: 5px;
    color: var(--gray-color);
}

.profile-info i {
    margin-right: 5px;
    color: var(--primary-color);
}

.profile-actions {
    display: flex;
    border-bottom: 1px solid #eee;
}

.action-tab {
    padding: 15px 30px;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 2px solid transparent;
}

.action-tab.active {
    border-bottom-color: var(--primary-color);
    color: var(--primary-color);
    font-weight: bold;
}

.action-tab:hover {
    background-color: #f8f9fa;
}

.profile-content {
    padding: 30px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

h3 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.form-group.text-right {
    text-align: right;
}

small {
    display: block;
    margin-top: 5px;
    color: var(--gray-color);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2rem;
    color: var(--primary-color);
}

.stat-details h4 {
    margin-bottom: 5px;
    color: var(--gray-color);
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--dark-color);
}

.stat-text {
    font-size: 1.1rem;
    color: var(--dark-color);
}

.rental-count {
    font-size: 0.9rem;
    color: var(--gray-color);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--border-radius);
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 576px) {
    .profile-actions {
        flex-direction: column;
    }
    
    .action-tab.active {
        background-color: #f8f9fa;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.action-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all tab contents
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Show the tab content corresponding to clicked tab
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php include_once 'footer.php'; ?> 
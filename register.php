<?php 
include_once 'includes/header.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $error = "";
    $success = "";
    
    // Format phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    if (substr($phone, 0, 2) !== '92') {
        $phone = '92' . $phone;
    }
    
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!preg_match("/^92[0-9]{10}$/", $phone)) {
        $error = "Please enter a valid Pakistani phone number (e.g., 03456789012)";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with phone number
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center">Register</h2>
        
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success) && !empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post" id="registerForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" required 
                       pattern="^0[0-9]{10}$" 
                       placeholder="03456789012">
                <small>Enter your Pakistani phone number starting with 0 (e.g., 03456789012)</small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small>Password must be at least 6 characters long</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
        
        <div class="text-center mt-2">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('phone').addEventListener('input', function(e) {
    // Remove any non-numeric characters
    let value = e.target.value.replace(/[^0-9]/g, '');
    
    // Ensure it starts with 0
    if (value.length > 0 && value[0] !== '0') {
        value = '0' + value;
    }
    
    // Limit to 11 digits (0 + 10 digits)
    value = value.substring(0, 11);
    
    e.target.value = value;
});
</script>

<style>
.alert {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: var(--border-radius);
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

small {
    display: block;
    margin-top: 5px;
    color: var(--gray-color);
}
</style>

<?php include_once 'includes/footer.php'; ?> 
<?php 
include_once 'includes/header.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) { //$_SESSION is a Global Array in PHP that stores information (variables) about the user session.
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

// Get login type (admin or user)
$type = isset($_GET['type']) && $_GET['type'] == 'admin' ? 'admin' : 'user'; //$_GET['type'] gets the value of the type parameter from the URL. isset($_GET['type']) checks if this parameter exists.
$formTitle = $type == 'admin' ? 'Admin Login' : 'User Login';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $error = "";
    
    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Check if it's admin login
        if ($type == 'admin') {
            $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1";
        } else {
            $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        }
        
        // Prepare statement. This is a security feature to prevent SQL injection attacks.
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables. It is used for Session Management.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center"><?php echo $formTitle; ?></h2>
        
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php<?php echo $type == 'admin' ? '?type=admin' : ''; ?>" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        
        <?php if ($type != 'admin'): ?>
            <div class="text-center mt-2">
                <p>Don't have an account? <a href="register.php">Register Now</a></p>
            </div>
        <?php else: ?>
            <div class="text-center mt-2">
                <p><a href="login.php">User Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.alert {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: var(--border-radius); /* Border Radius is 10px*/
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<?php include_once 'includes/footer.php'; ?> 
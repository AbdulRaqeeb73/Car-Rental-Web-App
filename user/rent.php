<?php 

include_once 'header.php';
include_once '../includes/notifications.php';
?>

<div class="container">
    <?php
    // Check if car ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['error'] = "Invalid car ID";
        header("Location: dashboard.php");
        exit();
    }
    
    $car_id = $_GET['id'];
    
    // Fetch car details
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ? AND is_available = 1");
    $stmt->bind_param("i", $car_id); //This is used to prevent SQL Injection.
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        $_SESSION['error'] = "Car not found or not available for rent";
        header("Location: dashboard.php");
        exit();
    }
    
    $car = $result->fetch_assoc();
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $total_cost = $_POST['total_cost_input'];
        $user_id = $_SESSION['user_id'];
        $error = "";
        
        // Validate dates
        $current_date = date('Y-m-d');
        if (empty($start_date) || empty($end_date)) {
            $error = "Please select both start and end dates";
        } elseif ($start_date < $current_date) {
            $error = "Start date cannot be in the past";
        } elseif ($end_date <= $start_date) {
            $error = "End date must be after start date";
        } elseif (empty($total_cost) || $total_cost <= 0) {
            $error = "Invalid total cost";
        } else {
            // Begin transaction. A transaction is a way to group multiple SQL operations together — and only apply them if all of them succeed.
            $conn->begin_transaction();
            
            try {
                // Get user details for notifications
                $stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                // Insert booking
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_cost, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_cost);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create booking");
                }
                
                // Update car availability
                $stmt = $conn->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
                $stmt->bind_param("i", $car_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update car availability");
                }
                
                // Prepare rental details for notifications
                $rental_details = [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'total_cost' => $total_cost
                ];
                
                // Send email notification
                if (!sendRentalEmail($user['email'], $user['name'], $car, $rental_details)) {
                    error_log("Failed to send rental confirmation email to: " . $user['email']);
                }
                
                // Send SMS notification if phone number exists
                if (!empty($user['phone'])) {
                    if (!sendRentalSMS($user['phone'], $car, $rental_details)) {
                        error_log("Failed to send rental confirmation SMS to: " . $user['phone']);
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $_SESSION['success'] = "Car rented successfully! Check your email and phone for confirmation.";
                header("Location: bookings.php");
                exit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
    ?>
    
    <h1 class="section-title">Rent a Car</h1>
    
    <?php if (isset($error) && !empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="rent-container">
        <div class="car-details-card">
            <img src="../<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" class="car-img-large">
            <div class="car-info">
                <h2><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h2>
                <p><strong>Year:</strong> <?php echo $car['year']; ?></p>
                <p><strong>Price per Day:</strong> $<?php echo number_format($car['price_per_day'], 2); ?></p>
                <input type="hidden" id="price_per_day" value="<?php echo $car['price_per_day']; ?>">
            </div>
        </div>
        
        <div class="rental-form">
            <h3>Rental Details</h3>
            <form action="rent.php?id=<?php echo $car_id; ?>" method="post">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                
                <div class="rental-summary">
                    <div class="summary-item">
                        <span>Total Days:</span>
                        <span id="total_days">0</span>
                    </div>
                    <div class="summary-item">
                        <span>Total Cost:</span>
                        <span>$<span id="total_cost">0.00</span></span>
                        <input type="hidden" id="total_cost_input" name="total_cost_input" value="0">
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    <a href="dashboard.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rent-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.car-details-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.car-img-large {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.car-info {
    padding: 20px;
}

.car-info h2 {
    margin-bottom: 15px;
    color: var(--dark-color);
}

.rental-form {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
}

.rental-form h3 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.rental-summary {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
    margin: 20px 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.summary-item:last-child {
    margin-bottom: 0;
    padding-top: 10px;
    border-top: 1px solid #ddd;
    font-weight: bold;
}

.form-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--border-radius);
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .rent-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include_once 'footer.php'; ?> 
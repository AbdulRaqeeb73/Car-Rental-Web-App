<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">My Bookings</h1>
    
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
    
    // Return car if requested
    if (isset($_GET['return']) && is_numeric($_GET['return'])) {
        $booking_id = $_GET['return'];
        $user_id = $_SESSION['user_id'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Get car ID from booking and verify it belongs to this user
            $stmt = $conn->prepare("SELECT car_id FROM bookings WHERE id = ? AND user_id = ? AND status = 'active'");
            $stmt->bind_param("ii", $booking_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                throw new Exception("Booking not found or already completed");
            }
            
            $car_id = $result->fetch_assoc()['car_id'];
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking status");
            }
            
            // Update car availability
            $stmt = $conn->prepare("UPDATE cars SET is_available = 1 WHERE id = ?");
            $stmt->bind_param("i", $car_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update car availability");
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Car returned successfully";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: bookings.php");
        exit();
    }
    ?>
    
    <div class="bookings-list">
        <?php
        $user_id = $_SESSION['user_id'];
        
        // Prepare base query
        $query = "
            SELECT b.*, c.brand, c.model, c.image_path, c.price_per_day
            FROM bookings b
            JOIN cars c ON b.car_id = c.id
            WHERE b.user_id = ?
        ";
        $params = array($user_id);
        $types = "i";
        
        // Apply status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $query .= " AND b.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }
        
        // Apply sorting
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'oldest':
                    $query .= " ORDER BY b.created_at ASC";
                    break;
                case 'start_date':
                    $query .= " ORDER BY b.start_date ASC";
                    break;
                case 'end_date':
                    $query .= " ORDER BY b.end_date ASC";
                    break;
                default:
                    $query .= " ORDER BY b.created_at DESC";
                    break;
            }
        } else {
            $query .= " ORDER BY b.created_at DESC";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $is_active = $row['status'] == 'active';
                $current_date = date('Y-m-d');
                $is_ongoing = $is_active && $row['start_date'] <= $current_date && $row['end_date'] >= $current_date;
                $is_upcoming = $is_active && $row['start_date'] > $current_date;
                
                $status_class = $is_active ? ($is_ongoing ? 'ongoing' : ($is_upcoming ? 'upcoming' : 'overdue')) : 'completed';
                $status_text = $is_active ? ($is_ongoing ? 'Ongoing' : ($is_upcoming ? 'Upcoming' : 'Overdue')) : 'Completed';
                ?>
                <div class="booking-card <?php echo $status_class; ?>">
                    <div class="booking-header">
                        <span class="booking-id">Booking #<?php echo $row['id']; ?></span>
                        <span class="booking-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                    
                    <div class="booking-content">
                        <div class="car-info">
                            <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?>" class="car-thumbnail">
                            <div>
                                <h3><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></h3>
                                <p>$<?php echo number_format($row['price_per_day'], 2); ?> per day</p>
                            </div>
                        </div>
                        
                        <div class="booking-details">
                            <div class="detail-row">
                                <span><i class="fas fa-calendar-alt"></i> Start Date:</span>
                                <span><?php echo date('M d, Y', strtotime($row['start_date'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span><i class="fas fa-calendar-check"></i> End Date:</span>
                                <span><?php echo date('M d, Y', strtotime($row['end_date'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span><i class="fas fa-dollar-sign"></i> Total Cost:</span>
                                <span>$<?php echo number_format($row['total_cost'], 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span><i class="fas fa-clock"></i> Booked On:</span>
                                <span><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-actions">
                        <?php if ($is_active): ?>
                            <a href="bookings.php?return=<?php echo $row['id']; ?>" class="btn btn-primary" onclick="return confirm('Are you sure you want to return this car?')">Return Car</a>
                        <?php else: ?>
                            <span class="text-muted">Rental Completed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='no-bookings'>You don't have any bookings yet. <a href='dashboard.php'>Browse available cars</a> to make a booking.</div>";
        }
        ?>
    </div>
</div>

<style>
.filter-form {
    background-color: white;
    padding: 15px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-item {
    flex: 1;
    min-width: 200px;
}

.filter-item label {
    display: block;
    margin-bottom: 5px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.bookings-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.booking-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.booking-header {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.booking-id {
    font-weight: bold;
}

.booking-status {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.booking-status.ongoing {
    background-color: #d4edda;
    color: #155724;
}

.booking-status.upcoming {
    background-color: #cce5ff;
    color: #004085;
}

.booking-status.completed {
    background-color: #e2e3e5;
    color: #383d41;
}

.booking-status.overdue {
    background-color: #f8d7da;
    color: #721c24;
}

.booking-content {
    padding: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.car-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    min-width: 250px;
}

.car-thumbnail {
    width: 100px;
    height: 70px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.car-info h3 {
    margin-bottom: 5px;
}

.booking-details {
    flex: 2;
    min-width: 300px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-row i {
    margin-right: 5px;
    color: var(--primary-color);
}

.booking-actions {
    padding: 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}

.no-bookings {
    background-color: white;
    padding: 50px;
    text-align: center;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
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

.text-muted {
    color: var(--gray-color);
    font-style: italic;
}

@media (max-width: 768px) {
    .booking-content {
        flex-direction: column;
        gap: 20px;
    }
}
</style>

<?php include_once 'footer.php'; ?> 
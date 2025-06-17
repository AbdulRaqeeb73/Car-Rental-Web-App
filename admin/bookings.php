<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">Booking Management</h1>
    
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
    
    // Mark booking as completed if requested
    if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
        $booking_id = $_GET['complete'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Get car ID from booking
            $stmt = $conn->prepare("SELECT car_id FROM bookings WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                throw new Exception("Booking not found");
            }
            
            $car_id = $result->fetch_assoc()['car_id'];
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            
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
            
            $_SESSION['success'] = "Booking marked as completed";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: bookings.php");
        exit();
    }
    ?>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Car</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "
                    SELECT b.*, u.name as user_name, u.email as user_email, 
                           c.brand, c.model, c.image_path
                    FROM bookings b
                    JOIN users u ON b.user_id = u.id
                    JOIN cars c ON b.car_id = c.id
                    ORDER BY b.created_at DESC
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td data-tooltip='" . htmlspecialchars($row['user_email']) . "'>" . htmlspecialchars($row['user_name']) . "</td>";
                        echo "<td>";
                        echo "<div class='car-info'>";
                        echo "<img src='../" . htmlspecialchars($row['image_path']) . "' alt='Car Image' class='car-thumbnail'>";
                        echo "<span>" . htmlspecialchars($row['brand'] . ' ' . $row['model']) . "</span>";
                        echo "</div>";
                        echo "</td>";
                        echo "<td>" . $row['start_date'] . "</td>";
                        echo "<td>" . $row['end_date'] . "</td>";
                        echo "<td>$" . number_format($row['total_cost'], 2) . "</td>";
                        echo "<td>";
                        if ($row['status'] == 'active') {
                            echo "<span class='status active'>Active</span>";
                        } else {
                            echo "<span class='status completed'>Completed</span>";
                        }
                        echo "</td>";
                        echo "<td class='actions'>";
                        if ($row['status'] == 'active') {
                            echo "<a href='bookings.php?complete=" . $row['id'] . "' class='btn-sm btn-primary' onclick='return confirm(\"Mark this booking as completed?\")'>Complete</a>";
                        } else {
                            echo "<span class='text-muted'>No Actions</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No bookings found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.car-thumbnail {
    width: 40px;
    height: 30px;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-right: 10px;
}

.car-info {
    display: flex;
    align-items: center;
}

.actions {
    white-space: nowrap;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.8rem;
}

.text-muted {
    color: var(--gray-color);
    font-style: italic;
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

/* Tooltip styles */
[data-tooltip] {
    position: relative;
    cursor: help;
    text-decoration: underline dotted;
}

[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    z-index: 10;
    font-size: 0.8rem;
}
</style>

<?php include_once 'footer.php'; ?> 

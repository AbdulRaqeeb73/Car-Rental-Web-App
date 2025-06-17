<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">Manage Cars</h1>
    
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
    
    // Delete car if requested
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $car_id = $_GET['delete'];
        
        // Check if car has active bookings
        $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE car_id = ? AND status = 'active'");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_count = $result->fetch_assoc()['booking_count'];
        
        if ($booking_count > 0) {
            $_SESSION['error'] = "Cannot delete car with active bookings";
            header("Location: cars.php");
            exit();
        }
        
        // Delete car
        $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Car deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete car: " . $conn->error;
        }
        
        header("Location: cars.php");
        exit();
    }
    ?>
    
    <div class="admin-actions mb-2">
        <a href="add_car.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Car
        </a>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Price/Day</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM cars ORDER BY id DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td><img src='../" . htmlspecialchars($row['image_path']) . "' alt='Car Image' class='car-thumbnail'></td>";
                        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                        echo "<td>" . $row['year'] . "</td>";
                        echo "<td>$" . number_format($row['price_per_day'], 2) . "</td>";
                        echo "<td>";
                        if ($row['is_available']) {
                            echo "<span class='status active'>Available</span>";
                        } else {
                            echo "<span class='status rented'>Rented</span>";
                        }
                        echo "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='edit_car.php?id=" . $row['id'] . "' class='btn-sm btn-primary'><i class='fas fa-edit'></i> Edit</a>";
                        echo "<a href='cars.php?delete=" . $row['id'] . "' class='btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this car?\")'><i class='fas fa-trash'></i> Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No cars found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.car-thumbnail {
    width: 80px;
    height: 50px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.actions {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.8rem;
}

.status.rented {
    background-color: #f8d7da;
    color: #721c24;
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
</style>

<?php include_once 'footer.php'; ?>

<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">Browse Available Cars</h1>
    
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
    ?>
    
    <div class="car-grid">
        <?php
        // Prepare base query - only show available cars
        $query = "SELECT * FROM cars WHERE is_available = 1";
        $params = array();
        $types = "";
        
        // Apply brand filter
        if (isset($_GET['brand']) && !empty($_GET['brand'])) {
            $query .= " AND brand = ?";
            $params[] = $_GET['brand'];
            $types .= "s";
        }
        
        // Apply sorting
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'price_desc':
                    $query .= " ORDER BY price_per_day DESC";
                    break;
                case 'year_desc':
                    $query .= " ORDER BY year DESC";
                    break;
                case 'brand_asc':
                    $query .= " ORDER BY brand ASC, model ASC";
                    break;
                default:
                    $query .= " ORDER BY price_per_day ASC";
                    break;
            }
        } else {
            $query .= " ORDER BY price_per_day ASC";
        }
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="car-card">
                    <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?>" class="car-img">
                    <div class="car-details">
                        <h3><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></h3>
                        <p><strong>Year:</strong> <?php echo $row['year']; ?></p>
                        <p class="price">$<?php echo number_format($row['price_per_day'], 2); ?> per day</p>
                    </div>
                    <div class="car-actions">
                        <a href="rent.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Rent Now</a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='no-cars'>No cars available at the moment. Please check back later.</div>";
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

.no-cars {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    color: var(--gray-color);
    font-size: 1.2rem;
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
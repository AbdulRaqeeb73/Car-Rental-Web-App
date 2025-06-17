<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">Admin Dashboard</h1>

    <?php
    // Get stats
    // Total cars
    $stmt = $conn->prepare("SELECT COUNT(*) as total_cars FROM cars");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_cars = $result->fetch_assoc()['total_cars'];
    
    // Available cars
    $stmt = $conn->prepare("SELECT COUNT(*) as available_cars FROM cars WHERE is_available = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $available_cars = $result->fetch_assoc()['available_cars'];
    
    // Total bookings
    $stmt = $conn->prepare("SELECT COUNT(*) as total_bookings FROM bookings");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_bookings = $result->fetch_assoc()['total_bookings'];
    
    // Active bookings
    $stmt = $conn->prepare("SELECT COUNT(*) as active_bookings FROM bookings WHERE status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $active_bookings = $result->fetch_assoc()['active_bookings'];
    
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['total_users'];
    
    // Total revenue
    $stmt = $conn->prepare("SELECT SUM(total_cost) as total_revenue FROM bookings");
    $stmt->execute();
    $result = $stmt->get_result();
    $revenue_data = $result->fetch_assoc();
    $total_revenue = $revenue_data['total_revenue'] ? $revenue_data['total_revenue'] : 0;
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-car"></i>
            </div>
            <div class="stat-details">
                <h3>Total Cars</h3>
                <p class="stat-number"><?php echo $total_cars; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>Available Cars</h3>
                <p class="stat-number"><?php echo $available_cars; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-details">
                <h3>Total Bookings</h3>
                <p class="stat-number"><?php echo $total_bookings; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="stat-details">
                <h3>Active Bookings</h3>
                <p class="stat-number"><?php echo $active_bookings; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo $total_users; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3>Total Revenue</h3>
                <p class="stat-number">$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>
    </div>

    <div class="admin-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="action-buttons">
            <a href="add_car.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Car
            </a>
            <a href="cars.php" class="btn">
                <i class="fas fa-car"></i> Manage Cars
            </a>
            <a href="bookings.php" class="btn">
                <i class="fas fa-list"></i> View All Bookings
            </a>
        </div>
    </div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.stat-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    display: flex;
    padding: 20px;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-right: 20px;
    display: flex;
    align-items: center;
}

.stat-details h3 {
    margin-bottom: 5px;
    color: var(--gray-color);
    font-size: 1rem;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--dark-color);
}

.admin-actions {
    margin: 40px 0;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
}

.action-buttons .btn {
    display: flex;
    align-items: center;
    font-size: 1rem;
}

.action-buttons .btn i {
    margin-right: 8px;
}

.status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.status.active {
    background-color: #d4edda;
    color: #155724;
}

.status.completed {
    background-color: #cce5ff;
    color: #004085;
}
</style>

<?php include_once 'footer.php'; ?> 
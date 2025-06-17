<?php include_once 'header.php'; ?>

<div class="container">
    <h1 class="section-title">Edit Car</h1>
    
    <?php
    // Check if car ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['error'] = "Invalid car ID";
        header("Location: cars.php");
        exit();
    }
    
    $car_id = $_GET['id'];
    
    // Fetch car details
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        $_SESSION['error'] = "Car not found";
        header("Location: cars.php");
        exit();
    }
    
    $car = $result->fetch_assoc();
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $model = $_POST['model'];
        $brand = $_POST['brand'];
        $year = $_POST['year'];
        $price_per_day = $_POST['price_per_day'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $error = "";
        
        // Validate input
        if (empty($model) || empty($brand) || empty($year) || empty($price_per_day)) {
            $error = "All fields are required";
        } elseif (!is_numeric($year) || $year < 1900 || $year > date("Y") + 1) {
            $error = "Please enter a valid year";
        } elseif (!is_numeric($price_per_day) || $price_per_day <= 0) {
            $error = "Please enter a valid price";
        } else {
            // Use existing image path by default
            $image_path = $car['image_path'];
            
            // Handle image upload if new image is provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $temp = explode('.', $filename);
                $ext = strtolower(end($temp));
                
                if (in_array($ext, $allowed)) {
                    $temp_name = $_FILES['image']['tmp_name'];
                    $new_filename = 'car_' . time() . '.' . $ext;
                    $upload_path = '../assets/images/' . $new_filename;
                    
                    if (move_uploaded_file($temp_name, $upload_path)) {
                        $image_path = 'assets/images/' . $new_filename;
                        
                        // Delete old image if it's not the default image
                        if ($car['image_path'] !== 'assets/images/default-car.jpg') {
                            $old_image = '../' . $car['image_path'];
                            if (file_exists($old_image)) {
                                unlink($old_image);
                            }
                        }
                    } else {
                        $error = "Failed to upload image";
                    }
                } else {
                    $error = "Invalid image format. Allowed formats: jpg, jpeg, png, gif";
                }
            }
            
            if (empty($error)) {
                // Update car in database
                $stmt = $conn->prepare("UPDATE cars SET model = ?, brand = ?, year = ?, price_per_day = ?, image_path = ?, is_available = ? WHERE id = ?");
                $stmt->bind_param("ssidsii", $model, $brand, $year, $price_per_day, $image_path, $is_available, $car_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Car updated successfully";
                    header("Location: cars.php");
                    exit();
                } else {
                    $error = "Failed to update car: " . $conn->error;
                }
            }
        }
    } else {
        // Pre-populate form fields
        $model = $car['model'];
        $brand = $car['brand'];
        $year = $car['year'];
        $price_per_day = $car['price_per_day'];
        $is_available = $car['is_available'];
        $image_path = $car['image_path'];
    }
    ?>
    
    <?php if (isset($error) && !empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-container car-form">
        <form action="edit_car.php?id=<?php echo $car_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" class="form-control" required value="<?php echo htmlspecialchars($brand); ?>">
            </div>
            
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" class="form-control" required value="<?php echo htmlspecialchars($model); ?>">
            </div>
            
            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" class="form-control" min="1900" max="<?php echo date("Y") + 1; ?>" required value="<?php echo $year; ?>">
            </div>
            
            <div class="form-group">
                <label for="price_per_day">Price Per Day ($)</label>
                <input type="number" id="price_per_day" name="price_per_day" class="form-control" min="0" step="0.01" required value="<?php echo $price_per_day; ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Car Image</label>
                <div class="image-preview-container">
                    <img id="image-preview" src="../<?php echo htmlspecialchars($image_path); ?>" alt="Car Image Preview">
                </div>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small>Leave empty to keep the current image</small>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_available" name="is_available" <?php echo $is_available ? 'checked' : ''; ?>>
                <label for="is_available">Available for Rent</label>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Update Car</button>
                <a href="cars.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.car-form {
    max-width: 700px;
    margin: 0 auto;
}

.form-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.image-preview-container {
    margin-bottom: 10px;
    text-align: center;
}

#image-preview {
    max-width: 100%;
    max-height: 200px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    padding: 5px;
}

.checkbox-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.checkbox-group input {
    width: auto;
}

.checkbox-group label {
    margin-bottom: 0;
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
</style>

<script>
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('image-preview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include_once 'footer.php'; ?> 
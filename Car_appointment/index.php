<?php
require_once 'config.php';
require_once 'includes/session.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $conn = getDBConnection();
    
    // Sanitize inputs
    $client_name = sanitizeInput($_POST['client_name']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    $license_no = sanitizeInput($_POST['license_no']);
    $engine_no = sanitizeInput($_POST['engine_no']);
    $appointment_date = sanitizeInput($_POST['appointment_date']);
    $mechanic_id = (int)$_POST['mechanic_id'];
    
    // Validate date
    if (!validateDate($appointment_date) || !isFutureDate($appointment_date)) {
        $message = "Please select a valid future date.";
        $messageType = "error";
    } else {
        // Check if client already has an appointment on this date
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE client_name = ? AND appointment_date = ?");
        $stmt->bind_param("ss", $client_name, $appointment_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "You already have an appointment on this date.";
            $messageType = "error";
        } else {
            // Check mechanic availability
            $stmt = $conn->prepare("
                SELECT COUNT(*) as appointment_count 
                FROM appointments 
                WHERE mechanic_id = ? AND appointment_date = ?
            ");
            $stmt->bind_param("is", $mechanic_id, $appointment_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['appointment_count'] >= 4) {
                $message = "Selected mechanic is fully booked for this date.";
                $messageType = "error";
            } else {
                // Insert appointment
                $stmt = $conn->prepare("
                    INSERT INTO appointments 
                    (client_name, address, phone, license_no, engine_no, appointment_date, mechanic_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("ssssssi", $client_name, $address, $phone, $license_no, $engine_no, $appointment_date, $mechanic_id);
                
                if ($stmt->execute()) {
                    $message = "Appointment booked successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error booking appointment. Please try again.";
                    $messageType = "error";
                }
            }
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop Appointment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Book Your Car Service Appointment</h1>
            <p>Schedule your car maintenance with our expert mechanics</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
        <div class="card">
            <form method="POST" action="" id="appointmentForm">
                <div class="form-group">
                    <label for="client_name">Full Name *</label>
                    <input type="text" id="client_name" name="client_name" required>
                </div>

                <div class="form-group">
                    <label for="address">Address *</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="license_no">Car License Number *</label>
                    <input type="text" id="license_no" name="license_no" required>
                </div>

                <div class="form-group">
                    <label for="engine_no">Car Engine Number *</label>
                    <input type="text" id="engine_no" name="engine_no" required>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Preferred Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" required>
                </div>

                <div class="form-group">
                    <label for="mechanic_id">Select Mechanic *</label>
                    <select id="mechanic_id" name="mechanic_id" required>
                        <option value="">Select a date first</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Book Appointment</button>
            </form>
        </div>
        <?php else: ?>
        <div class="card" style="text-align:center;">
            <p><strong>You must be logged in to book a service appointment.</strong></p>
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html> 
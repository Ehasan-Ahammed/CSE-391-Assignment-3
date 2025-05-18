<?php
require_once 'config.php';
require_once 'includes/session.php';
requireAdmin();

$message = '';
$messageType = '';

// Handle appointment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $conn = getDBConnection();
    
    $appointment_id = (int)$_POST['appointment_id'];
    $new_date = sanitizeInput($_POST['new_date']);
    $new_mechanic_id = (int)$_POST['new_mechanic_id'];
    
    // Validate date
    if (!validateDate($new_date) || !isFutureDate($new_date)) {
        $message = "Please select a valid future date.";
        $messageType = "error";
    } else {
        // Check mechanic availability
        $stmt = $conn->prepare("
            SELECT COUNT(*) as appointment_count 
            FROM appointments 
            WHERE mechanic_id = ? AND appointment_date = ? AND id != ?
        ");
        $stmt->bind_param("isi", $new_mechanic_id, $new_date, $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['appointment_count'] >= 4) {
            $message = "Selected mechanic is fully booked for this date.";
            $messageType = "error";
        } else {
            // Update appointment
            $stmt = $conn->prepare("
                UPDATE appointments 
                SET appointment_date = ?, mechanic_id = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("sii", $new_date, $new_mechanic_id, $appointment_id);
            
            if ($stmt->execute()) {
                $message = "Appointment updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating appointment. Please try again.";
                $messageType = "error";
            }
        }
    }
    
    $stmt->close();
    $conn->close();
}

// Handle appointment delete
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'delete' &&
    isset($_POST['appointment_id'])
) {
    $conn = getDBConnection();
    $appointment_id = (int)$_POST['appointment_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    if ($stmt->execute()) {
        $message = "Appointment deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting appointment. Please try again.";
        $messageType = "error";
    }
    $stmt->close();
    $conn->close();
}

// Fetch all appointments
$conn = getDBConnection();
$query = "
    SELECT a.*, m.name as mechanic_name 
    FROM appointments a 
    JOIN mechanics m ON a.mechanic_id = m.id 
    ORDER BY a.appointment_date DESC, a.created_at DESC
";
$result = $conn->query($query);
$appointments = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all mechanics for the update form
$mechanics_query = "SELECT * FROM mechanics ORDER BY name";
$mechanics_result = $conn->query($mechanics_query);
$mechanics = $mechanics_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Car Workshop Appointment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            max-width: 500px;
            width: 90%;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--primary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
            padding: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .appointment-details {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--background-color);
            border-radius: var(--border-radius);
        }

        .appointment-details p {
            margin: 0.5rem 0;
        }

        .appointment-details strong {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Appointment Management</h1>
            <p>View and manage all car service appointments</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Phone</th>
                            <th>License No.</th>
                            <th>Appointment Date</th>
                            <th>Mechanic</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['license_no']); ?></td>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['mechanic_name']); ?></td>
                                <td>
                                    <button class="btn btn-primary" onclick="showUpdateForm(<?php echo htmlspecialchars(json_encode($appointment)); ?>)">
                                        Edit
                                    </button>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Appointment Modal -->
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Update Appointment</h2>
                    <button class="close-modal" onclick="hideUpdateForm()">&times;</button>
                </div>
                
                <div class="appointment-details">
                    <p><strong>Client:</strong> <span id="modal_client_name"></span></p>
                    <p><strong>Phone:</strong> <span id="modal_phone"></span></p>
                    <p><strong>License No:</strong> <span id="modal_license_no"></span></p>
                    <p><strong>Current Date:</strong> <span id="modal_current_date"></span></p>
                    <p><strong>Current Mechanic:</strong> <span id="modal_current_mechanic"></span></p>
                </div>

                <form method="POST" action="" id="updateForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="appointment_id" id="update_appointment_id">
                    
                    <div class="form-group">
                        <label for="new_date">New Appointment Date *</label>
                        <input type="date" id="new_date" name="new_date" required>
                    </div>

                    <div class="form-group">
                        <label for="new_mechanic_id">New Mechanic *</label>
                        <select id="new_mechanic_id" name="new_mechanic_id" required>
                            <?php foreach ($mechanics as $mechanic): ?>
                                <option value="<?php echo $mechanic['id']; ?>">
                                    <?php echo htmlspecialchars($mechanic['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Appointment</button>
                        <button type="button" class="btn btn-danger" onclick="hideUpdateForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showUpdateForm(appointment) {
            // Populate modal with appointment details
            document.getElementById('modal_client_name').textContent = appointment.client_name;
            document.getElementById('modal_phone').textContent = appointment.phone;
            document.getElementById('modal_license_no').textContent = appointment.license_no;
            document.getElementById('modal_current_date').textContent = new Date(appointment.appointment_date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('modal_current_mechanic').textContent = appointment.mechanic_name;
            
            // Set form values
            document.getElementById('update_appointment_id').value = appointment.id;
            document.getElementById('new_date').value = appointment.appointment_date;
            document.getElementById('new_mechanic_id').value = appointment.mechanic_id;
            
            // Show modal
            document.getElementById('updateModal').style.display = 'flex';
        }

        function hideUpdateForm() {
            document.getElementById('updateModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('updateModal');
            if (event.target === modal) {
                hideUpdateForm();
            }
        }

        // Set min date for new appointments
        document.getElementById('new_date').min = new Date().toISOString().split('T')[0];

        // Add event listener for date change to check mechanic availability
        document.getElementById('new_date').addEventListener('change', function() {
            const date = this.value;
            const mechanicSelect = document.getElementById('new_mechanic_id');
            
            if (!date) return;

            // Show loading state
            mechanicSelect.disabled = true;
            const originalHTML = mechanicSelect.innerHTML;

            // Fetch available mechanics
            fetch(`get_available_mechanics.php?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    mechanicSelect.innerHTML = '<option value="">Select a Mechanic</option>';
                    data.forEach(mechanic => {
                        const option = document.createElement('option');
                        option.value = mechanic.id;
                        option.textContent = `${mechanic.name} (${mechanic.available_slots} slots available)`;
                        mechanicSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    mechanicSelect.innerHTML = originalHTML;
                })
                .finally(() => {
                    mechanicSelect.disabled = false;
                });
        });
    </script>
</body>
</html> 
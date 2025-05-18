<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Date parameter is required']);
    exit;
}

$date = sanitizeInput($_GET['date']);

if (!validateDate($date) || !isFutureDate($date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

$conn = getDBConnection();

// Get all mechanics with their available slots for the selected date
$query = "
    SELECT 
        m.id,
        m.name,
        m.max_slots,
        COUNT(a.id) as booked_slots,
        (m.max_slots - COUNT(a.id)) as available_slots
    FROM mechanics m
    LEFT JOIN appointments a ON m.id = a.mechanic_id AND a.appointment_date = ?
    GROUP BY m.id, m.name, m.max_slots
    HAVING available_slots > 0
    ORDER BY m.name
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$mechanics = [];
while ($row = $result->fetch_assoc()) {
    $mechanics[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'available_slots' => $row['available_slots']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($mechanics); 
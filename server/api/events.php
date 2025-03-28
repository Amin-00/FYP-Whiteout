<?php
// Include database configuration
require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request - fetch events
        getEvents($pdo);
        break;
    case 'POST':
        // Handle POST request - create a new event (would require authentication)
        // createEvent($pdo);
        break;
    case 'PUT':
        // Handle PUT request - update an event (would require authentication)
        // updateEvent($pdo);
        break;
    case 'DELETE':
        // Handle DELETE request - delete an event (would require authentication)
        // deleteEvent($pdo);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

/**
 * Get events with optional filtering
 */
function getEvents($pdo) {
    try {
        $query = "SELECT * FROM events";
        $params = [];
        $where = [];
        
        // Check if an ID is provided
        if (isset($_GET['id'])) {
            $where[] = "id = ?";
            $params[] = $_GET['id'];
        }
        
        // Filter by region
        if (isset($_GET['region'])) {
            $where[] = "region = ?";
            $params[] = $_GET['region'];
        }
        
        // Filter by event name (partial match)
        if (isset($_GET['name'])) {
            $where[] = "event_name LIKE ?";
            $params[] = '%' . $_GET['name'] . '%';
        }
        
        // Add where clauses if any
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by dates, upcoming first
        $query .= " ORDER BY STR_TO_DATE(SUBSTRING_INDEX(dates, ' - ', 1), '%b %e, %Y') DESC";
        
        // Check if a limit is provided
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $query .= " LIMIT ?";
            $params[] = intval($_GET['limit']);
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        // Return events as JSON
        echo json_encode($events);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error fetching events: ' . $e->getMessage()]);
    }
}

<?php
// Include database configuration
require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request - fetch upcoming matches
        getUpcomingMatches($pdo);
        break;
    case 'POST':
        // Handle POST request - create a new upcoming match (would require authentication)
        // createUpcomingMatch($pdo);
        break;
    case 'PUT':
        // Handle PUT request - update an upcoming match (would require authentication)
        // updateUpcomingMatch($pdo);
        break;
    case 'DELETE':
        // Handle DELETE request - delete an upcoming match (would require authentication)
        // deleteUpcomingMatch($pdo);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

/**
 * Get upcoming matches with optional filtering
 */
function getUpcomingMatches($pdo) {
    try {
        $query = "SELECT * FROM upcoming_matches";
        $params = [];
        $where = [];
        
        // Check if an ID is provided
        if (isset($_GET['id'])) {
            $where[] = "id = ?";
            $params[] = $_GET['id'];
        }
        
        // Filter by team
        if (isset($_GET['team'])) {
            $teamName = $_GET['team'];
            $where[] = "(team_one_name = ? OR team_two_name = ?)";
            $params[] = $teamName;
            $params[] = $teamName;
        }
        
        // Filter by event
        if (isset($_GET['event'])) {
            $where[] = "event_name = ?";
            $params[] = $_GET['event'];
        }
        
        // Add where clauses if any
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by match time, most recent first
        $query .= " ORDER BY STR_TO_DATE(match_time, '%Y-%m-%d %H:%i:%s') ASC";
        
        // Check if a limit is provided
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $query .= " LIMIT ?";
            $params[] = intval($_GET['limit']);
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $matches = $stmt->fetchAll();
        
        // Return matches as JSON
        echo json_encode($matches);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error fetching upcoming matches: ' . $e->getMessage()]);
    }
}

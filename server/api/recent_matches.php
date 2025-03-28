<?php
// Include database configuration
require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request - fetch recent matches
        getRecentMatches($pdo);
        break;
    case 'POST':
        // Handle POST request - create a new recent match (would require authentication)
        // createRecentMatch($pdo);
        break;
    case 'PUT':
        // Handle PUT request - update a recent match (would require authentication)
        // updateRecentMatch($pdo);
        break;
    case 'DELETE':
        // Handle DELETE request - delete a recent match (would require authentication)
        // deleteRecentMatch($pdo);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

/**
 * Get recent matches for a team
 */
function getRecentMatches($pdo) {
    try {
        $query = "SELECT * FROM recent_matches";
        $params = [];
        $where = [];
        
        // Check if an ID is provided
        if (isset($_GET['id'])) {
            $where[] = "id = ?";
            $params[] = $_GET['id'];
        }
        
        // Filter by team_id
        if (isset($_GET['team_id'])) {
            $where[] = "team_id = ?";
            $params[] = $_GET['team_id'];
        }
        
        // Add where clauses if any
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by match time, most recent first
        $query .= " ORDER BY STR_TO_DATE(match_time, '%Y-%m-%d %H:%i:%s') DESC";
        
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
        echo json_encode(['message' => 'Error fetching recent matches: ' . $e->getMessage()]);
    }
}

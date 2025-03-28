<?php
// Include database configuration
require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request - fetch teams
        getTeams($pdo);
        break;
    case 'POST':
        // Handle POST request - create a new team (would require authentication)
        // createTeam($pdo);
        break;
    case 'PUT':
        // Handle PUT request - update a team (would require authentication)
        // updateTeam($pdo);
        break;
    case 'DELETE':
        // Handle DELETE request - delete a team (would require authentication)
        // deleteTeam($pdo);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

/**
 * Get teams with optional filtering
 */
function getTeams($pdo) {
    try {
        $query = "SELECT * FROM teams";
        $params = [];
        
        // Check if an ID is provided for a single team
        if (isset($_GET['id'])) {
            $query .= " WHERE id = ?";
            $params[] = $_GET['id'];
        }
        
        // Check if a region filter is applied
        if (isset($_GET['region'])) {
            $queryConnector = empty($params) ? " WHERE" : " AND";
            $query .= "$queryConnector region = ?";
            $params[] = $_GET['region'];
        }
        
        // Order by team rank if available
        $query .= " ORDER BY team_rank IS NULL, team_rank ASC";
        
        // Check if a limit is provided
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $query .= " LIMIT ?";
            $params[] = intval($_GET['limit']);
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $teams = $stmt->fetchAll();
        
        // Return teams as JSON
        echo json_encode($teams);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error fetching teams: ' . $e->getMessage()]);
    }
}

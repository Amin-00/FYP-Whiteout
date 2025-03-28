<?php
// Include database configuration
require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Handle GET request - fetch players
        getPlayers($pdo);
        break;
    case 'POST':
        // Handle POST request - create a new player (would require authentication)
        // createPlayer($pdo);
        break;
    case 'PUT':
        // Handle PUT request - update a player (would require authentication)
        // updatePlayer($pdo);
        break;
    case 'DELETE':
        // Handle DELETE request - delete a player (would require authentication)
        // deletePlayer($pdo);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

/**
 * Get players with optional filtering
 */
function getPlayers($pdo) {
    try {
        $query = "SELECT * FROM players";
        $params = [];
        $where = [];
        
        // Check if an ID is provided
        if (isset($_GET['id'])) {
            $where[] = "id = ?";
            $params[] = $_GET['id'];
        }
        
        // Filter by team
        if (isset($_GET['team'])) {
            $where[] = "player_team_initials = ?";
            $params[] = $_GET['team'];
        }
        
        // Filter by country
        if (isset($_GET['country'])) {
            $where[] = "player_country_initials = ?";
            $params[] = $_GET['country'];
        }
        
        // Add where clauses if any
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by rating by default
        $orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'rating';
        $orderDirection = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
        
        // Ensure the sort column exists to prevent SQL injection
        $allowedColumns = ['rating', 'player_name', 'kills_deaths', 'headshot_percentage', 'average_combat_score', 'kills_per_round'];
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'rating';
        }
        
        $query .= " ORDER BY $orderBy IS NULL, $orderBy $orderDirection";
        
        // Check if a limit is provided
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $query .= " LIMIT ?";
            $params[] = intval($_GET['limit']);
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $players = $stmt->fetchAll();
        
        // Return players as JSON
        echo json_encode($players);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error fetching players: ' . $e->getMessage()]);
    }
}

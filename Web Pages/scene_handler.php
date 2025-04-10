<?php
header('Content-Type: application/json');

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u822894334_LoveandInfo');
define('DB_PASSWORD', 'Love&Info@QUEENS2025');
define('DB_NAME', 'u822894334_AnswersDB');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
    die(json_encode(["error" => "Could not connect to database. " . mysqli_connect_error()]));
}

// Set timezone to EST
date_default_timezone_set('America/New_York');

// Initialize scenes by act and depression scenes
$scenes_by_act = [
    1 => [
        'fixed' => ['Secret' => 0],  // First position
        'scenes' => ["Census", "Fan", "Torture", "Lab", "Sleep", "Remote"]
    ],
    2 => [
        'fixed' => ['Fired' => 0],  // First position
        'scenes' => ["Irrational", "Affair", "Mother", "Message", "Grass", "Terminal"]
    ],
    3 => [
        'fixed' => [
            'Star' => 0,           // Scene 18 (first position)
            'Recluse' => 1,        // Scene 19 (second position)
            'The Child Who Didnt Know Fear' => 2  // Scene 20 (third position)
        ],
        'scenes' => ["Schizophrenic", "Spies", "Dream", "God's Voice"]
    ],
    4 => [
        'fixed' => ['Piano' => 0],  // First position
        'scenes' => ["Wedding Video", "Savant", "Ex", "Memory House", "Dinner", "Flashback"]
    ],
    5 => [
        'fixed' => ['Maths' => 0],  // First position
        'scenes' => ["Linguist", "Sex", "God", "Rash", "Children", "Shrink"]
    ],
    6 => [
        'fixed' => ['Wife' => 0],  // First position
        'scenes' => ["The Child Who Didn't Know Sorry", "Climate", "Censor", "Decision", "The Child Who Didn't Know Pain", "Earthquake"]
    ],
    7 => [
        'fixed' => ['Virtual' => 0],  // First position
        'scenes' => ["Chinese Poetry", "Manic", "Grief", "Fate", "Stone", "Small Thing"]
    ]
];

// Depression scene positions for each act
$depression_positions = [
    1 => [2, 6],    // Act 1: positions 3 and 7
    2 => [3],       // Act 2: position 4
    3 => [3],       // Act 3: position 4
    4 => [2, 5],    // Act 4: positions 3 and 6
    5 => [3],       // Act 5: position 4
    6 => [3],       // Act 6: position 4
    7 => [2, 5]     // Act 7: positions 3 and 6
];

# scene_handler.php
function shouldUpdate($last_update_time) {
    if (!$last_update_time) return true;
    
    $current_time = new DateTime();
    $last_update = new DateTime($last_update_time);
    $target_hour = 16; // 4 PM EST
    
    if ($current_time->format('Y-m-d') === $last_update->format('Y-m-d') && 
        $current_time->format('H') >= $target_hour && 
        $last_update->format('H') < $target_hour) {
        error_log("Same day, after 4 PM, last update before 4 PM - update needed");
        return true;
    }
    
    // If it's a different day and we haven't updated today
    if ($current_time->format('Y-m-d') !== $last_update->format('Y-m-d')) {
        if ($current_time->format('H') >= $target_hour) {
            error_log("Different day, past 4 PM - update needed");
            return true;
        }
        
        if ($last_update->format('H') < $target_hour) {
            error_log("Different day, last update was before 4 PM - update needed");
            return true;
        }
    }
    
    error_log("No update needed - current time: " . $current_time->format('Y-m-d H:i:s') . 
              ", last update: " . $last_update->format('Y-m-d H:i:s'));
    return false;
}

function generateActOrder($act_number, $act_data, &$depression_index) {
    global $depression_positions;
    
    // Calculate exact number of scenes needed for this act
    $scene_count = match($act_number) {
        1 => 9,  // 7 regular + 2 depression
        2 => 8,  // 7 regular + 1 depression
        3 => 8,  // 7 regular + 1 depression
        4 => 9,  // 7 regular + 2 depression
        5 => 8,  // 7 regular + 1 depression
        6 => 8,  // 7 regular + 1 depression
        7 => 10, // 7 regular + 2 depression + Facts
        default => 7
    };
    
    // Initialize the final array with exact size
    $final_scenes = array_fill(0, $scene_count, null);
    
    // Place fixed scenes first
    foreach ($act_data['fixed'] as $scene => $position) {
        $final_scenes[$position] = $scene;
    }
    
    // Place depression scenes
    if (isset($depression_positions[$act_number])) {
        foreach ($depression_positions[$act_number] as $position) {
            $final_scenes[$position] = "Depression " . ($depression_index + 1);
            $depression_index++;
        }
    }
    
    // Shuffle remaining scenes
    $remaining_scenes = $act_data['scenes'];
    shuffle($remaining_scenes);
    
    // Fill in null positions with remaining scenes
    $remaining_index = 0;
    for ($i = 0; $i < count($final_scenes); $i++) {
        if ($final_scenes[$i] === null) {
            if ($remaining_index < count($remaining_scenes)) {
                $final_scenes[$i] = $remaining_scenes[$remaining_index];
                $remaining_index++;
            }
        }
    }
    
    // Add Facts as the last scene of Act 7
    if ($act_number === 7) {
        $final_scenes[count($final_scenes) - 1] = "Facts";
    }
    
    return $final_scenes;
}

function generateNewOrder() {
    global $scenes_by_act;
    $full_order = array();
    $depression_index = 0;

    foreach ($scenes_by_act as $act_number => $act_data) {
        $act_order = generateActOrder($act_number, $act_data, $depression_index);
        $full_order = array_merge($full_order, $act_order);
    }

    return $full_order;
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // First, try to get existing order
    $sql = "SELECT * FROM scene_orders ORDER BY update_time DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    $need_update = shouldUpdate($row ? $row['update_time'] : null);
    error_log("Need update: " . ($need_update ? "yes" : "no"));
    
    if ($need_update) {
        try {
            mysqli_begin_transaction($conn);
            
            $new_order = generateNewOrder();
            $order_json = json_encode($new_order);
            
            // Store update time in variable for consistency
            $current_time = date('Y-m-d H:i:s');
            
            // Clear old orders and insert new order
            mysqli_query($conn, "TRUNCATE TABLE scene_orders");
            $sql = "INSERT INTO scene_orders (order_list, update_time) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ss', $order_json, $current_time);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to insert new order");
            }
            
            mysqli_commit($conn);
            
            echo json_encode([
                'order' => $new_order,
                'last_update' => $current_time,
                'need_update' => false,
                'status' => 'updated'
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            error_log("Transaction failed: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to update order']);
        }
    } else {
        // No update needed, return existing data
        echo json_encode([
            'order' => json_decode($row['order_list'], true),
            'last_update' => $row['update_time'],
            'need_update' => false,
            'status' => 'existing'
        ]);
    }
}

mysqli_close($conn);
?>
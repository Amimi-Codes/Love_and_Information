<?php
header('Content-Type: application/json');

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u822894334_LoveandInfo');
define('DB_PASSWORD', 'Love&Info');
define('DB_NAME', 'u822894334_AnswersDB');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
    die(json_encode(["error" => "Could not connect to database. " . mysqli_connect_error()]));
}

// Set timezone to EST
date_default_timezone_set('America/New_York');

// Scene traits mapping with specific scores
$scene_traits = [
    // Act 1
    'Lab' => 'IE',
    'Census' => 'IE',
    'Remote' => 'DN',
    'Fan' => 'DN',
    'Torture' => 'SO',
    'Sleep' => 'SO',
    // Act 2
    'Terminal' => 'DN',
    'Message' => 'DN',
    'Grass' => 'IE',
    'Mother' => 'SO',
    'Affair' => 'SO',
    'Irrational' => 'SO',
    // Act 3
    'God\'s Voice' => 'SO',
    'Spies' => 'SO',
    'Recluse' => 'DN',
    'Schizophrenic' => 'SO',
    'Dream' => 'DN',
    "The Child Who Didn't Know Fear" => 'IE',
    // Act 4
    'Savant' => 'IE',
    'Ex' => 'DN',
    'Memory House' => 'IE',
    'Wedding Video' => 'DN',
    'Piano' => 'DN',
    'Dinner' => 'SO',
    'Flashback' => 'DN',    
    // Act 5
    'Linguist' => 'IE',
    'Sex' => 'DN',
    'Rash' => 'DN',
    'God' => 'SO',
    'Children' => 'SO',
    'Shrink' => 'SO',
    // Act 6
    'Climate' => 'IE',
    'Decision' => 'SO',
    'Censor' => 'SO',
    'Earthquake' => 'DN',
    "The Child Who Didn't Know Sorry" => 'SO',
    "The Child Who Didn't Know Pain" => 'SO',
    // Act 7
    'Chinese Poetry' => 'IE',
    'Virtual' => 'DN',
    'Fate' => 'SO',
    'Stone' => 'SO',
    'Grief' => 'SO',
    'Manic' => 'DN',
    'Small Thing' => 'IE'
];

// Initialize scenes by act
$scenes_by_act = [
    1 => [
        'fixed' => ['Secret' => 0],
        'scenes' => ["Census", "Fan", "Torture", "Lab", "Sleep", "Remote"]
    ],
    2 => [
        'fixed' => ['Fired' => 0],
        'scenes' => ["Irrational", "Affair", "Mother", "Message", "Grass", "Terminal"]
    ],
    3 => [
        'fixed' => ['Star' => 0, 'Recluse' => 1, "The Child Who Didn't Know Fear" => 2],
        'scenes' => ["Schizophrenic", "Spies", "Dream", "God's Voice"]
    ],
    4 => [
        'fixed' => ['Piano' => 0],
        'scenes' => ["Wedding Video", "Savant", "Ex", "Memory House", "Dinner", "Flashback"]
    ],
    5 => [
        'fixed' => ['Maths' => 0],
        'scenes' => ["Linguist", "Sex", "God", "Rash", "Children", "Shrink"]
    ],
    6 => [
        'fixed' => ['Wife' => 0],
        'scenes' => ["The Child Who Didn't Know Sorry", "Climate", "Censor", "Decision", "The Child Who Didn't Know Pain", "Earthquake"]
    ],
    7 => [
        'fixed' => ['Virtual' => 0],
        'scenes' => ["Chinese Poetry", "Manic", "Grief", "Fate", "Stone", "Small Thing"]
    ]
];

$depression_positions = [
    1 => [2, 6],    // Act 1: positions 3 and 7
    2 => [3],       // Act 2: position 4
    3 => [3],       // Act 3: position 4
    4 => [2, 5],    // Act 4: positions 3 and 6
    5 => [3],       // Act 5: position 4
    6 => [3],       // Act 6: position 4
    7 => [2, 5]     // Act 7: positions 3 and 6
];

# get everage for each trait
function getTraitAverages($conn, $date) {
    $sql = "SELECT AVG(IE) as IE_avg, AVG(DN) as DN_avg, AVG(SO) as SO_avg 
            FROM quiz_responses WHERE DATE(date) = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $date);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function getDominantTrait($averages) {
    $max = max($averages['IE_avg'], $averages['DN_avg'], $averages['SO_avg']);
    return $max == $averages['IE_avg'] ? 'IE' : ($max == $averages['DN_avg'] ? 'DN' : 'SO');
}

function getWeightedAverages($averages, $previous_dominant) {
    $weights = [
        'IE' => $previous_dominant == 'DN' ? 0.85 : ($previous_dominant == 'IE' ? 0.5 : 0.55),
        'DN' => $previous_dominant == 'DN' ? 0.85 : ($previous_dominant == 'IE' ? 0.52 : 0.52),
        'SO' => $previous_dominant == 'DN' ? 1.2 : ($previous_dominant == 'IE' ? 1.2 : 1)
    ];
    
    return array_combine(
        ['IE_avg', 'DN_avg', 'SO_avg'],
        array_map(function($trait, $weight) use ($averages) {
            return $averages[$trait.'_avg'] * $weight;
        }, array_keys($weights), $weights)
    );
}

# compare scenes for sorting
function compareScenesByTrait($a, $b, $trait, $scene_traits) {
    return $scene_traits[$b][$trait] <=> $scene_traits[$a][$trait];
}
function generateActOrder($act_number, $act_data, &$depression_index, $conn, $date) {
    global $depression_positions, $scene_traits;
    
    // Get averages and weighted values
    $averages = getTraitAverages($conn, $date);
    
    if ($date != '2025-03-05') {
        $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
        $prev_averages = getTraitAverages($conn, $prev_date);
        $prev_dominant = getDominantTrait($prev_averages);
        $averages = getWeightedAverages($averages, $prev_dominant);
    }
    
    // Sort traits by their weighted averages
    $trait_scores = [
        'IE' => $averages['IE_avg'],
        'DN' => $averages['DN_avg'],
        'SO' => $averages['SO_avg']
    ];
    arsort($trait_scores); // Sort in descending order
    $trait_order = array_keys($trait_scores);
    
    // Initialize array
    $scene_count = match($act_number) {
        1 => 9, 2 => 8, 3 => 8, 4 => 9, 5 => 8, 6 => 8, 7 => 10, default => 7
    };
    $final_scenes = array_fill(0, $scene_count, null);
    
    // Place fixed scenes and depression scenes
    foreach ($act_data['fixed'] as $scene => $position) {
        $final_scenes[$position] = $scene;
    }
    if (isset($depression_positions[$act_number])) {
        foreach ($depression_positions[$act_number] as $position) {
            $final_scenes[$position] = "Depression " . ($depression_index + 1);
            $depression_index++;
        }
    }
    
    // Group scenes by their dominant trait
    $scenes_by_trait = [
        'IE' => [],
        'DN' => [],
        'SO' => []
    ];
    
    foreach ($act_data['scenes'] as $scene) {
        if (isset($scene_traits[$scene])) {
            $scenes_by_trait[$scene_traits[$scene]][] = $scene;
        }
    }
    
    // Shuffle each group
    shuffle($scenes_by_trait['IE']);
    shuffle($scenes_by_trait['DN']);
    shuffle($scenes_by_trait['SO']);
    
    // Order scenes based on weighted averages
    $ordered_scenes = array_merge(
        $scenes_by_trait[$trait_order[0]], // Highest weighted average trait
        $scenes_by_trait[$trait_order[1]], // Second highest
        $scenes_by_trait[$trait_order[2]]  // Lowest
    );
    
    // Fill remaining positions
    $remaining_positions = array_keys(array_filter($final_scenes, function($v) {  return $v === null; }));
    foreach ($remaining_positions as $i => $position) {
        if (isset($ordered_scenes[$i])) {
            $final_scenes[$position] = $ordered_scenes[$i];
        }
    }
    
    // Add Facts as last scene of Act 7
    if ($act_number === 7) {
        $final_scenes[count($final_scenes) - 1] = "Facts";
    }
    
    return $final_scenes;
}


function generateNewOrder($conn, $date) {
    global $scenes_by_act;
    $full_order = array();
    $depression_index = 0;

    foreach ($scenes_by_act as $act_number => $act_data) {
        $act_order = generateActOrder($act_number, $act_data, $depression_index, $conn, $date);
        $full_order = array_merge($full_order, $act_order);
    }

    return $full_order;
}

// check if date is Sunday
function isSunday($date) {
    return date('w', strtotime($date)) == 0;
}

// get the target hour for a specific date
function getTargetHour($date) {
    // If it's March 9th or 16th (Sundays), return 10AM
    if (in_array($date, ['2025-03-09', '2025-03-16'])) {
        return 10;
    }
    // Otherwise return 4PM
    return 16;
}

function shouldUpdate($last_update_time) {
    if (!$last_update_time) return true;
    
    $current_time = new DateTime();
    $last_update = new DateTime($last_update_time);
    $current_date = $current_time->format('Y-m-d');
    $target_hour = getTargetHour($current_date);
    
    // Check if it's past target hour and we haven't updated today
    if ($current_time->format('Y-m-d') === $last_update->format('Y-m-d') && 
        $current_time->format('H') >= $target_hour && 
        $last_update->format('H') < $target_hour) {
        return true;
    }
    
    // Check if it's a new day
    if ($current_time->format('Y-m-d') !== $last_update->format('Y-m-d')) {
        if ($current_time->format('H') >= $target_hour) {
            return true;
        }
        if ($last_update->format('H') < $target_hour) {
            return true;
        }
    }
    
    return false;
}


// In your GET request handler:
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $current_time = date('H:i:s');
        $current_date = date('Y-m-d');
        $target_hour = getTargetHour($current_date);
        
        // Check if we need to update
        $sql = "SELECT update_time FROM scene_orders WHERE DATE(update_time) = ? ORDER BY update_time DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $current_date);
        mysqli_stmt_execute($stmt);
        $last_update = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        $target_time = sprintf("%02d:00:00", $target_hour);
        $need_update = !$last_update || 
                      ($current_time >= $target_time && substr($last_update['update_time'], 11) < $target_time);
        
        if ($need_update) {
            mysqli_begin_transaction($conn);
            
            // Delete all existing orders
            mysqli_query($conn, "DELETE FROM scene_orders");
            
            // Generate new orders for all dates
            for ($date = '2025-03-05'; $date <= '2025-03-16'; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
                // Get averages for this date
                $averages = getTraitAverages($conn, $date);
                
                // Apply weights if after March 5th
                if ($date > '2025-03-05') {
                    $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
                    $prev_averages = getTraitAverages($conn, $prev_date);
                    $prev_dominant = getDominantTrait($prev_averages);
                    $averages = getWeightedAverages($averages, $prev_dominant);
                }
                
                $dominant_trait = getDominantTrait($averages);
                $new_order = generateNewOrder($conn, $date);
                
                // Set update time based on the date
                $update_hour = getTargetHour($date);
                $update_time = $date . ' ' . sprintf("%02d:00:00", $update_hour);
                
                // Insert new order
                $sql = "INSERT INTO scene_orders (order_list, update_time, dominant_trait, IE_avg, DN_avg, SO_avg) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'sssddd', 
                    json_encode($new_order), 
                    $update_time,
                    $dominant_trait,
                    $averages['IE_avg'],
                    $averages['DN_avg'],
                    $averages['SO_avg']
                );
                mysqli_stmt_execute($stmt);
            }
            
            mysqli_commit($conn);
        }
        
        // Return current date's order
        $sql = "SELECT * FROM scene_orders WHERE DATE(update_time) = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $current_date);
        mysqli_stmt_execute($stmt);
        $current_order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        echo json_encode([
            'order' => json_decode($current_order['order_list'], true),
            'weighted_averages' => [
                'IE_avg' => $current_order['IE_avg'],
                'DN_avg' => $current_order['DN_avg'],
                'SO_avg' => $current_order['SO_avg']
            ],
            'dominant_trait' => $current_order['dominant_trait'],
            'update_time' => $current_order['update_time']
        ]);
        
    } catch (Exception $e) {
        if (isset($conn)) mysqli_rollback($conn);
        error_log("Error: " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
}

mysqli_close($conn);
?>
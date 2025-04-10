<?php
header('Content-Type: application/json');

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u822894334_LoveandInfo');
define('DB_PASSWORD', 'Love&Info@QUEENS2025');
define('DB_NAME', 'u822894334_AnswersDB');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    die(json_encode(["error" => "Could not connect to database. " . mysqli_connect_error()]));
}

date_default_timezone_set('America/New_York');

// Trait scoring
$scene_traits = [
    'Lab' => 'IE', 'Census' => 'IE', 'Remote' => 'DN', 'Fan' => 'DN', 'Torture' => 'SO', 'Sleep' => 'SO',
    'Terminal' => 'DN', 'Message' => 'DN', 'Grass' => 'IE', 'Mother' => 'SO', 'Affair' => 'SO', 'Irrational' => 'SO',
    "God's Voice" => 'SO', 'Spies' => 'SO', 'Recluse' => 'DN', 'Schizophrenic' => 'SO', 'Dream' => 'DN',
    "The Child Who Didn't Know Fear" => 'IE',
    'Savant' => 'IE', 'Ex' => 'DN', 'Memory House' => 'IE', 'Wedding Video' => 'DN', 'Piano' => 'DN',
    'Dinner' => 'SO', 'Flashback' => 'DN',
    'Linguist' => 'IE', 'Sex' => 'DN', 'Rash' => 'DN', 'God' => 'SO', 'Children' => 'SO', 'Shrink' => 'SO',
    'Climate' => 'IE', 'Decision' => 'SO', 'Censor' => 'SO', 'Earthquake' => 'DN',
    "The Child Who Didn't Know Sorry" => 'SO', "The Child Who Didn't Know Pain" => 'SO',
    'Chinese Poetry' => 'IE', 'Virtual' => 'DN', 'Fate' => 'SO', 'Stone' => 'SO', 'Grief' => 'SO', 'Manic' => 'DN', 'Small Thing' => 'IE'
];

// Acts and scene layout
$scenes_by_act = [
    1 => ['fixed' => ['Secret' => 0], 'scenes' => ["Census", "Fan", "Torture", "Lab", "Sleep", "Remote"]],
    2 => ['fixed' => ['Fired' => 0], 'scenes' => ["Irrational", "Affair", "Mother", "Message", "Grass", "Terminal"]],
    3 => ['fixed' => ['Star' => 0, 'Recluse' => 1, "The Child Who Didn't Know Fear" => 2], 'scenes' => ["Schizophrenic", "Spies", "Dream", "God's Voice"]],
    4 => ['fixed' => ['Piano' => 0], 'scenes' => ["Wedding Video", "Savant", "Ex", "Memory House", "Dinner", "Flashback"]],
    5 => ['fixed' => ['Maths' => 0], 'scenes' => ["Linguist", "Sex", "God", "Rash", "Children", "Shrink"]],
    6 => ['fixed' => ['Wife' => 0], 'scenes' => ["The Child Who Didn't Know Sorry", "Climate", "Censor", "Decision", "The Child Who Didn't Know Pain", "Earthquake"]],
    7 => ['fixed' => ['Virtual' => 0], 'scenes' => ["Chinese Poetry", "Manic", "Grief", "Fate", "Stone", "Small Thing"]]
];

$depression_positions = [
    1 => [2, 6], 2 => [3], 3 => [3], 4 => [2, 5], 5 => [3], 6 => [3], 7 => [2, 5]
];

function getTraitAverages($conn, $date) {
    $sql = "SELECT AVG(IE) as IE_avg, AVG(DN) as DN_avg, AVG(SO) as SO_avg FROM quiz_responses WHERE DATE(date) = ?";
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
    $weights = [];

    if ($previous_dominant == 'DN') {
        $weights = ['IE' => 1.00, 'DN' => 0.85, 'SO' => 1.20];
    } elseif ($previous_dominant == 'IE') {
        $weights = ['IE' => 0.75, 'DN' => 0.52, 'SO' => 1.20];
    } elseif ($previous_dominant == 'SO') {
        $weights = ['IE' => 1.00, 'DN' => 0.52, 'SO' => 1.00];
    }

    return [
        'IE_avg' => $averages['IE_avg'] * $weights['IE'],
        'DN_avg' => $averages['DN_avg'] * $weights['DN'],
        'SO_avg' => $averages['SO_avg'] * $weights['SO']
    ];
}

function generateActOrder($act_number, $act_data, &$depression_index, $averages) {
    global $depression_positions, $scene_traits;

    $trait_scores = [
        'IE' => $averages['IE_avg'],
        'DN' => $averages['DN_avg'],
        'SO' => $averages['SO_avg']
    ];
    arsort($trait_scores);
    $trait_order = array_keys($trait_scores);

    $scene_count = match ($act_number) {
        1 => 9, 2 => 8, 3 => 8, 4 => 9, 5 => 8, 6 => 8, 7 => 10, default => 7
    };
    $final_scenes = array_fill(0, $scene_count, null);

    foreach ($act_data['fixed'] as $scene => $pos) {
        $final_scenes[$pos] = $scene;
    }

    if (isset($depression_positions[$act_number])) {
        foreach ($depression_positions[$act_number] as $pos) {
            $final_scenes[$pos] = "Depression " . ($depression_index + 1);
            $depression_index++;
        }
    }

    $scenes_by_trait = ['IE' => [], 'DN' => [], 'SO' => []];
    foreach ($act_data['scenes'] as $scene) {
        if (isset($scene_traits[$scene])) {
            $scenes_by_trait[$scene_traits[$scene]][] = $scene;
        }
    }

    foreach ($scenes_by_trait as &$group) shuffle($group);
    $ordered_scenes = array_merge(
        $scenes_by_trait[$trait_order[0]],
        $scenes_by_trait[$trait_order[1]],
        $scenes_by_trait[$trait_order[2]]
    );

    $remaining_positions = array_keys(array_filter($final_scenes, fn($v) => $v === null));
    foreach ($remaining_positions as $i => $pos) {
        if (isset($ordered_scenes[$i])) {
            $final_scenes[$pos] = $ordered_scenes[$i];
        }
    }

    if ($act_number === 7) {
        $final_scenes[count($final_scenes) - 1] = "Facts";
    }

    return $final_scenes;
}

function generateNewOrder($conn, $date, $averages) {
    global $scenes_by_act;
    $full_order = [];
    $depression_index = 0;

    foreach ($scenes_by_act as $act_number => $act_data) {
        $act_order = generateActOrder($act_number, $act_data, $depression_index, $averages);
        $full_order = array_merge($full_order, $act_order);
    }

    return $full_order;
}

// --- Main update logic ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        mysqli_begin_transaction($conn);

        // 🔁 Clear existing data
        mysqli_query($conn, "DELETE FROM scene_orders");
        
        $prev_dominant = null;
        $prev_weighted_averages = null;

        for ($date = '2025-03-05'; $date <= '2025-03-16'; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
            $averages = getTraitAverages($conn, $date);

            if ($date > '2025-03-05') {
                // Use the previously calculated weighted averages and dominant trait
                $averages = getWeightedAverages($averages, $prev_dominant);
            }

            $dominant_trait = getDominantTrait($averages);
            $new_order = generateNewOrder($conn, $date, $averages);
            
            // Save for next iteration
            $prev_dominant = $dominant_trait;
            $prev_weighted_averages = $averages;

            $sql = "INSERT INTO scene_orders (order_list, update_time, dominant_trait, IE_avg, DN_avg, SO_avg) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            $update_time = $date . ' 16:00:00';
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
        echo json_encode(["status" => "Scene orders updated successfully."]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['error' => $e->getMessage()]);
    }
}


mysqli_close($conn);
?>

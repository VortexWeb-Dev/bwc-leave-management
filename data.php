<?php
header('Content-Type: application/json');

require_once __DIR__ . "/src/RankController.php";

$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

$rankController = new RankController();

if ($userId !== null) {
    $rankings = $rankController->getUserRanking($userId, $year, $month);
} else {
    $rankings = $rankController->getRankings($year, $month);
}

$response = [
    'success' => !isset($rankings['error']),
    'data' => $rankings,
    'filters' => [
        'year' => $year,
        'month' => $month,
        'user_id' => $userId
    ],
    'timestamp' => date('Y-m-d H:i:s')
];

if (isset($rankings['error'])) {
    $response['error'] = $rankings['error'];
    unset($response['data']);
}

echo json_encode($response);

exit;
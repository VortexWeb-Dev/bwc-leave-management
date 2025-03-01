<?php
header('Content-Type: application/json');

require_once __DIR__ . "/src/LeaveController.php";

$leaveController = new LeaveController();
$leaveBalance = $leaveController->getEmployeeLeaveBalance();

echo json_encode($leaveBalance);
<?php
require_once __DIR__ . "/../crest/crest.php";

class LeaveController
{
    private $cacheFile;
    private $cacheDuration = 360;

    public function __construct()
    {
        $randomNumber = rand(1000, 9999);
        $this->cacheFile = "/tmp/bitrix_" . $randomNumber . ".json";
    }

    public function getEmployeeLeaveBalance()
    {
        if ($this->isCacheValid()) {
            return $this->getCache();
        }

        $employees = CRest::call('user.get', [
            'filter' => [
                'ACTIVE' => true,
                '!=ID' => [9, 11, 67]
            ]
        ]);

        $leaveData = [];
        $annualLeaveLimit = 24;

        if (!empty($employees['result'])) {
            foreach ($employees['result'] as $employee) {
                $employeeId = $employee['ID'];
                $employeeName = trim($employee['NAME'] . ' ' . $employee['LAST_NAME']);

                $leaveRecords = CRest::call('timeman.timecontrol.reports.get', [
                    'USER_ID' => $employeeId
                ]);

                $leaveTaken = !empty($leaveRecords['result']) ? count($leaveRecords['result']['report']['days']) : 0;

                $leaveData[] = [
                    'id' => $employeeId,
                    'name' => $employeeName,
                    'leave_taken' => $leaveTaken,
                    'remaining_leave' => max(0, $annualLeaveLimit - $leaveTaken)
                ];
            }
        }

        $this->setCache($leaveData);
        return $leaveData;
    }

    private function isCacheValid()
    {
        return file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile)) < $this->cacheDuration;
    }

    private function getCache()
    {
        return json_decode(file_get_contents($this->cacheFile), true);
    }

    private function setCache($data)
    {
        file_put_contents($this->cacheFile, json_encode($data));
    }
}

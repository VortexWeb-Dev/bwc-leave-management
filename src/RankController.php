<?php
require_once __DIR__ . "/../crest/crest.php";

date_default_timezone_set('Asia/Dubai');

class RankController
{
    private $cacheFile;
    private $cacheDuration = 180;

    public function __construct()
    {
        $this->cacheFile = "/tmp/bitrix_rankings_cache.json";
    }

    private function getCacheKey($year = null, $month = null)
    {
        return "rankings_" . ($year ?? 'all') . "_" . ($month ?? 'all');
    }

    private function isCacheValid($key)
    {
        $file = $this->cacheFile;
        if (!file_exists($file)) {
            return false;
        }

        $cache = json_decode(file_get_contents($file), true);
        return isset($cache[$key]) &&
            isset($cache[$key]['timestamp']) &&
            (time() - $cache[$key]['timestamp'] < $this->cacheDuration);
    }

    private function getCache($key)
    {
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        return $cache[$key]['data'] ?? null;
    }

    private function setCache($key, $data)
    {
        $file = $this->cacheFile;
        $cache = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $cache[$key] = [
            'timestamp' => time(),
            'data' => $data
        ];
        file_put_contents($file, json_encode($cache));
    }

    public function getRankings($year = null, $month = null)
    {
        $cacheKey = $this->getCacheKey($year, $month);

        if ($this->isCacheValid($cacheKey)) {
            return $this->getCache($cacheKey);
        }

        $usersResult = CRest::call('user.get', [
            'filter' => ['ACTIVE' => 'Y', '!=ID' => [9, 11, 67]]
        ]);

        if (!$usersResult || !isset($usersResult['result'])) {
            return ['error' => 'Failed to fetch users: ' . ($usersResult['error_description'] ?? 'Unknown error')];
        }

        $users = $usersResult['result'];
        $rankings = [];
        $userDealsData = [];

        $filter = ["STAGE_ID" => "FINAL_INVOICE"];

        if ($year !== null) {
            if ($month !== null) {
                $startDate = date('Y-m-d H:i:s', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));
                $endDate = date('Y-m-t H:i:s', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));
            } else {
                $startDate = date('Y-m-d H:i:s', strtotime($year . '-01-01'));
                $endDate = date('Y-m-d H:i:s', strtotime($year . '-12-31 23:59:59'));
            }

            $filter['>CLOSEDATE'] = $startDate;
            $filter['<CLOSEDATE'] = $endDate;
        }

        foreach ($users as $user) {
            $userId = $user['ID'];
            $userDealsData[$userId] = [
                'count' => 0,
                'value' => 0,
                'deals' => []
            ];
        }

        $start = 0;
        $batchSize = 50;
        $totalDeals = [];

        do {
            $dealsResult = CRest::call('crm.deal.list', [
                'filter' => $filter,
                'select' => ['ID', 'ASSIGNED_BY_ID', 'TITLE', 'OPPORTUNITY', 'CLOSEDATE'],
                'start' => $start,
                'limit' => $batchSize
            ]);

            if (!$dealsResult || !isset($dealsResult['result'])) {
                return ['error' => 'Failed to fetch deals: ' . ($dealsResult['error_description'] ?? 'Unknown error')];
            }

            $deals = $dealsResult['result'];
            $totalDeals = array_merge($totalDeals, $deals);
            $start += count($deals);


            if (count($deals) < $batchSize) {
                break;
            }
        } while (count($deals) > 0);

        foreach ($totalDeals as $deal) {
            $assignedUserId = $deal['ASSIGNED_BY_ID'];


            if (!isset($userDealsData[$assignedUserId])) {
                continue;
            }

            $userDealsData[$assignedUserId]['count']++;
            $userDealsData[$assignedUserId]['value'] += floatval($deal['OPPORTUNITY']);
            $userDealsData[$assignedUserId]['deals'][] = [
                'id' => $deal['ID'],
                'title' => $deal['TITLE'],
                'value' => $deal['OPPORTUNITY'],
                'close_date' => $deal['CLOSEDATE']
            ];
        }

        foreach ($users as $user) {
            $userId = $user['ID'];

            if (!isset($userDealsData[$userId])) {
                continue;
            }

            $rankings[] = [
                'USER_ID' => $userId,
                'NAME' => $user['NAME'] . ' ' . $user['LAST_NAME'],
                'CLOSED_DEALS' => $userDealsData[$userId]['count'],
                'TOTAL_VALUE' => $userDealsData[$userId]['value'],
                'DEALS' => $userDealsData[$userId]['deals']
            ];
        }

        usort($rankings, function ($a, $b) {
            if ($b['CLOSED_DEALS'] != $a['CLOSED_DEALS']) {
                return $b['CLOSED_DEALS'] - $a['CLOSED_DEALS'];
            }

            return $b['TOTAL_VALUE'] - $a['TOTAL_VALUE'];
        });

        $rank = 1;
        $prevDeals = null;
        $prevValue = null;
        $prevRank = 1;

        foreach ($rankings as &$userRank) {

            if (
                $prevDeals !== null &&
                $prevDeals == $userRank['CLOSED_DEALS'] &&
                $prevValue == $userRank['TOTAL_VALUE']
            ) {
                $userRank['RANK'] = $prevRank;
            } else {
                $userRank['RANK'] = $rank;
                $prevRank = $rank;
            }

            $prevDeals = $userRank['CLOSED_DEALS'];
            $prevValue = $userRank['TOTAL_VALUE'];
            $rank++;


            unset($userRank['DEALS']);
        }

        $this->setCache($cacheKey, $rankings);

        return $rankings;
    }

    public function getUserRanking($userId, $year = null, $month = null)
    {
        $allRankings = $this->getRankings($year, $month);

        if (isset($allRankings['error'])) {
            return $allRankings;
        }

        foreach ($allRankings as $rank) {
            if ($rank['USER_ID'] == $userId) {
                return $rank;
            }
        }

        return ['error' => 'User not found or has no closed deals'];
    }
}

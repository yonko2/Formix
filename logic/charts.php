<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/forms.php';

function get_user_submissions_chart_data($user_id, $days_range = 30)
{

    $db = get_db();
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime("-{$days_range} days"));
    $stmt = $db->prepare("
        SELECT DATE(submission_time) as date, COUNT(*) as count
        FROM form_submissions 
        WHERE user_id = ? 
        AND DATE(submission_time) BETWEEN ? AND ?
        GROUP BY DATE(submission_time)
        ORDER BY DATE(submission_time) ASC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $date_range = [];
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    while ($current_date <= $end_date_obj) {
        $date_range[$current_date->format('Y-m-d')] = 0;
        $current_date->modify('+1 day');
    }

    foreach ($results as $row) {
        $date_range[$row['date']] = (int)$row['count'];
    }

    $labels = array_keys($date_range);
    $data = array_values($date_range);
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

function get_form_submissions_chart_data($form_id, $days_range = 30)
{

    $db = get_db();
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime("-{$days_range} days"));
    $stmt = $db->prepare("
        SELECT DATE(submission_time) as date, COUNT(*) as count
        FROM form_submissions 
        WHERE form_id = ? 
        AND DATE(submission_time) BETWEEN ? AND ?
        GROUP BY DATE(submission_time)
        ORDER BY DATE(submission_time) ASC
    ");
    $stmt->execute([$form_id, $start_date, $end_date]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $date_range = [];
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    while ($current_date <= $end_date_obj) {
        $date_range[$current_date->format('Y-m-d')] = 0;
        $current_date->modify('+1 day');
    }

    foreach ($results as $row) {
        $date_range[$row['date']] = (int)$row['count'];
    }

    $labels = array_keys($date_range);
    $data = array_values($date_range);
    return [
        'labels' => $labels,
        'data' => $data
    ];
}

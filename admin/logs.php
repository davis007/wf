<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();

// 検索パラメータ
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

// WHERE句の構築
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(r.name LIKE ? OR r.email LIKE ? OR d.subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status) && in_array($status, ['success', 'failed'])) {
    $where[] = "d.status = ?";
    $params[] = $status;
}

if (!empty($date_from)) {
    $where[] = "DATE(d.sent_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where[] = "DATE(d.sent_at) <= ?";
    $params[] = $date_to;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

// 総件数取得
$count_sql = "SELECT COUNT(*) as count FROM delivery_logs d LEFT JOIN recipients r ON d.recipient_id = r.id $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_count = $stmt->fetch()['count'];
$total_pages = ceil($total_count / $limit);

// ログ一覧取得
$sql = "SELECT d.*, r.name, r.email FROM delivery_logs d LEFT JOIN recipients r ON d.recipient_id = r.id $where_clause ORDER BY d.sent_at DESC LIMIT ? OFFSET ?";
$all_params = array_merge($params, [$limit, $offset]);
$stmt = $db->prepare($sql);
$stmt->execute($all_params);
$logs = $stmt->fetchAll();

// 統計情報
$stats_sql = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM delivery_logs";
$stats = $db->query($stats_sql)->fetch();

// テンプレート変数を配列にまとめる
$template_vars = [
    'search' => $search,
    'status' => $status,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'page' => $page,
    'total_count' => $total_count,
    'total_pages' => $total_pages,
    'logs' => $logs,
    'stats' => $stats,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/logs_template.php';
?>
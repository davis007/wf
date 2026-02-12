<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();

// 統計情報取得
$recipients_count = $db->query("SELECT COUNT(*) as count FROM recipients")->fetch()['count'];
$subscribed_count = $db->query("SELECT COUNT(*) as count FROM recipients WHERE subscribed = 1")->fetch()['count'];
$delivery_count = $db->query("SELECT COUNT(*) as count FROM delivery_logs")->fetch()['count'];
$recent_deliveries = $db->query("SELECT d.*, r.name, r.email FROM delivery_logs d LEFT JOIN recipients r ON d.recipient_id = r.id ORDER BY d.sent_at DESC LIMIT 5")->fetchAll();

// テンプレート変数を配列にまとめる
$template_vars = [
    'recipients_count' => $recipients_count,
    'subscribed_count' => $subscribed_count,
    'delivery_count' => $delivery_count,
    'recent_deliveries' => $recent_deliveries,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/dashboard_template.php';
?>
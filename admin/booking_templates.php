<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();
$error = '';
$success = '';

// テンプレート更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    error_log("POST DEBUG: type={$type}, subject={$subject}, csrf_token={$csrf_token}");

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($subject) || empty($content)) {
        $error = '件名と本文は必須です';
    } elseif (!in_array($type, ['normal', 'night_battle', 'rental'])) {
        $error = '無効なテンプレート種別です: ' . htmlspecialchars($type);
    } else {
        try {
            $stmt = $db->prepare("UPDATE booking_templates SET subject = ?, content = ?, updated_at = datetime('now') WHERE type = ?");
            $stmt->execute([$subject, $content, $type]);
            $success = 'テンプレートを更新しました';
        } catch (PDOException $e) {
            $error = 'データベースエラー: ' . $e->getMessage();
        }
    }
}

// テンプレート取得
$stmt = $db->prepare("SELECT * FROM booking_templates ORDER BY type ASC");
$stmt->execute();
$templates = $stmt->fetchAll();

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'templates' => $templates,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
$current_page = 'booking_templates';
$page_title = '予約定型メール管理 - WEST FIELD 管理パネル';
include_once 'templates/booking_templates_template.php';
?>

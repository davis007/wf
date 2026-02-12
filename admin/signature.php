<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();
$error = '';
$success = '';

// 署名を取得
$stmt = $db->prepare("SELECT content FROM signature ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$signature = $stmt->fetch()['content'] ?? '';

// 署名更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($content)) {
        $error = '署名を入力してください';
    } else {
        $stmt = $db->prepare("INSERT INTO signature (content) VALUES (?)");
        $stmt->execute([$content]);
        $success = '署名を更新しました';
        $signature = $content;
    }
}

// 署名履歴を取得
$stmt = $db->prepare("SELECT content, created_at FROM signature ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$history = $stmt->fetchAll();

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'signature' => $signature,
    'history' => $history,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/signature_template.php';
?>
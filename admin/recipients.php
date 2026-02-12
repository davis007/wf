<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();
$error = '';
$success = '';

// 送信先追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($name) || empty($email)) {
        $error = '名前とメールアドレスは必須です';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '有効なメールアドレスを入力してください';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO recipients (name, email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
            $success = '送信先を追加しました';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $error = 'このメールアドレスは既に登録されています';
            } else {
                $error = 'データベースエラー: ' . $e->getMessage();
            }
        }
    }
}

// 送信先編集
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subscribed = isset($_POST['subscribed']) ? 1 : 0;
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif ($id <= 0) {
        $error = '無効なIDです';
    } elseif (empty($name) || empty($email)) {
        $error = '名前とメールアドレスは必須です';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '有効なメールアドレスを入力してください';
    } else {
        try {
            $stmt = $db->prepare("UPDATE recipients SET name = ?, email = ?, subscribed = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([$name, $email, $subscribed, $id]);
            $success = '送信先を更新しました';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $error = 'このメールアドレスは既に登録されています';
            } else {
                $error = 'データベースエラー: ' . $e->getMessage();
            }
        }
    }
}

// 送信先削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif ($id <= 0) {
        $error = '無効なIDです';
    } else {
        $stmt = $db->prepare("DELETE FROM recipients WHERE id = ?");
        $stmt->execute([$id]);
        $success = '送信先を削除しました';
    }
}

// 送信先一覧取得
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE name LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $db->prepare("SELECT COUNT(*) as count FROM recipients $where");
$stmt->execute($params);
$total_count = $stmt->fetch()['count'];
$total_pages = ceil($total_count / $limit);

$stmt = $db->prepare("SELECT * FROM recipients $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($params, [$limit, $offset]);
$stmt->execute($all_params);
$recipients = $stmt->fetchAll();

// 編集モードの送信先取得
$edit_id = $_GET['edit'] ?? 0;
$edit_recipient = null;
if ($edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM recipients WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_recipient = $stmt->fetch();
}

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'recipients' => $recipients,
    'edit_recipient' => $edit_recipient,
    'search' => $search,
    'page' => $page,
    'total_count' => $total_count,
    'total_pages' => $total_pages,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/recipients_template.php';
?>
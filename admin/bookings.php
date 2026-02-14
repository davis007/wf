<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();
$error = '';
$success = '';

// メール送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_email') {
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($email) || empty($subject) || empty($body)) {
        $error = '送信先、件名、本文はすべて必須です';
    } else {
        require_once 'mailer.php';
        if (send_email($email, 'お客様', $subject, $body)) {
            $success = 'メールを送信しました';
        } else {
            $error = 'メール送信に失敗しました';
        }
    }
}

// 予約削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif ($id <= 0) {
        $error = '無効なIDです';
    } else {
        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $success = '予約を削除しました';
    }
}

// 一覧取得と検索
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE name_kanji LIKE ? OR email LIKE ? OR team_name LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings $where");
$stmt->execute($params);
$total_count = $stmt->fetch()['count'];
$total_pages = ceil($total_count / $limit);

$stmt = $db->prepare("SELECT * FROM bookings $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($params, [$limit, $offset]);
$stmt->execute($all_params);
$bookings = $stmt->fetchAll();

// 特定の予約詳細取得（表示用）
$view_id = $_GET['view'] ?? 0;
$view_booking = null;
if ($view_id > 0) {
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$view_id]);
    $view_booking = $stmt->fetch();
}

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'bookings' => $bookings,
    'view_booking' => $view_booking,
    'search' => $search,
    'page' => $page,
    'total_count' => $total_count,
    'total_pages' => $total_pages,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
$current_page = 'bookings';
$page_title = '予約管理 - WEST FIELD 管理パネル';
include_once 'templates/bookings_template.php';
?>

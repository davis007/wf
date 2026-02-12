<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();

// 送信先データを取得
$stmt = $db->prepare("SELECT name, email, subscribed, created_at FROM recipients ORDER BY created_at DESC");
$stmt->execute();
$recipients = $stmt->fetchAll();

// CSVヘッダーを設定
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=recipients_' . date('Ymd_His') . '.csv');

// 出力ストリームを開く
$output = fopen('php://output', 'w');

// BOMを追加（Excel用）
fwrite($output, "\xEF\xBB\xBF");

// ヘッダー行を書き込み
fputcsv($output, ['名前', 'メールアドレス', '購読状態', '登録日時']);

// データ行を書き込み
foreach ($recipients as $recipient) {
    $subscribed_status = $recipient['subscribed'] ? '購読中' : '停止中';
    $created_at = date('Y/m/d H:i:s', strtotime($recipient['created_at']));

    fputcsv($output, [
        $recipient['name'],
        $recipient['email'],
        $subscribed_status,
        $created_at
    ]);
}

fclose($output);
exit;
?>
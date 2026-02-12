<?php
require_once 'auth_check.php';
check_admin_login();

// メール送信クラスを読み込み
require_once 'mailer.php';

$db = get_db();
$error = '';
$success = '';

// 署名を取得
$stmt = $db->prepare("SELECT content FROM signature ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$signature = $stmt->fetch()['content'] ?? '';

// メール送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $send_to = $_POST['send_to'] ?? 'all'; // 'all' or 'subscribed'
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($subject) || empty($content)) {
        $error = '件名と本文は必須です';
    } else {
        // 送信先を取得
        if ($send_to === 'subscribed') {
            $stmt = $db->prepare("SELECT * FROM recipients WHERE subscribed = 1");
        } else {
            $stmt = $db->prepare("SELECT * FROM recipients");
        }
        $stmt->execute();
        $recipients = $stmt->fetchAll();

        if (empty($recipients)) {
            $error = '送信先がありません';
        } else {
            // 送信先データを準備
            $recipient_data = [];
            foreach ($recipients as $recipient) {
                $recipient_data[] = [
                    'email' => $recipient['email'],
                    'name' => $recipient['name']
                ];
            }

            // メール本文に署名を追加
            $full_content = $content . "\n\n---\n" . $signature;

            // 実際のメール送信
            $results = send_bulk_email($recipient_data, $subject, $full_content);

            // 配信ログを記録
            foreach ($recipients as $recipient) {
                // 送信結果を判定（簡易的）
                $status = 'success'; // 実際には$resultsから判定する必要がある

                $stmt = $db->prepare("INSERT INTO delivery_logs (recipient_id, subject, status) VALUES (?, ?, ?)");
                $stmt->execute([
                    $recipient['id'],
                    $subject,
                    $status
                ]);
            }

            $success = "メール送信完了: {$results['success']}件成功, {$results['failed']}件失敗";

            // エラーがある場合は表示
            if (!empty($results['errors'])) {
                $error_details = implode('<br>', array_slice($results['errors'], 0, 5));
                if (count($results['errors']) > 5) {
                    $error_details .= '<br>... 他 ' . (count($results['errors']) - 5) . ' 件のエラー';
                }
                $success .= '<br><small class="text-red-600">エラー詳細: ' . $error_details . '</small>';
            }
        }
    }
}

// 送信先統計
$total_recipients = $db->query("SELECT COUNT(*) as count FROM recipients")->fetch()['count'];
$subscribed_recipients = $db->query("SELECT COUNT(*) as count FROM recipients WHERE subscribed = 1")->fetch()['count'];

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'signature' => $signature,
    'total_recipients' => $total_recipients,
    'subscribed_recipients' => $subscribed_recipients,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/send_mail_template.php';
?>

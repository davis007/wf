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
            $sent_count = 0;
            $failed_count = 0;

            // メール送信（実際の実装ではメール送信機能を使用）
            // ここではデータベースにログを記録するだけ
            foreach ($recipients as $recipient) {
                $full_content = $content . "\n\n---\n" . $signature;

                // 実際のメール送信処理（簡易版）
                $mail_sent = true; // 仮の成功

                // 配信ログを記録
                $stmt = $db->prepare("INSERT INTO delivery_logs (recipient_id, subject, status) VALUES (?, ?, ?)");
                $stmt->execute([
                    $recipient['id'],
                    $subject,
                    $mail_sent ? 'success' : 'failed'
                ]);

                if ($mail_sent) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }
            }

            $success = "メール送信完了: {$sent_count}件成功, {$failed_count}件失敗";
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
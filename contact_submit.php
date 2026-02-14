<?php
/**
 * お問い合わせフォーム送信処理
 */

// まずエラー表示を無効にする（config.phpより先に実行）
ini_set('display_errors', 0);
error_reporting(E_ALL);

// エラーログをファイルに出力
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/contact_submit_errors.log');

// 設定ファイル読み込み
require_once 'admin/config.php';

// config.phpでエラー表示が有効になっている可能性があるので、再度無効化
ini_set('display_errors', 0);

// 管理者メールアドレスをグローバル変数から取得
global $admin_email;

// レスポンス用ヘッダー
header('Content-Type: application/json; charset=utf-8');

// エラーメッセージ定数
define('ERROR_CSRF', 'CSRFトークンが無効です。ページを再読み込みしてください。');
define('ERROR_HONEYPOT', 'スパムと判断されました。');
define('ERROR_REFERRER', '不正な送信元です。');
define('ERROR_TOO_FAST', '送信が早すぎます。もう少し時間を置いてから送信してください。');
define('ERROR_DUPLICATE', '同じ内容のお問い合わせは短時間内に複数回送信できません。');
define('ERROR_VALIDATION', '入力内容に誤りがあります。');
define('ERROR_SERVER', 'サーバーエラーが発生しました。しばらく経ってから再度お試しください。');
define('SUCCESS_MESSAGE', 'お問い合わせを受け付けました。ありがとうございます。');

// 送信時間チェック用の最小時間（秒）
define('MIN_SUBMIT_TIME', 3);

// 重複送信防止用の期間（秒）
define('DUPLICATE_CHECK_TIME', 300); // 5分

/**
 * リファラーチェック
 */
function check_referer() {
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return false;
    }

    $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $server = $_SERVER['HTTP_HOST'];

    // ポート番号を除去して比較
    $referer = preg_replace('/:\d+$/', '', $referer);
    $server = preg_replace('/:\d+$/', '', $server);

    return $referer === $server;
}

/**
 * Originチェック
 */
function check_origin() {
    if (!isset($_SERVER['HTTP_ORIGIN'])) {
        return isset($_SERVER['HTTP_REFERER']); // Originがない場合はRefererで判断
    }

    $origin = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
    $server = $_SERVER['HTTP_HOST'];

    // ポート番号を除去して比較
    $origin = preg_replace('/:\d+$/', '', $origin);
    $server = preg_replace('/:\d+$/', '', $server);

    return $origin === $server;
}

/**
 * 送信時間チェック
 */
function check_submit_time($page_load_time) {
    if (empty($page_load_time)) {
        return false;
    }

    $load_time = (int)$page_load_time;
    $current_time = time();
    $elapsed_time = $current_time - $load_time;

    return $elapsed_time >= MIN_SUBMIT_TIME;
}

/**
 * 重複送信チェック
 */
function check_duplicate_submission($email, $message) {
    try {
        $db = get_db();

        // テーブルが存在するか確認
        $table_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contact_submissions'")->fetch();

        if (!$table_exists) {
            // テーブルが存在しない場合は重複チェックをスキップ
            return true;
        }

        // メッセージのハッシュを計算
        $message_hash = hash('sha256', $email . $message);

        // 最近の送信をチェック
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM contact_submissions
            WHERE message_hash = ?
            AND submitted_at > datetime('now', ?)
        ");
        $stmt->execute([$message_hash, '-' . DUPLICATE_CHECK_TIME . ' seconds']);
        $result = $stmt->fetch();

        return $result['count'] == 0;
    } catch (Exception $e) {
        error_log('check_duplicate_submission error: ' . $e->getMessage());
        // エラーが発生した場合は重複チェックをスキップ
        return true;
    }
}

/**
 * 入力バリデーション
 */
function validate_input($name, $email, $message) {
    $errors = [];

    // 名前チェック
    if (empty($name) || mb_strlen($name) > 100) {
        $errors[] = '名前は1文字以上100文字以内で入力してください。';
    }

    // メールアドレスチェック
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
        $errors[] = '有効なメールアドレスを入力してください。';
    }

    // メッセージチェック
    if (empty($message) || mb_strlen($message) > 2000) {
        $errors[] = 'お問い合わせ内容は1文字以上2000文字以内で入力してください。';
    }

    return $errors;
}

/**
 * ヘッダーインジェクション対策
 */
function sanitize_headers($value) {
    // 改行文字を削除
    $value = str_replace(["\r", "\n"], '', $value);
    // 複数のスペースを単一スペースに
    $value = preg_replace('/\s+/', ' ', $value);
    // HTML特殊文字をエスケープ
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * メール送信
 */
function send_contact_email($name, $email, $message) {
    global $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;

    // 管理者メールアドレス（config.phpから取得）
    global $admin_email;

    // 件名
    $subject = '【WEST FIELD】お問い合わせがありました';

    // 本文作成
    $body = "お問い合わせがありました。\n\n";
    $body .= "■お名前\n" . $name . "\n\n";
    $body .= "■メールアドレス\n" . $email . "\n\n";
    $body .= "■お問い合わせ内容\n" . $message . "\n\n";
    $body .= "---\n";
    $body .= "送信日時: " . date('Y年m月d日 H時i分s秒') . "\n";
    $body .= "IPアドレス: " . $_SERVER['REMOTE_ADDR'] . "\n";

    // メール送信クラスを使用
    require_once 'admin/mailer.php';

    return send_email($admin_email, '管理者', $subject, $body);
}

/**
 * 送信記録をデータベースに保存
 */
function save_submission($name, $email, $message) {
    try {
        $db = get_db();

        // テーブルが存在するか確認
        $table_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contact_submissions'")->fetch();

        if (!$table_exists) {
            // テーブル作成
            $db->exec("CREATE TABLE contact_submissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                message TEXT NOT NULL,
                message_hash TEXT NOT NULL,
                ip_address TEXT NOT NULL,
                user_agent TEXT,
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $db->exec("CREATE INDEX idx_message_hash ON contact_submissions (message_hash)");
            $db->exec("CREATE INDEX idx_submitted_at ON contact_submissions (submitted_at)");
        }

        // メッセージハッシュを計算
        $message_hash = hash('sha256', $email . $message);

        // データ挿入
        $stmt = $db->prepare("
            INSERT INTO contact_submissions
            (name, email, message, message_hash, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $name,
            $email,
            $message,
            $message_hash,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('save_submission error: ' . $e->getMessage());
        return false;
    }
}

// メイン処理
try {
    // POSTリクエストのみ許可
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('不正なリクエストです。', 405);
    }

    // リファラーチェック
    if (!check_referer()) {
        throw new Exception(ERROR_REFERRER, 403);
    }

    // Originチェック
    if (!check_origin()) {
        throw new Exception(ERROR_REFERRER, 403);
    }

    // CSRFトークンチェック
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        throw new Exception(ERROR_CSRF, 403);
    }

    // ハニーポットチェック
    if (!empty($_POST['website'])) {
        throw new Exception(ERROR_HONEYPOT, 403);
    }

    // 送信時間チェック
    if (!check_submit_time($_POST['page_load_time'] ?? '')) {
        throw new Exception(ERROR_TOO_FAST, 429);
    }

    // 入力値取得とサニタイズ
    $name = sanitize_headers($_POST['name'] ?? '');
    $email = sanitize_headers($_POST['email'] ?? '');
    $message = sanitize_headers($_POST['message'] ?? '');

    // 入力バリデーション
    $validation_errors = validate_input($name, $email, $message);
    if (!empty($validation_errors)) {
        throw new Exception(ERROR_VALIDATION . ' ' . implode(' ', $validation_errors), 400);
    }

    // 重複送信チェック
    if (!check_duplicate_submission($email, $message)) {
        throw new Exception(ERROR_DUPLICATE, 429);
    }

    // データベースに保存
    try {
        if (!save_submission($name, $email, $message)) {
            error_log('Failed to save contact submission to database');
        }
    } catch (Exception $e) {
        error_log('Database error in save_submission: ' . $e->getMessage());
        // データベースエラーでも処理を続行（メール送信は試みる）
    }

    // メール送信
    $mail_sent = send_contact_email($name, $email, $message);

    if (!$mail_sent) {
        error_log('Failed to send contact email');
        // メール送信失敗でもユーザーには成功を伝える（データベースには保存済み）
    }

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => SUCCESS_MESSAGE
    ]);

} catch (Exception $e) {
    // エラーログ
    error_log('Contact form error: ' . $e->getMessage());

    // エラーレスポンス
    $error_code = $e->getCode();
    if (!is_int($error_code) || $error_code < 100 || $error_code > 599) {
        $error_code = 500;
    }
    http_response_code($error_code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
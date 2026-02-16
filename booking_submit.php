<?php
/**
 * 予約フォーム送信処理
 */

// エラー表示を無効にする
ini_set('display_errors', 0);
error_reporting(E_ALL);

// エラーログをファイルに出力
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/booking_submit_errors.log');

// 設定ファイル読み込み
require_once 'admin/config.php';

// 管理者メールアドレスをグローバル変数から取得
global $admin_email;

// レスポンス用ヘッダー
header('Content-Type: application/json; charset=utf-8');

// エラーメッセージ定数
define('ERROR_CSRF', 'CSRFトークンが無効です。ページを再読み込みしてください。');
define('ERROR_HONEYPOT', 'スパムと判断されました。');
define('ERROR_REFERRER', '不正な送信元です。');
define('ERROR_TOO_FAST', '送信が早すぎます。もう少し時間を置いてから送信してください。');
define('ERROR_VALIDATION', '入力内容に誤りがあります。');
define('ERROR_SERVER', 'サーバーエラーが発生しました。しばらく経ってから再度お試しください。');
define('SUCCESS_MESSAGE', 'ご予約を承りました。ありがとうございます。');

// 送信時間チェック用の最小時間（秒）
define('MIN_SUBMIT_TIME', 3);

/**
 * リファラー/Originチェック (contact_submit.phpと同様)
 */
function check_referer() {
    if (!isset($_SERVER['HTTP_REFERER'])) return false;
    $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $server = $_SERVER['HTTP_HOST'];
    return preg_replace('/:\d+$/', '', $referer) === preg_replace('/:\d+$/', '', $server);
}

function check_origin() {
    if (!isset($_SERVER['HTTP_ORIGIN'])) return isset($_SERVER['HTTP_REFERER']);
    $origin = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
    $server = $_SERVER['HTTP_HOST'];
    return preg_replace('/:\d+$/', '', $origin) === preg_replace('/:\d+$/', '', $server);
}

function check_submit_time($page_load_time) {
    if (empty($page_load_time)) return false;
    return (time() - (int)$page_load_time) >= MIN_SUBMIT_TIME;
}

/**
 * 入力バリデーション
 */
function validate_booking_input($data) {
    $errors = [];
    if (empty($data['name_kanji'])) $errors[] = '氏名（漢字）を入力してください。';
    if (empty($data['name_kana'])) $errors[] = '氏名（ひらがな）を入力してください。';
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = '有効なメールアドレスを入力してください。';
    if (empty($data['event_date'])) $errors[] = '参加日を入力してください。';
    if (empty($data['people']) || !is_numeric($data['people']) || $data['people'] < 1) $errors[] = 'ご利用人数を正しく入力してください。';
    return $errors;
}

/**
 * データのサニタイズ
 */
function sanitize_data($value) {
    $value = str_replace(["\r", "\n"], ' ', $value);
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * 予約情報を保存
 */
function save_booking($data) {
    try {
        $db = get_db();
        $stmt = $db->prepare("
            INSERT INTO bookings
            (booking_type, name_kanji, name_kana, email, address, tel, event_date, team_name, people, participants, pickup, pickup_people, rental_gun, rental_goggle, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['booking_type'],
            $data['name_kanji'],
            $data['name_kana'],
            $data['email'],
            $data['address'],
            $data['tel'],
            $data['event_date'],
            $data['team_name'],
            (int)$data['people'],
            $data['participants'],
            $data['pickup'],
            $data['pickup_people'],
            $data['rental_gun'],
            $data['rental_goggle'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('save_booking error: ' . $e->getMessage());
        return false;
    }
}

/**
 * ICSファイル（カレンダー用）の生成
 */
function generate_ics_content($data) {
    // 日時のフォーマット (YYYYMMDD)
    $date_str = str_replace('-', '', $data['event_date']);

    // 開始時間と終了時間（仮：定例会は10:00〜16:00、夜戦は19:00〜23:00など）
    // 正確な時間が不明なため、終日イベントとするか、一般的な時間を設定
    $start_time = "090000"; // 9:00
    $end_time = "170000";   // 17:00

    if ($data['booking_type'] === 'night_battle') {
        $start_time = "190000";
        $end_time = "230000";
    }

    $dtstart = $date_str . 'T' . $start_time;
    $dtend = $date_str . 'T' . $end_time;

    $now = gmdate('Ymd\THis\Z');
    $uid = uniqid('westfield_') . '@westfield2023.net';

    $summary = "【予約】" . $data['team_name'] . " 様 (" . $data['people'] . "名)";
    $description = "予約種別: " . ($data['booking_type'] === 'rental' ? '貸切' : ($data['booking_type'] === 'night_battle' ? '夜戦' : '定例会')) . "\\n";
    $description .= "氏名: " . $data['name_kanji'] . "\\n";
    $description .= "人数: " . $data['people'] . "名\\n";
    $description .= "電話: " . $data['tel'] . "\\n";
    $description .= "送迎: " . ($data['pickup'] === 'yes' ? '有り' : '無し');

    // ICSフォーマットの構築
    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//WEST FIELD//Booking System//JA\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";
    $ics .= "BEGIN:VEVENT\r\n";
    $ics .= "UID:{$uid}\r\n";
    $ics .= "DTSTAMP:{$now}\r\n";
    $ics .= "DTSTART;TZID=Asia/Tokyo:{$dtstart}\r\n";
    $ics .= "DTEND;TZID=Asia/Tokyo:{$dtend}\r\n";
    $ics .= "SUMMARY:{$summary}\r\n";
    $ics .= "DESCRIPTION:{$description}\r\n";
    $ics .= "LOCATION:WEST FIELD\r\n";
    $ics .= "STATUS:CONFIRMED\r\n";
    $ics .= "END:VEVENT\r\n";
    $ics .= "END:VCALENDAR\r\n";

    return $ics;
}

/**
 * メール送信
 */
function send_booking_emails($data) {
    require_once 'admin/mailer.php';
    global $admin_email;

    // ICSファイルを生成
    $ics_content = generate_ics_content($data);
    $attachments = [
        [
            'name' => 'booking_' . $data['event_date'] . '.ics',
            'content' => $ics_content,
            'type' => 'text/calendar'
        ]
    ];

    // 1. 管理者へ通知
    $admin_subject = '【WEST FIELD】新規予約がありました';
    $admin_body = "新規予約がありました。\n\n";
    $booking_type_label = '通常';
    if ($data['booking_type'] === 'night_battle') {
        $booking_type_label = '夜戦';
    } elseif ($data['booking_type'] === 'rental') {
        $booking_type_label = '貸切';
    }
    $admin_body .= "■予約種別: " . $booking_type_label . "\n";
    $admin_body .= "■お名前: " . $data['name_kanji'] . " (" . $data['name_kana'] . ")\n";
    $admin_body .= "■メール: " . $data['email'] . "\n";
    $admin_body .= "■電話番号: " . $data['tel'] . "\n";
    $admin_body .= "■参加日: " . $data['event_date'] . "\n";
    $admin_body .= "■人数: " . $data['people'] . "名\n";
    $admin_body .= "■チーム名: " . $data['team_name'] . "\n";
    $admin_body .= "■代表者: " . $data['participants'] . "\n";
    $admin_body .= "■送迎: " . ($data['pickup'] === 'yes' ? '有り' : '無し') . " (" . $data['pickup_people'] . ")\n";
    $admin_body .= "■レンタルガン: " . $data['rental_gun'] . "\n";
    $admin_body .= "■レンタルゴーグル: " . $data['rental_goggle'] . "\n";
    $admin_body .= "\n送信日時: " . date('Y-m-d H:i:s') . "\n";
    $admin_body .= "\n※添付のICSファイルを開くとカレンダーに予定を登録できます。";

    // send_emailに添付ファイルを渡す
    send_email($admin_email, '管理者', $admin_subject, $admin_body, $attachments);

    // 2. 予約者へ自動返信
    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT subject, content FROM booking_templates WHERE type = ?");
        $stmt->execute([$data['booking_type']]);
        $template = $stmt->fetch();

        if ($template) {
            $subject = $template['subject'];
            $body = $template['content'];

            // 変数置換
            $placeholders = [
                '{name}' => $data['name_kanji'],
                '{event_date}' => $data['event_date'],
                '{team_name}' => $data['team_name'],
                '{people}' => $data['people'],
                '{participants}' => $data['participants'],
                '{pickup_status}' => ($data['pickup'] === 'yes' ? "有り ({$data['pickup_people']})" : '無し'),
                '{rental_gun}' => $data['rental_gun'],
                '{rental_goggle}' => $data['rental_goggle']
            ];
            $body = str_replace(array_keys($placeholders), array_values($placeholders), $body);

            // 予約者には添付ファイルなしで送信
            send_email($data['email'], $data['name_kanji'], $subject, $body);
        }
    } catch (Exception $e) {
        error_log('Auto-reply error: ' . $e->getMessage());
    }
}

// メイン処理
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('不正なリクエストです。', 405);
    if (!check_referer() || !check_origin()) throw new Exception(ERROR_REFERRER, 403);
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) throw new Exception(ERROR_CSRF, 403);
    if (!empty($_POST['website'])) throw new Exception(ERROR_HONEYPOT, 403);
    if (!check_submit_time($_POST['page_load_time'] ?? '')) throw new Exception(ERROR_TOO_FAST, 429);

    $fields = [
        'booking_type', 'name_kanji', 'name_kana', 'email', 'address', 'tel',
        'event_date', 'team_name', 'people', 'participants', 'pickup',
        'pickup_people', 'rental_gun', 'rental_goggle'
    ];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = sanitize_data($_POST[$field] ?? '');
    }

    $validation_errors = validate_booking_input($data);
    if (!empty($validation_errors)) throw new Exception(ERROR_VALIDATION . ' ' . implode(' ', $validation_errors), 400);

    if (save_booking($data)) {
        send_booking_emails($data);
        echo json_encode(['success' => true, 'message' => SUCCESS_MESSAGE]);
    } else {
        throw new Exception(ERROR_SERVER, 500);
    }

} catch (Exception $e) {
    error_log('Booking form error: ' . $e->getMessage());
    $code = $e->getCode();
    if (!is_int($code) || $code < 100 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

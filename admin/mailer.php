<?php
/**
 * メール送信クラス
 */
class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $from_email;
    private $from_name;

    public function __construct($host, $port, $username, $password, $from_email, $from_name) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->from_email = $from_email;
        $this->from_name = $from_name;
    }

    /**
     * メールを送信する
     *
     * @param string $to 送信先メールアドレス
     * @param string $name 送信先名前
     * @param string $subject 件名
     * @param string $body 本文
     * @return bool 送信成功時true、失敗時false
     */
    public function send($to, $name, $subject, $body) {
        // メールヘッダー
        $headers = [
            'From' => mb_encode_mimeheader($this->from_name) . ' <' . $this->from_email . '>',
            'Reply-To' => $this->from_email,
            'Return-Path' => $this->from_email,
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
            'MIME-Version' => '1.0',
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        // ヘッダー文字列に変換
        $header_string = '';
        foreach ($headers as $key => $value) {
            $header_string .= $key . ': ' . $value . "\r\n";
        }

        // 件名をエンコード
        $encoded_subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");

        // 本文を正規化（CRLFに統一）
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\r", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);

        // SMTP認証が必要な場合
        if (!empty($this->username) && !empty($this->password)) {
            return $this->sendViaSMTP($to, $encoded_subject, $body, $header_string);
        }

        // 標準のmail()関数を使用
        return mail($to, $encoded_subject, $body, $header_string);
    }

    /**
     * SMTP経由でメールを送信する（簡易実装）
     * 実際の実装ではPHPMailerやSwiftMailerなどのライブラリを使用することを推奨
     */
    private function sendViaSMTP($to, $subject, $body, $headers) {
        // 簡易実装：実際のSMTP送信は外部ライブラリを使用することを推奨
        // ここではmail()関数を使用し、SMTP設定はphp.iniで行うことを想定

        error_log("SMTP送信: {$to}, 件名: {$subject}");

        // 実際の実装では以下のようなSMTP接続処理が必要：
        /*
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP接続エラー: {$errno} - {$errstr}");
            return false;
        }

        // SMTPコマンドのやり取り...
        fclose($socket);
        */

        // 暫定実装：mail()関数を使用
        return mail($to, $subject, $body, $headers);
    }

    /**
     * 一括メール送信
     *
     * @param array $recipients 送信先配列 [['email' => '', 'name' => ''], ...]
     * @param string $subject 件名
     * @param string $body 本文
     * @return array 結果 ['success' => 成功件数, 'failed' => 失敗件数, 'errors' => エラー詳細]
     */
    public function sendBulk($recipients, $subject, $body) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            $to = $recipient['email'];
            $name = $recipient['name'];

            try {
                $sent = $this->send($to, $name, $subject, $body);

                if ($sent) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "送信失敗: {$name} <{$to}>";
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "エラー: {$name} <{$to}> - " . $e->getMessage();
            }

            // 大量送信防止のため少し待機（1秒に1件程度）
            usleep(1000000); // 1秒
        }

        return $results;
    }
}

/**
 * グローバル関数：メール送信
 */
function send_email($to, $name, $subject, $body) {
    global $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;

    $mailer = new Mailer(
        $smtp_host,
        $smtp_port,
        $smtp_username,
        $smtp_password,
        $smtp_from_email,
        $smtp_from_name
    );

    return $mailer->send($to, $name, $subject, $body);
}

/**
 * グローバル関数：一括メール送信
 */
function send_bulk_email($recipients, $subject, $body) {
    global $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;

    $mailer = new Mailer(
        $smtp_host,
        $smtp_port,
        $smtp_username,
        $smtp_password,
        $smtp_from_email,
        $smtp_from_name
    );

    return $mailer->sendBulk($recipients, $subject, $body);
}
?>
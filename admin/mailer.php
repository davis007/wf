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
    /**
     * メールを送信する
     *
     * @param string $to 送信先メールアドレス
     * @param string $name 送信先名前
     * @param string $subject 件名
     * @param string $body 本文
     * @param array $attachments 添付ファイル配列 [['name' => 'filename', 'content' => 'data', 'type' => 'mime/type']]
     * @return bool|string 送信成功時true、失敗時エラーメッセージ文字列
     */
    public function send($to, $name, $subject, $body, $attachments = []) {
        // 件名をエンコード
        $encoded_subject = mb_encode_mimeheader($subject, 'UTF-8', 'B');

        // 本文を正規化（CRLFに統一）
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\r", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);

        // SMTP認証情報がある場合、またはポートがSMTP用の場合
        if (!empty($this->host) && ($this->host !== 'localhost' || !empty($this->username))) {
            return $this->sendViaSMTP($to, $name, $encoded_subject, $body, $attachments);
        }

        // それ以外は標準のmail()関数（互換性のため残す）
        // mail関数で添付ファイルを送るのは複雑なので、簡易的に非対応とするか、本来はMIME構築が必要
        // ここでは簡易実装として本文のみ送信（添付ファイルがある場合はログに警告）
        if (!empty($attachments)) {
            error_log("Warning: mail() function used but attachments provided. Attachments will be ignored.");
        }

        $headers = "From: " . mb_encode_mimeheader($this->from_name) . " <" . $this->from_email . ">\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        if (mail($to, $encoded_subject, $body, $headers)) {
            return true;
        } else {
            return "mail()関数での送信に失敗しました";
        }
    }

    /**
     * SMTP経由でメールを送信する
     */
    private function sendViaSMTP($to, $name, $subject, $body, $attachments = []) {
        $host = $this->host;
        if ($this->port == 465) {
            $host = 'ssl://' . $host;
        }

        $socket = @fsockopen($host, $this->port, $errno, $errstr, 10);
        if (!$socket) {
            $error = "SMTP接続失敗: {$errno} - {$errstr}";
            error_log($error);
            return $error;
        }

        $this->getResponse($socket); // サーバーの挨拶を待つ

        // HELO/EHLO
        fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $this->getResponse($socket);

        // 認証
        if (!empty($this->username) && !empty($this->password)) {
            fwrite($socket, "AUTH LOGIN\r\n");
            $this->getResponse($socket);

            fwrite($socket, base64_encode($this->username) . "\r\n");
            $this->getResponse($socket);

            fwrite($socket, base64_encode($this->password) . "\r\n");
            $response = $this->getResponse($socket);

            if (strpos($response, '235') === false) {
                $error = "SMTP認証失敗: " . $response;
                error_log($error);
                fwrite($socket, "QUIT\r\n");
                fclose($socket);
                return $error;
            }
        }

        // MAIL FROM
        fwrite($socket, "MAIL FROM: <{$this->from_email}>\r\n");
        $response = $this->getResponse($socket);
        if (substr($response, 0, 3) >= 400) {
            return "MAIL FROM エラー: " . $response;
        }

        // RCPT TO
        fwrite($socket, "RCPT TO: <{$to}>\r\n");
        $response = $this->getResponse($socket);
        if (substr($response, 0, 3) >= 400) {
            return "RCPT TO エラー: " . $response;
        }

        // DATA
        fwrite($socket, "DATA\r\n");
        $response = $this->getResponse($socket);
        if (substr($response, 0, 3) >= 400) {
            return "DATAコマンドエラー: " . $response;
        }

        // メッセージ本体の構築（マルチパート対応）
        $boundary = "----=_NextPart_" . md5(time());

        $headers = "From: " . mb_encode_mimeheader($this->from_name) . " <{$this->from_email}>\r\n";
        $headers .= "To: " . mb_encode_mimeheader($name) . " <{$to}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if (!empty($attachments)) {
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        }
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $message = "";

        if (!empty($attachments)) {
            // 本文パート
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $body . "\r\n\r\n";

            // 添付ファイルパート
            foreach ($attachments as $attachment) {
                $filename = mb_encode_mimeheader($attachment['name'], 'UTF-8', 'B');
                $content = chunk_split(base64_encode($attachment['content']));
                $type = $attachment['type'] ?? 'application/octet-stream';

                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: {$type}; name=\"{$filename}\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n";
                $message .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
                $message .= $content . "\r\n";
            }

            $message .= "--{$boundary}--\r\n";
        } else {
            $message .= $body;
        }

        fwrite($socket, $headers . "\r\n" . $message . "\r\n.\r\n");
        $response = $this->getResponse($socket);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        if (strpos($response, '250') !== false) {
            return true;
        } else {
            return "メール送信エラー (DATA終わり): " . $response;
        }
    }

    private function getResponse($socket) {
        $response = "";
        while ($str = fgets($socket, 512)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $response;
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
function send_email($to, $name, $subject, $body, $attachments = []) {
    global $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;

    $mailer = new Mailer(
        $smtp_host,
        $smtp_port,
        $smtp_username,
        $smtp_password,
        $smtp_from_email,
        $smtp_from_name
    );

    return $mailer->send($to, $name, $subject, $body, $attachments);
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
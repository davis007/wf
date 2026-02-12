<?php
require_once 'admin/config.php';

$db = get_db();
$message = '';
$success = false;

// メールアドレスパラメータを取得
$email = $_GET['email'] ?? '';

// 購読解除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $message = 'CSRFトークンが無効です';
    } elseif (empty($email)) {
        $message = 'メールアドレスを入力してください';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '有効なメールアドレスを入力してください';
    } else {
        // メールアドレスが登録されているか確認
        $stmt = $db->prepare("SELECT id, name, subscribed FROM recipients WHERE email = ?");
        $stmt->execute([$email]);
        $recipient = $stmt->fetch();

        if (!$recipient) {
            $message = 'このメールアドレスは登録されていません';
        } elseif (!$recipient['subscribed']) {
            $message = 'このメールアドレスは既に購読解除されています';
        } else {
            // 購読解除を実行
            $stmt = $db->prepare("UPDATE recipients SET subscribed = 0, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([$recipient['id']]);

            $message = 'メールの購読を解除しました。今後はメールが送信されません。';
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール購読解除 - WEST FIELD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full">
        <!-- ロゴ -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-white rounded-full shadow-lg">
                <i class="fas fa-envelope text-4xl text-purple-600"></i>
            </div>
            <h1 class="mt-4 text-3xl font-bold text-white">WEST FIELD</h1>
            <p class="text-white opacity-90">メール購読解除</p>
        </div>

        <!-- フォームカード -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
                    <div class="flex items-center">
                        <i class="fas <?php echo $success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="text-center">
                        <a href="/" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                            <i class="fas fa-home mr-2"></i>ホームページに戻る
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="space-y-6">
                        <!-- 説明 -->
                        <div class="text-center">
                            <p class="text-gray-600">
                                メールアドレスを入力すると、WEST FIELDからのメール配信を停止します。
                            </p>
                        </div>

                        <!-- メールアドレス入力 -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-1"></i>メールアドレス
                            </label>
                            <input type="email" id="email" name="email"
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-300"
                                   placeholder="example@email.com"
                                   required>
                        </div>

                        <!-- 注意事項 -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                <div class="text-sm text-yellow-700">
                                    <p class="font-medium">ご注意</p>
                                    <ul class="mt-1 list-disc list-inside space-y-1">
                                        <li>購読解除後は新着情報やお得な情報が届かなくなります</li>
                                        <li>再度購読するには管理者にお問い合わせください</li>
                                        <li>解除処理は即時反映されます</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 送信ボタン -->
                        <div>
                            <button type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-300">
                                <i class="fas fa-ban mr-2"></i>購読を解除する
                            </button>
                        </div>

                        <!-- リンク -->
                        <div class="text-center pt-4 border-t border-gray-200">
                            <a href="/" class="text-sm text-purple-600 hover:text-purple-800">
                                <i class="fas fa-arrow-left mr-1"></i>WEST FIELD ホームページに戻る
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- フッター -->
        <div class="mt-8 text-center text-white opacity-75">
            <p class="text-sm">
                &copy; <?php echo date('Y'); ?> WEST FIELD. All rights reserved.
            </p>
            <p class="text-xs mt-2">
                お問い合わせ: 090-9715-1979
            </p>
        </div>
    </div>
</body>
</html>
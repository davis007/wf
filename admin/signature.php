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

// 署名更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($content)) {
        $error = '署名を入力してください';
    } else {
        $stmt = $db->prepare("INSERT INTO signature (content) VALUES (?)");
        $stmt->execute([$content]);
        $success = '署名を更新しました';
        $signature = $content;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>署名編集 - WEST FIELD 管理パネル</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">WEST FIELD 管理パネル</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="dashboard.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>ダッシュボード
                        </a>
                        <a href="recipients.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i>送信先管理
                        </a>
                        <a href="send_mail.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>メール送信
                        </a>
                        <a href="logs.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-history mr-2"></i>配信ログ
                        </a>
                        <a href="signature.php" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-signature mr-2"></i>署名編集
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">
                        <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($_SESSION['admin_email']); ?>
                    </span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>ログアウト
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- メッセージ表示 -->
        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">メール署名編集</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">メールのフッターに追加される署名を編集します</p>
            </div>

            <div class="px-4 py-5 sm:p-6">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="space-y-6">
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">署名内容</label>
                            <textarea id="content" name="content" rows="10"
                                      class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="メールの署名を入力してください"
                                      required><?php echo htmlspecialchars($signature); ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">
                                改行はそのまま反映されます。メールの最後に追加されます。
                            </p>
                        </div>

                        <!-- プレビュー -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">プレビュー</h4>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm text-gray-700">
                                <div class="mb-4">
                                    <p class="text-gray-500">[メール本文]</p>
                                    <p class="mt-2">これはメールの本文です。メールの内容がここに表示されます。</p>
                                </div>
                                <div class="pt-4 border-t border-gray-200 text-gray-500">
                                    <div id="previewSignature" class="whitespace-pre-wrap"><?php echo htmlspecialchars($signature); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- テンプレート -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">テンプレート</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <button type="button" onclick="loadTemplate('default')"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                    <i class="fas fa-home mr-1"></i>デフォルト
                                </button>
                                <button type="button" onclick="loadTemplate('simple')"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                    <i class="fas fa-star mr-1"></i>シンプル
                                </button>
                                <button type="button" onclick="loadTemplate('detailed')"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                    <i class="fas fa-info-circle mr-1"></i>詳細
                                </button>
                                <button type="button" onclick="loadTemplate('business')"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                    <i class="fas fa-briefcase mr-1"></i>ビジネス
                                </button>
                            </div>
                        </div>

                        <!-- 保存ボタン -->
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>署名を保存
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 現在の署名履歴 -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">署名履歴</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">過去の署名（最新5件）</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <?php
                $stmt = $db->prepare("SELECT content, created_at FROM signature ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $history = $stmt->fetchAll();

                if (empty($history)): ?>
                    <p class="text-gray-500 text-center">署名履歴はありません</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($history as $index => $item): ?>
                            <div class="border border-gray-200 rounded-md p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?php echo date('Y/m/d H:i', strtotime($item['created_at'])); ?>
                                    </span>
                                    <?php if ($index === 0): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                            現在使用中
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-600 whitespace-pre-wrap bg-gray-50 p-3 rounded">
                                    <?php echo htmlspecialchars($item['content']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // プレビュー更新
        const contentTextarea = document.getElementById('content');
        const previewSignature = document.getElementById('previewSignature');

        contentTextarea.addEventListener('input', function() {
            previewSignature.textContent = this.value;
        });

        // テンプレート読み込み
        function loadTemplate(type) {
            let template = '';

            switch(type) {
                case 'default':
                    template = "WEST FIELD サバイバルゲームフィールド\n〒586-0052 大阪府河内長野市河合寺４２６\nTEL: 090-9715-1979\n営業時間: 10:00-16:00 (フィールド開閉 9:00-17:00)";
                    break;
                case 'simple':
                    template = "WEST FIELD\n河内長野市河合寺４２６\n090-9715-1979";
                    break;
                case 'detailed':
                    template = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nWEST FIELD - Survival Game Field in Osaka\n〒586-0052 大阪府河内長野市河合寺４２６\nTEL: 090-9715-1979\n営業時間: 10:00-16:00 (フィールド開閉 9:00-17:00)\n定休日: 不定休\n駐車場: 50台以上可能\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
                    break;
                case 'business':
                    template = "株式会社 WEST FIELD\n代表取締役 西野\n〒586-0052 大阪府河内長野市河合寺４２６\nTEL: 090-9715-1979\nEmail: info@westfield.example.com\nURL: https://westfield.example.com";
                    break;
            }

            contentTextarea.value = template;
            previewSignature.textContent = template;
        }
    </script>
</body>
</html>
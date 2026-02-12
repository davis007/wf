<?php
// ページタイトルと現在のページを設定
$page_title = '署名編集 - WEST FIELD 管理パネル';
$current_page = 'signature';
?>

<?php include_once 'header.php'; ?>

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 左側: 署名編集フォーム -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">署名編集</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                            <div class="space-y-4">
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                        署名内容
                                    </label>
                                    <textarea id="content" name="content" rows="10"
                                              class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="メールの末尾に追加される署名を入力してください"
                                              required><?php echo htmlspecialchars($signature); ?></textarea>
                                    <p class="mt-1 text-sm text-gray-500">
                                        改行はそのまま反映されます。メールの末尾に自動的に追加されます。
                                    </p>
                                </div>

                                <div>
                                    <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-save mr-2"></i>署名を更新
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右側: プレビューと履歴 -->
            <div class="lg:col-span-1 space-y-6">
                <!-- プレビュー -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">プレビュー</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm text-gray-700">
                            <div class="mb-4">
                                <p class="text-gray-600 mb-2">メール本文の例:</p>
                                <div class="bg-white border border-gray-300 rounded p-3 mb-4">
                                    いつもWEST FIELDをご利用いただきありがとうございます。<br>
                                    新しいイベントのご案内をお送りいたします。
                                </div>
                            </div>
                            <div class="border-t border-gray-300 pt-4 text-gray-500 text-xs">
                                <div class="whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($signature)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 署名履歴 -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">署名履歴</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <?php if (empty($history)): ?>
                            <p class="text-gray-500 text-center py-4">署名履歴はありません</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($history as $index => $item): ?>
                                    <div class="border border-gray-200 rounded-md p-4 <?php echo $index === 0 ? 'bg-blue-50 border-blue-200' : ''; ?>">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-xs font-medium text-gray-500">
                                                <?php echo date('Y/m/d H:i', strtotime($item['created_at'])); ?>
                                            </span>
                                            <?php if ($index === 0): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    現在の署名
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-700 whitespace-pre-wrap">
                                            <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 署名の使い方 -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">署名の使い方</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">推奨フォーマット</h4>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm">
                            <div class="whitespace-pre-wrap text-gray-600">
WEST FIELD
〒000-0000 東京都渋谷区〇〇〇〇〇〇
TEL: 03-1234-5678
FAX: 03-1234-5679
Email: info@westfield.example.com
URL: https://westfield.example.com
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">注意事項</h4>
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                            <li>署名はすべての送信メールの末尾に自動的に追加されます</li>
                            <li>改行はそのまま反映されます</li>
                            <li>HTMLタグは使用できません（プレーンテキストのみ）</li>
                            <li>長すぎる署名はメールの見た目を損なう可能性があります</li>
                            <li>最新の署名のみが使用されます</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

<?php include_once 'footer.php'; ?>
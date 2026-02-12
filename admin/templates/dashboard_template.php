<?php
// ページタイトルと現在のページを設定
$page_title = 'ダッシュボード - WEST FIELD 管理パネル';
$current_page = 'dashboard';
?>

<?php include_once 'header.php'; ?>

        <!-- 統計カード -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">登録送信先</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $recipients_count; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-bell text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">購読中</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $subscribed_count; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-paper-plane text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">送信済みメール</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $delivery_count; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- クイックアクション -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4">クイックアクション</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="recipients.php?action=add" class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-300 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-900">送信先追加</h3>
                            <p class="text-sm text-gray-500">新しい送信先を登録</p>
                        </div>
                    </div>
                </a>

                <a href="send_mail.php" class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-300 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                            <i class="fas fa-envelope text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-900">メール送信</h3>
                            <p class="text-sm text-gray-500">一斉メールを送信</p>
                        </div>
                    </div>
                </a>

                <a href="signature.php" class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-300 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                            <i class="fas fa-edit text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-900">署名編集</h3>
                            <p class="text-sm text-gray-500">メール署名を変更</p>
                        </div>
                    </div>
                </a>

                <a href="logs.php" class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-300 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <i class="fas fa-chart-bar text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-900">配信ログ</h3>
                            <p class="text-sm text-gray-500">送信履歴を確認</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 最近の送信履歴 -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">最近の送信履歴</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">直近5件のメール送信履歴</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">送信日時</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">送信先</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件名</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($recent_deliveries)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    送信履歴はありません
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_deliveries as $log): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('Y/m/d H:i', strtotime($log['sent_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($log['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($log['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($log['subject']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php echo $log['status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $log['status'] === 'success' ? '成功' : '失敗'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a href="logs.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    すべての履歴を見る <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- システム情報 -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">システム情報</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">PHPバージョン</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo phpversion(); ?></dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">データベース</dt>
                        <dd class="mt-1 text-sm text-gray-900">SQLite</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">管理者メール</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars(get_admin_email()); ?></dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">最終ログイン</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo date('Y/m/d H:i:s'); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

    <script>
        // 自動更新（60秒ごと）
        setTimeout(function() {
            window.location.reload();
        }, 60000);
    </script>

<?php include_once 'footer.php'; ?>
<?php include_once 'header.php'; ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">予約管理</h2>
        <p class="text-gray-600">受け付けた予約の一覧と詳細を確認できます。</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($success); ?></p>
    </div>
<?php endif; ?>

<!-- 検索バー -->
<div class="bg-white shadow rounded-lg p-6 mb-8">
    <form action="bookings.php" method="GET" class="flex items-center">
        <div class="relative flex-grow">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="名前、メール、チーム名で検索">
        </div>
        <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            検索
        </button>
        <?php if (!empty($search)): ?>
            <a href="bookings.php" class="ml-2 text-sm text-gray-500 hover:text-gray-700">クリア</a>
        <?php endif; ?>
    </form>
</div>

<!-- 予約詳細表示（モーダル風） -->
<?php if ($view_booking): ?>
    <div class="bg-white shadow rounded-lg overflow-hidden mb-8 border-2 border-blue-500">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-blue-800">予約詳細 #<?php echo $view_booking['id']; ?></h3>
            <a href="bookings.php<?php echo !empty($search) ? '?search='.urlencode($search).'&page='.$page : '?page='.$page; ?>" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">基本情報</h4>
                    <table class="min-w-full text-sm">
                        <tr>
                            <td class="py-2 font-medium text-gray-500 w-1/3">予約種別</td>
                            <td class="py-2 text-gray-900"><?php echo $view_booking['booking_type'] === 'night_battle' ? '夜戦' : '通常（定例会）'; ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">氏名</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['name_kanji']); ?> (<?php echo htmlspecialchars($view_booking['name_kana']); ?>)</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">メール</td>
                            <td class="py-2 text-blue-600 font-medium"><?php echo htmlspecialchars($view_booking['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">電話番号</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['tel']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">住所</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['address']); ?></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">予約内容</h4>
                    <table class="min-w-full text-sm">
                        <tr>
                            <td class="py-2 font-medium text-gray-500 w-1/3">参加日</td>
                            <td class="py-2 text-gray-900 font-bold"><?php echo htmlspecialchars($view_booking['event_date']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">チーム名</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['team_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">人数</td>
                            <td class="py-2 text-gray-900 font-bold"><?php echo (int)$view_booking['people']; ?> 名</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">代表者</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['participants']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">送迎</td>
                            <td class="py-2 text-gray-900"><?php echo $view_booking['pickup'] === 'yes' ? '有り ('.$view_booking['pickup_people'].')' : '無し'; ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">レンタルガン</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['rental_gun']); ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">レンタルゴーグル</td>
                            <td class="py-2 text-gray-900"><?php echo htmlspecialchars($view_booking['rental_goggle']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- メール送信フォーム -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h4 class="text-sm font-bold text-gray-800 mb-4">予約者にメールを送信</h4>
                <form action="bookings.php" method="POST">
                    <input type="hidden" name="action" value="send_email">
                    <input type="hidden" name="id" value="<?php echo $view_booking['id']; ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($view_booking['email']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">件名</label>
                        <input type="text" name="subject" value="【WEST FIELD】ご予約の件について" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">本文</label>
                        <textarea name="body" rows="6" class="w-full border border-gray-300 rounded-md p-2 text-sm"><?php echo htmlspecialchars($view_booking['name_kanji']); ?> 様

WEST FIELDです。
ご予約いただいた件につきまして、以下の通りご連絡申し上げます。

...
</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded text-sm transition duration-200">
                            <i class="fas fa-paper-plane mr-2"></i>メールを送信する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 予約一覧テーブル -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">受付日時</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約種別</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">氏名 / チーム</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">参加日</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">人数</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">アクション</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">予約は見つかりませんでした</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr class="<?php echo $view_id == $booking['id'] ? 'bg-blue-50' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                <?php echo date('Y/m/d H:i', strtotime($booking['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $booking['booking_type'] === 'night_battle' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $booking['booking_type'] === 'night_battle' ? '夜戦' : '通常'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['name_kanji']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($booking['team_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($booking['event_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                <?php echo htmlspecialchars($booking['people']); ?>名
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                    <a href="bookings.php?view=<?php echo $booking['id']; ?>&page=<?php echo $page; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"
                                       class="text-blue-600 hover:text-blue-900" title="詳細を表示">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="bookings.php" method="POST" onsubmit="return confirm('本当にこの予約を削除しますか？');" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="削除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ページネーション -->
    <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                全 <span class="font-medium"><?php echo $total_count; ?></span> 件中
                <span class="font-medium"><?php echo $offset + 1; ?></span> 〜
                <span class="font-medium"><?php echo min($total_count, $offset + $limit); ?></span> 件を表示
            </div>
            <div class="flex-1 flex justify-end">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="bookings.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="bookings.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-<?php echo $i === $page ? 'blue-50 text-blue-600 font-bold' : 'white text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="bookings.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>

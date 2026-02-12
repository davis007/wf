<?php
// ページタイトルと現在のページを設定
$page_title = 'メール送信 - WEST FIELD 管理パネル';
$current_page = 'send_mail';
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 左側: 送信設定 -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">送信設定</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="send">

                            <div class="space-y-4">
                                <!-- 送信先選択 -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">送信先</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" id="send_all" name="send_to" value="all" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <label for="send_all" class="ml-2 block text-sm text-gray-900">
                                                全送信先 (<?php echo $total_recipients; ?>件)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="send_subscribed" name="send_to" value="subscribed" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <label for="send_subscribed" class="ml-2 block text-sm text-gray-900">
                                                購読者のみ (<?php echo $subscribed_recipients; ?>件)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 件名 -->
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700">件名</label>
                                    <input type="text" id="subject" name="subject"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="メールの件名"
                                           required>
                                </div>

                                <!-- 送信ボタン -->
                                <div>
                                    <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-paper-plane mr-2"></i>メールを送信する
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2 text-center">
                                        送信前に内容を確認してください
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 送信統計 -->
                <div class="mt-6 bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">送信統計</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">全送信先</dt>
                                <dd class="text-sm text-gray-900"><?php echo $total_recipients; ?> 件</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">購読者</dt>
                                <dd class="text-sm text-gray-900"><?php echo $subscribed_recipients; ?> 件</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">非購読者</dt>
                                <dd class="text-sm text-gray-900"><?php echo $total_recipients - $subscribed_recipients; ?> 件</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- 右側: メール本文編集 -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">メール本文</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-4">
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">本文</label>
                                <textarea id="content" name="content" rows="15"
                                          class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="メールの本文を入力してください"
                                          required></textarea>
                            </div>

                            <!-- プレビュー -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">プレビュー（署名付き）</h4>
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm text-gray-700">
                                    <div id="previewContent" class="whitespace-pre-wrap">
                                        本文を入力するとここにプレビューが表示されます
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-200 text-gray-500 text-xs">
                                        <div id="previewSignature"><?php echo nl2br(htmlspecialchars($signature)); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- テンプレート -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">テンプレート</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <button type="button" onclick="loadTemplate('event')"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                        <i class="fas fa-calendar-alt mr-1"></i>イベント案内
                                    </button>
                                    <button type="button" onclick="loadTemplate('news')"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                        <i class="fas fa-newspaper mr-1"></i>お知らせ
                                    </button>
                                    <button type="button" onclick="loadTemplate('campaign')"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                        <i class="fas fa-tag mr-1"></i>キャンペーン
                                    </button>
                                    <button type="button" onclick="loadTemplate('greeting')"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-2 px-3 rounded-md">
                                        <i class="fas fa-handshake mr-1"></i>挨拶状
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script>
        // プレビュー更新
        const contentTextarea = document.getElementById('content');
        const previewContent = document.getElementById('previewContent');

        contentTextarea.addEventListener('input', function() {
            previewContent.textContent = this.value;
        });

        // テンプレート読み込み
        function loadTemplate(type) {
            let template = '';

            switch(type) {
                case 'event':
                    template = "【WEST FIELD イベント開催のお知らせ】\n\nいつもWEST FIELDをご利用いただきありがとうございます。\n\nこの度、下記の通り特別イベントを開催いたします。\n\n■イベント名：\n■開催日時：\n■開催場所：WEST FIELD\n■参加費：\n■定員：\n\n皆様のご参加をお待ちしております。\n\n※詳細は当フィールドまでお問い合わせください。";
                    break;
                case 'news':
                    template = "【WEST FIELD からのお知らせ】\n\nいつもWEST FIELDをご利用いただきありがとうございます。\n\n重要なご案内がございますので、お知らせいたします。\n\n【お知らせ内容】\n\n今後ともWEST FIELDをよろしくお願いいたします。";
                    break;
                case 'campaign':
                    template = "【WEST FIELD キャンペーンのお知らせ】\n\nいつもWEST FIELDをご利用いただきありがとうございます。\n\nこの度、感謝を込めて特別キャンペーンを実施いたします！\n\n■キャンペーン内容：\n■対象期間：\n■特典：\n\nこの機会にぜひWEST FIELDをご利用ください。\n\n皆様のご来場を心よりお待ちしております。";
                    break;
                case 'greeting':
                    template = "平素よりWEST FIELDをご愛顧いただき、誠にありがとうございます。\n\nこの度、季節のご挨拶を申し上げます。\n\n皆様にはますますご清祥のこととお慶び申し上げます。\n\n今後とも変わらぬご愛顧を賜りますよう、心よりお願い申し上げます。\n\n引き続き、WEST FIELDをよろしくお願いいたします。";
                    break;
            }

            contentTextarea.value = template;
            previewContent.textContent = template;
        }

        // フォーム送信前の確認
        document.querySelector('form').addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!subject || !content) {
                e.preventDefault();
                alert('件名と本文は必須です');
                return;
            }

            const sendTo = document.querySelector('input[name="send_to"]:checked').value;
            const recipientCount = sendTo === 'all' ? <?php echo $total_recipients; ?> : <?php echo $subscribed_recipients; ?>;

            if (recipientCount === 0) {
                e.preventDefault();
                alert('送信先がありません');
                return;
            }

            if (!confirm(`本当に${recipientCount}件のメールを送信しますか？`)) {
                e.preventDefault();
            }
        });
    </script>

<?php include_once 'footer.php'; ?>
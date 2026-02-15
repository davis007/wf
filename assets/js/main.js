// オフキャンバスメニューの機能
(function() {
	'use strict';

	// オフキャンバス初期化（要素が存在する時だけ）
	function initOffCanvasMenu() {
		const menuToggle = document.getElementById('menuToggle');
		const offCanvasMenu = document.getElementById('offCanvasMenu');
		const body = document.body;

		// ヘッダーがまだ挿入されていない等で要素が無ければ初期化しない
		if (!menuToggle || !offCanvasMenu) {
			return false;
		}

		// 二重初期化防止
		if (menuToggle.dataset.offcanvasInitialized === '1') {
			return true;
		}
		menuToggle.dataset.offcanvasInitialized = '1';

		// メニューオーバーレイを作成（重複作成しない）
		let overlay = document.querySelector('.menu-overlay');
		if (!overlay) {
			overlay = document.createElement('div');
			overlay.className = 'menu-overlay';
			document.body.appendChild(overlay);
		}

		// メニューを開く関数
	function openMenu() {
		// インラインスタイルで表示（CSSクラスに依存しない）
		offCanvasMenu.style.display = '';
		// 次のフレームでtransformを変更（アニメーションのため）
		requestAnimationFrame(() => {
			offCanvasMenu.style.transform = 'translateX(0)';
			overlay.classList.add('active');
		});
		body.style.overflow = 'hidden'; // スクロールを防止
	}

	// メニューを閉じる関数
	function closeMenu() {
		offCanvasMenu.style.transform = 'translateX(-100%)';
		overlay.classList.remove('active');
		body.style.overflow = ''; // スクロールを復元
		// トランジション完了後にインラインスタイルで非表示
		setTimeout(() => {
			offCanvasMenu.style.display = 'none';
		}, 300); // transition duration
	}

		// バーガーメニュークリックでメニューを開く
		if (menuToggle) {
			menuToggle.addEventListener('click', function(e) {
				e.stopPropagation();
				// transformで開閉状態を判定（CSSクラスに依存しない）
				if (offCanvasMenu.style.transform === 'translateX(0)' || offCanvasMenu.style.transform === 'translateX(0px)') {
					closeMenu();
				} else {
					openMenu();
				}
			});
		}

		// 閉じるボタン（✕）でメニューを閉じる
		const menuClose = document.getElementById('menuClose');
		if (menuClose) {
			menuClose.addEventListener('click', function(e) {
				e.stopPropagation();
				closeMenu();
			});
		}

		// オーバーレイクリックでメニューを閉じる
		if (overlay) {
			overlay.addEventListener('click', closeMenu);
		}

		// メニュー内のリンククリックでメニューを閉じる
		const menuLinks = offCanvasMenu.querySelectorAll('a');
		if (menuLinks.length > 0) {
			menuLinks.forEach(link => {
				link.addEventListener('click', function(e) {
					// ダミーリンク（#）の場合はデフォルト動作を防止
					if (this.getAttribute('href') === '#') {
						e.preventDefault();
					}
					closeMenu();
				});
			});
		}

		// ESCキーでメニューを閉じる（documentは常に存在するはずだが安全のため）
		if (document && document.addEventListener) {
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && (offCanvasMenu.style.transform === 'translateX(0)' || offCanvasMenu.style.transform === 'translateX(0px)')) {
					closeMenu();
				}
			});
		}

		// ウィンドウリサイズ時にメニューを閉じる（モバイルでの回転時など）
		let resizeTimer;
		if (window && window.addEventListener) {
			window.addEventListener('resize', function() {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function() {
					if (window.innerWidth > 768) {
						closeMenu();
					}
				}, 250);
			});
		}

		// ロゴの読み込み状態を確認
		const logo = document.querySelector('header img');
		if (logo) {
			logo.addEventListener('error', function() {
				console.warn('ロゴ画像の読み込みに失敗しました。代替テキストを表示します。');
				this.alt = 'WEST FIELD ロゴ';
			});
		}

		// モバイルデバイスかどうかを判定
		function isMobileDevice() {
			return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
		}

		// モバイルデバイスの場合の追加処理
		if (isMobileDevice() && menuToggle) {
			// タッチイベントの最適化
			menuToggle.style.cursor = 'pointer';
			// タッチイベントを通常のクリックイベントで処理するため、
			// preventDefaultは使用しない
		}

		console.log('WEST FIELD サイトのJavaScriptが正常に読み込まれました。');
		return true;
	}

	// そのほかの初期化（DOMがあれば動かす系）
	function initOptionalAnimations() {
		// スクロール時に料金表を少しふわっと表示（オプション）
		const priceTable = document.querySelector('table');
		if (priceTable) {
			const observerOptions = {
				threshold: 0.2,
				rootMargin: '0px 0px -50px 0px'
			};

			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.classList.add('opacity-100', 'translate-y-0');
						entry.target.classList.remove('opacity-0', 'translate-y-4');
						observer.unobserve(entry.target);
					}
				});
			}, observerOptions);

			priceTable.classList.add('opacity-0', 'translate-y-4', 'transition-all', 'duration-700', 'ease-out');
			observer.observe(priceTable);
		}
	}

	// documentが存在することを確認してからイベントリスナーを追加
	if (document && document.addEventListener) {
		document.addEventListener('DOMContentLoaded', function() {
			// まずは通常のタイミングで初期化を試す
			const ok = initOffCanvasMenu();
			initOptionalAnimations();

			// ヘッダーをfetch等で後挿入する構成なら、DOM変化を監視して要素出現後に初期化する
			if (!ok) {
				const observer = new MutationObserver(() => {
					if (initOffCanvasMenu()) {
						observer.disconnect();
					}
				});
				observer.observe(document.body, { childList: true, subtree: true });
			}

			// ページ読み込み完了後にloadedクラスを追加（コンセプト画像アニメーション用）
			window.addEventListener('load', function() {
				document.body.classList.add('loaded');
			});

			// 画像が既に読み込まれている場合もloadedクラスを追加
			if (document.readyState === 'complete') {
				document.body.classList.add('loaded');
			}
		});
	} else {
		// documentが利用できない場合は即時実行
		initOffCanvasMenu();
		initOptionalAnimations();
		document.body.classList.add('loaded');
	}
})();

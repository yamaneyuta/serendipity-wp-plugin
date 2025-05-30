<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constants;

/** トークンの転送種別。コントラクトで定義している内容と一致させる必要がある */
final class UnlockPaywallTransferType {
	/** 販売手数料 */
	public const HANDLING_FEE = 1;

	/** 販売者の売上 */
	public const SELLER_PROFIT = 2;

	/** アフィリエイト報酬 */
	public const AFFILIATE_REWARD = 3;
}

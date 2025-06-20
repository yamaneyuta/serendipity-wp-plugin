<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\DI;

use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\AppContractRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ChainRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\InvoiceRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\OracleRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\PostRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Logging\Handler\SimpleLogger;
use Cornix\Serendipity\Core\Infrastructure\Logging\Logger;
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\WalletServiceImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\LogLevelProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PostTitleProviderImpl;
use wpdb;

use function DI\autowire;

final class ContainerDefinitions {
	public static function getDefinitions(): array {
		return array(
			wpdb::class                  => fn() => $GLOBALS['wpdb'],

			// TableGateway
			// ChainTable::class => autowire(),

			// Repository
			AppContractRepository::class => autowire( AppContractRepositoryImpl::class ),
			ChainRepository::class       => autowire( ChainRepositoryImpl::class ),
			InvoiceRepository::class     => autowire( InvoiceRepositoryImpl::class ),
			OracleRepository::class      => autowire( OracleRepositoryImpl::class ),
			PostRepository::class        => autowire( PostRepositoryImpl::class ),
			TokenRepository::class       => autowire( TokenRepositoryImpl::class ),

			// Service
			WalletService::class         => autowire( WalletServiceImpl::class ),
			PostTitleProvider::class     => autowire( PostTitleProviderImpl::class ),

			// Logging
			Logger::class                => autowire( SimpleLogger::class ),
			LogLevelProvider::class      => autowire( LogLevelProviderImpl::class ),
		);
	}
}

<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Process;

use DateTime;
use DirectoryIterator;
use MediaWiki\Config\Config;
use MediaWiki\Logger\LoggerFactory;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class RemoveOrphanedDirectories implements IProcessStep, LoggerAwareInterface {

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/** @var string */
	private string $uploadDirectory;

	/**
	 * @param Config $mainConfig
	 */
	public function __construct( Config $mainConfig ) {
		$this->uploadDirectory = $mainConfig->get( 'UploadDirectory' );

		$logger = LoggerFactory::getInstance( 'ImportOfficeFiles_RemoveOrphanedDirectories' );
		$this->setLogger( $logger );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $data = [] ): array {
		$this->logger->info( "Start removal of orphaned 'ImportOfficeFiles' subdirectories..." );

		$importOfficeDir = $this->uploadDirectory . '/cache/ImportOfficeFiles';
		if ( !file_exists( $importOfficeDir ) ) {
			$this->logger->info( "'ImportOfficeFiles' directory does not exist, nothing to do here." );

			return [];
		}

		$iterator = new DirectoryIterator( $importOfficeDir );
		foreach ( $iterator as $subDir ) {
			if ( $subDir->isDir() === true && $subDir->isDot() === false ) {
				$lastModificationTimestamp = $subDir->getMTime();

				$dt = new DateTime();
				$dt->setTimestamp( $lastModificationTimestamp );

				$dtNow = new DateTime();

				$diff = $dt->diff( $dtNow );
				if ( $diff->days >= 1 ) {
					$this->logger->info( "Directory '{$subDir->getFilename()}' is more then 1 day old. Removing..." );

					$subDirPath = $subDir->getPathname();
					unlink( $subDirPath );
				}
			}
		}

		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}

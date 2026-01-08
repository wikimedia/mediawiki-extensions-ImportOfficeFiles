<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

use MediaWiki\Extension\ImportOfficeFiles\Process\RemoveOrphanedDirectories;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\WikiCron\WikiCronManager;

class RemoveOrphanedDirectoriesCron {

	/**
	 * @return void
	 */
	public static function register(): void {
		if ( defined( 'MW_PHPUNIT_TEST' ) || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}

		/** @var WikiCronManager $cronManager */
		$cronManager = MediaWikiServices::getInstance()->getService( 'MWStake.WikiCronManager' );

		// Interval: Daily at 01:00
		$cronManager->registerCron(
			'ext-importofficefiles-remove-orphaned-directories',
			'0 1 * * *',
			new ManagedProcess( [
				'remove-orphaned-directories' => [
					'class' => RemoveOrphanedDirectories::class,
					'services' => [
						'MainConfig',
					],
				]
			] )
		);
	}
}

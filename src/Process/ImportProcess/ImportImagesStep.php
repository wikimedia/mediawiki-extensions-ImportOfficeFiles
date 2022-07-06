<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Process\ImportProcess;

use Exception;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\InterruptingProcessStep;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ImportImagesStep implements InterruptingProcessStep, LoggerAwareInterface {

	/**
	 * @var Workspace
	 */
	private $workspace;

	/**
	 * @var string
	 */
	private $importImagesScript;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param string $uploadId
	 * @param string $uploadDirectory
	 * @param string $importImagesScript
	 */
	public function __construct( string $uploadId, string $uploadDirectory, string $importImagesScript ) {
		$workspaceDir = $uploadDirectory . '/cache/ImportOfficeFiles';

		$config = MediaWikiServices::getInstance()->getMainConfig();

		$this->workspace = new Workspace( $config );
		$this->workspace->init( $uploadId, $workspaceDir );

		$this->importImagesScript = $importImagesScript;

		$logger = LoggerFactory::getInstance( 'ImportOfficeFiles_UI' );
		$this->setLogger( $logger );

		$this->logger->debug( "Start importing images for upload ID '$uploadId'." );
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		$imagesDir = $this->workspace->getPath() . '/result/images/';

		$this->logger->debug( 'Importing images from directory: ' . $imagesDir );

		try {
			// php {IP}/maintenance/importImages.php images/cache/ImportOfficeFiles/{uploadId}/result/images/
			$processImages = new Process( [ $GLOBALS['wgPhpCli'], $this->importImagesScript, $imagesDir ] );
			$processImages->run();
		} catch ( Exception $e ) {
			return [ 'output_images_exception' => $e->getMessage() ];
		}

		if ( !$processImages->isSuccessful() ) {
			throw new ProcessFailedException( $processImages );
		}

		$this->logger->debug( 'Images imported.' );
		$this->logger->debug( 'Output of "importImages.php" script: ' . "\n" . $processImages->getOutput() );

		return [
			'output_images' => $processImages->getOutput()
		];
	}

}

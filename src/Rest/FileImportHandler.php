<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest;

use Config;
use MediaWiki\Extension\ImportOfficeFiles\Process\ImportProcess\ImportImagesStep;
use MediaWiki\Extension\ImportOfficeFiles\Process\ImportProcess\ImportPagesStep;
use MediaWiki\Extension\ImportOfficeFiles\Process\ImportProcess\RemoveFilesStep;
use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use Wikimedia\ParamValidator\ParamValidator;

class FileImportHandler extends SimpleHandler {

	/**
	 * @var string
	 */
	private $uploadDirectory;

	/**
	 * @param Config $mainConfig
	 */
	public function __construct( Config $mainConfig ) {
		$this->uploadDirectory = $mainConfig->get( 'UploadDirectory' );
	}

	/**
	 * @return \MediaWiki\Rest\Response
	 */
	public function run() {
		$request = $this->getRequest();

		$uploadId = $request->getPathParam( "uploadId" );

		$importXmlScript = $GLOBALS['IP'] . '/maintenance/importDump.php';
		$importImagesScript = $GLOBALS['IP'] . '/maintenance/importImages.php';

		$process = new ManagedProcess( [
			'import-images-step' => [
				'class' => ImportImagesStep::class,
				'args' => [
					$uploadId,
					$this->uploadDirectory,
					$importImagesScript,
				]
			],
			'import-pages-step' => [
				'class' => ImportPagesStep::class,
				'args' => [
					$uploadId,
					$this->uploadDirectory,
					$importXmlScript
				]
			],
			'remove-files-step' => [
				'class' => RemoveFilesStep::class,
				'args' => [
					$uploadId,
					$this->uploadDirectory
				]
			]
		], 300 );

		/** @var \MWStake\MediaWiki\Component\ProcessManager\ProcessManager $processManager */
		$processManager = MediaWikiServices::getInstance()->getService( 'ProcessManager' );
		$processId = $processManager->startProcess( $process );

		return $this->getResponseFactory()->createJson( [
			'success' => true,
			'processId' => $processId
		] );
	}

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'uploadId' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			],
			'filename' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			]
		];
	}

}

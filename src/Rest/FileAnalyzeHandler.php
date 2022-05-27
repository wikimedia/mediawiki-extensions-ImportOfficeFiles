<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest;

use MediaWiki\Extension\ImportOfficeFiles\Process\FileConvertProcessStep;
use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use Wikimedia\ParamValidator\ParamValidator;
use function Sabre\HTTP\decodePath;

class FileAnalyzeHandler extends SimpleHandler {

	public function __construct() {
	}

	public function run() {
		$request = $this->getRequest();
		$uploadId = $request->getPathParam( "uploadId" );
		$file = $request->getPathParam( "filename" );

		$params = $request->getUri()->getQuery();
		$config = json_decode( decodePath( $params ), true );
		$title = $config['config']['title'];
		$structure = $config['config']['structure'];
		$conflict = $config['config']['conflict'];

		$process = new ManagedProcess( [
			'file-convert-step' => [
				'class' => FileConvertProcessStep::class,
				'args' => [
					$uploadId,
					$file,
					$title,
					$structure,
					$conflict
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

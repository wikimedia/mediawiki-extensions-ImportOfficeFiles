<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest;

use Config;
use MediaWiki\Extension\ImportOfficeFiles\Importer;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Rest\SimpleHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\ParamValidator\ParamValidator;

abstract class ImportProcessHandler extends SimpleHandler implements LoggerAwareInterface {

	/**
	 * @var Workspace
	 */
	protected $workspace;

	/**
	 * @var string
	 */
	protected $workspaceDir;

	/**
	 * @var Importer
	 */
	protected $importer;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @param Config $mainConfig
	 * @param Importer $importer
	 */
	public function __construct( Config $mainConfig, Importer $importer ) {
		$this->importer = $importer;

		$uploadDirectory = $mainConfig->get( 'UploadDirectory' );
		$this->workspaceDir = $uploadDirectory . '/cache/ImportOfficeFiles';

		$this->workspace = new Workspace( $mainConfig );

		$logger = LoggerFactory::getInstance( 'ImportOfficeFiles_UI' );
		$this->setLogger( $logger );
		$this->importer->setLogger( $logger );
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @return \MediaWiki\Rest\Response
	 */
	abstract public function run();

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'uploadId' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			]
		];
	}
}

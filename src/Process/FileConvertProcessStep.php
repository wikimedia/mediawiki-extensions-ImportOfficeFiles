<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Process;

use Exception;
use MediaWiki\Extension\ImportOfficeFiles\AnalyzerResult;
use MediaWiki\Extension\ImportOfficeFiles\ConverterResult;
use MediaWiki\Extension\ImportOfficeFiles\IModule;
use MediaWiki\Extension\ImportOfficeFiles\ModuleFactory;
use MediaWiki\Extension\ImportOfficeFiles\Modules\MSOfficeWord;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SplFileInfo;

class FileConvertProcessStep implements IProcessStep, LoggerAwareInterface {

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var IModule
	 */
	private $module;

	/**
	 * @var Workspace
	 */
	private $workspace;

	/**
	 * @var string
	 */
	private $destination;

	/**
	 * @var SplFileInfo
	 */
	private $source;

	/**
	 * @var string
	 */
	private $baseTitle;

	/**
	 * @var int
	 */
	private $split;

	/**
	 * @var bool
	 */
	private $uncollide;

	/**
	 * @param string $uploadId
	 * @param string $file
	 * @param string $title
	 * @param string $structure
	 * @param string $conflict
	 * @throws Exception
	 */
	public function __construct(
		string $uploadId,
		string $file,
		string $title,
		string $structure,
		string $conflict
	) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		// We need integer "split" parameter
		$splitMap = [
			'h1' => 2,
			'h2' => 3,
			'h3' => 4,
			'h4' => 5,
			'h5' => 6
		];

		if ( $structure ) {
			$this->split = $splitMap[$structure];
		} else {
			$this->split = 0;
		}

		if ( $conflict === 'rename' ) {
			$this->uncollide = true;
		} else {
			$this->uncollide = false;
		}

		$workspaceDirectory = $config->get( 'UploadDirectory' ) . '/cache/' . $uploadId;

		$this->destination = $workspaceDirectory . '/workspace/';
		$sourceFilePath = trim( $workspaceDirectory . '/' . $file );

		$this->baseTitle = $title;

		if ( !file_exists( $sourceFilePath ) ) {
			throw new Exception( 'File does not exist' );
		}
		$this->source = new SplFileInfo( $sourceFilePath );

		$logger = LoggerFactory::getInstance( 'ImportOfficeFiles' );
		$this->setLogger( $logger );

		$this->logger->debug( "Convert process launched." );
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		$this->setWorkspace( $this->destination );
		if ( !$this->source ) {
			throw new Exception( 'Source file not found' );
		}
		$this->logger->debug( "Source file: '{$this->source->getFilename()}'..." );

		$this->source = $this->workspace->uploadSourceFile( $this->source );
		if ( $this->source === null ) {
			throw new Exception( 'Source file failed to upload into workspace directory' );
		}

		$this->workspace->addToBucket(
			MSOfficeWord::BUCKET_ANALYZER_PARAMS,
			[
				'namespace' => '',
				'base-title' => $this->baseTitle,
				'verbose' => false,
				'split' => $this->split,
				'ns-filerepo-compat' => 'false',
				'uncollide' => $this->uncollide,
				'categories' => [
					'Office import'
				],
			]
		);
		$this->workspace->saveBucket( MSOfficeWord::BUCKET_ANALYZER_PARAMS );

		$moduleFactory = new ModuleFactory();
		$this->module = $moduleFactory->getModule( $this->workspace );

		$analyzerResult = $this->runAnalyzer( $this->workspace );
		$converterResult = $this->runConvertStep( $this->workspace, $analyzerResult );

		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param string $path
	 * @return void
	 */
	private function setWorkspace( $path ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$this->workspace = new Workspace( $config );
		$workspaceInfo = $this->workspace->init( 'ImportOffice', $path );
		$workspacePath = $workspaceInfo['id'];

		$this->logger->debug( "Workspace: '{$workspacePath}'" );
	}

	/**
	 * @param Workspace $workspace
	 * @return AnalyzerResult
	 */
	private function runAnalyzer( Workspace $workspace ): AnalyzerResult {
		$this->logger->debug( "Start analyzing ..." );
		$analyzer = $this->module->getAnalyzer();
		$result = $analyzer->analyze( $workspace );
		$status = $result->getStatus();

		if ( $status ) {
			$this->logger->debug( "Analyzing done." );
		} else {
			$this->logger->error( "Analyzing failed." );
		}

		return $result;
	}

	/**
	 * @param Workspace $workspace
	 * @param AnalyzerResult $result
	 * @return ConverterResult
	 */
	private function runConvertStep( Workspace $workspace, AnalyzerResult $result ): ConverterResult {
		$this->logger->debug( "Start conversion ..." );
		$params = [
			'filename' => $result->getFilename(),
			'namespace' => $result->getNamespace(),
			'base-title' => $result->getBaseTitle(),
			'verbose' => false,
			'split' => $result->getSplit(),
			'categories' => $result->getCategories(),
			'ns-filerepo-compat' => $result->getNsFileRepoCompat(),
			'title-map' => $result->getTitleMap()
		];

		$workspace->addToBucket( MSOfficeWord::BUCKET_CONVERTER_PARAMS, $params );
		$workspace->saveBucket( MSOfficeWord::BUCKET_CONVERTER_PARAMS );
		$extractor = $this->module->getConverter();
		$result = $extractor->convert( $workspace );
		$status = $result->getStatus();

		if ( $status ) {
			$this->logger->debug( "Conversion done." );
		} else {
			$this->logger->error( "Conversion failed." );
		}

		return $result;
	}
}

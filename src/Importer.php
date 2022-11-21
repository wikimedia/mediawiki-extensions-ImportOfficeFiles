<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

use Config;
use Exception;
use ImportStreamSource;
use MediaHandler;
use MediaWiki\HookContainer\HookContainer;
use MimeAnalyzer;
use MWFileProps;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RawMessage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RepoGroup;
use SpecialUpload;
use SplFileInfo;
use Status;
use Title;
use User;
use WikiImporter;
use Wikimedia\AtEase\AtEase;

class Importer implements LoggerAwareInterface {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var RepoGroup
	 */
	private $repoGroup;

	/**
	 * @var HookContainer
	 */
	private $hookContainer;

	/**
	 * @var MimeAnalyzer
	 */
	private $mimeAnalyzer;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param Config $config
	 * @param RepoGroup $repoGroup
	 * @param HookContainer $hookContainer
	 * @param MimeAnalyzer $mimeAnalyzer
	 */
	public function __construct(
		Config $config,
		RepoGroup $repoGroup,
		HookContainer $hookContainer,
		MimeAnalyzer $mimeAnalyzer
	) {
		$this->config = $config;
		$this->repoGroup = $repoGroup;
		$this->hookContainer = $hookContainer;
		$this->mimeAnalyzer = $mimeAnalyzer;

		$this->logger = new NullLogger();
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param string $dirPath
	 * @param User|null $user
	 *
	 * @return Status
	 */
	public function importImages( string $dirPath, ?User $user ): Status {
		$status = Status::newGood();

		$this->logger->debug( 'Importing images from directory: ' . $dirPath );

		if ( $user === null ) {
			$user = User::newSystemUser( 'Maintenance script', [ 'steal' => true ] );
		}

		$this->logger->debug( 'User: ' . $user->getName() );

		$fileList = $this->getFileList( $dirPath );
		foreach ( $fileList as $file ) {
			$result = $this->processFile( $file, $dirPath, $user );

			if ( !$result ) {
				$status->error( RawMessage::newFromKey( $file->getBasename() ) );
				$this->logger->warning( "File {$file->getBasename()} was not correctly processed." );
			} else {
				$this->logger->debug( "File {$file->getBasename()} processed." );
			}
		}

		return $status;
	}

	/**
	 * @param string $dirPath
	 * @return SplFileInfo[]
	 */
	private function getFileList( string $dirPath ): array {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dirPath ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$files = [];
		foreach ( $iterator as $path => $file ) {
			if ( $file instanceof SplFileInfo === false || $file->isDir() ) {
				continue;
			}
			$files[$file->getPathname()] = $file;
		}

		ksort( $files, SORT_NATURAL );
		return $files;
	}

	/**
	 * @param SplFileInfo $file
	 * @param string $dirPath
	 * @param User $user
	 *
	 * @return bool
	 */
	private function processFile( SplFileInfo $file, string $dirPath, User $user ): bool {
		$relPath = str_replace(
			$dirPath,
			'',
			str_replace( '\\', '/', $file->getRealPath() )
		);
		$relPath = trim( $relPath, '/' );

		$parts = explode( '/', $relPath );
		$root = $parts[0];
		$title = implode( '_', $parts );

		// MediaWiki normalizes multiple spaces/undescores into one single score/underscore
		$title = str_replace( ' ', '_', $title );
		$title = preg_replace( '#(_)+#si', '_', $title );
		$parts = explode( '_', $title );

		$targetTitle = Title::makeTitle( NS_FILE, $title );
		$repo = $this->repoGroup->getLocalRepo();

		$this->hookContainer->run( 'BSImportFilesMakeTitle', [
			$this,
			&$targetTitle,
			&$repo,
			$parts,
			$root
		] );

		$repoFile = $repo->newFile( $targetTitle );

		/*
		 * The following code is almost a direct copy of
		 * <mediawiki>/maintenance/importImages.php
		 */

		$mwProps = new MWFileProps( $this->mimeAnalyzer );
		$props = $mwProps->getPropsFromPath( $file->getPathname(), true );
		$flags = 0;
		$publishOptions = [];
		$handler = MediaHandler::getHandler( $props['mime'] );
		if ( $handler ) {
			$metadata = AtEase::quietCall( 'unserialize', $props['metadata'] );

			$publishOptions['headers'] = $handler->getContentHeaders( $metadata );
		} else {
			$publishOptions['headers'] = [];
		}
		$archive = $repoFile->publish( $file->getPathname(), $flags, $publishOptions );
		if ( !$archive->isGood() ) {
			return false;
		}

		$commentText = SpecialUpload::getInitialPageText();

		$repoFile->recordUpload3( $archive->value, '', $commentText, $user, $props );

		return true;
	}

	/**
	 * @param string $filePath
	 *
	 * @return Status
	 */
	public function importPages( string $filePath ): Status {
		$this->logger->debug( 'Importing pages from XML: ' . $filePath );

		$status = ImportStreamSource::newFromFile( $filePath );
		if ( !$status->isGood() ) {
			$this->logger->error( 'Failed to create source object...' );
			return $status;
		}

		$source = $status->getValue();

		try {
			$wikiImporter = new WikiImporter( $source, $this->config );
			$wikiImporter->doImport();
		} catch ( Exception $ex ) {
			$this->logger->error( 'Error during import:' . $ex->getMessage() );
			return Status::newFatal( $ex->getMessage() );
		}

		return Status::newGood();
	}

	/**
	 * @param string $workspaceDir
	 *
	 * @return void
	 */
	public function removeTemporaryFiles( string $workspaceDir ): void {
		// Delete source and all intermediate (like data buckets) files and directories
		wfRecursiveRemoveDir( $workspaceDir );

		$this->logger->debug( 'Temporary files removed.' );
	}
}

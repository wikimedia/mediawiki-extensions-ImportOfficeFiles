<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcess;

use MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcessHandler;

class ImportPagesHandler extends ImportProcessHandler {

	/**
	 * @inheritDoc
	 */
	public function run() {
		$request = $this->getRequest();

		$uploadId = $request->getPathParam( "uploadId" );

		$this->logger->debug( "Start importing pages for upload ID '$uploadId'." );

		$this->workspace->init( $uploadId, $this->workspaceDir );
		$xmlPath = $this->workspace->getPath() . '/result/import.xml';

		$status = $this->importer->importPages( $xmlPath );
		if ( !$status->isGood() ) {
			return $this->getResponseFactory()->createJson( [
				'success' => false,
				'errors' => $status->getErrors()
			] );
		}

		return $this->getResponseFactory()->createJson( [
			'success' => true
		] );
	}
}

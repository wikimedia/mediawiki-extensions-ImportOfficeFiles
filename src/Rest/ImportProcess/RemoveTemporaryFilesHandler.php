<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcess;

use MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcessHandler;

class RemoveTemporaryFilesHandler extends ImportProcessHandler {

	/**
	 * @inheritDoc
	 */
	public function run() {
		$request = $this->getRequest();

		$uploadId = $request->getPathParam( "uploadId" );

		$this->logger->debug( "Start removing temporary files for upload ID '$uploadId'." );

		$this->importer->removeTemporaryFiles( $this->workspaceDir . '/' . $uploadId );

		return $this->getResponseFactory()->createJson( [
			'success' => true
		] );
	}
}

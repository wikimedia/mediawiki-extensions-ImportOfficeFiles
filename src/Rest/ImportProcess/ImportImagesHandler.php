<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcess;

use MediaWiki\Extension\ImportOfficeFiles\Rest\ImportProcessHandler;
use RequestContext;

class ImportImagesHandler extends ImportProcessHandler {

	/**
	 * @inheritDoc
	 */
	public function run() {
		$request = $this->getRequest();

		$uploadId = $request->getPathParam( "uploadId" );

		$this->logger->debug( "Start importing images for upload ID '$uploadId'." );

		$context = RequestContext::getMain();
		$user = $context->getUser();
		if ( $user->isAnon() ) {
			$user = null;
		}

		$this->workspace->init( $uploadId, $this->workspaceDir );
		$imagesDir = $this->workspace->getPath() . '/result/images/';

		// Fix for Windows path
		$imagesDir = str_replace( '\\', '/', $imagesDir );

		// There is an issue in the farm, somewhere in the path slashes are duplicated
		// Like that: "$IP/_sf_instances//Instance_2/images"
		// It will break images namings, so get rid of that here
		$imagesDir = str_replace( '//', '/', $imagesDir );

		$status = $this->importer->importImages( $imagesDir, $user );
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

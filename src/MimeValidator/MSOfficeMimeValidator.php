<?php

namespace MediaWiki\Extension\ImportOfficeFiles\MimeValidator;

use MediaWiki\Extension\ImportOfficeFiles\IModuleMimeValidator;
use MediaWiki\MediaWikiServices;
use MimeAnalyzer;
use SplFileInfo;

class MSOfficeMimeValidator implements IModuleMimeValidator {

	/**
	 * @return array
	 */
	protected function getSupportedMimeTypes(): array {
		return [
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
		];
	}

	/**
	 * @param SplFileInfo $file
	 * @return bool
	 */
	public function canHandle( $file ): bool {
		$services = MediaWikiServices::getInstance();
		/** @var MimeAnalyzer */
		$mimeAnalyzer = $services->getService( 'MimeAnalyzer' );
		$mimeType = $mimeAnalyzer->getMimeTypeFromExtensionOrNull( $file->getExtension() );

		$supportedMimeTypes = $this->getSupportedMimeTypes();
		if ( in_array( $mimeType, $supportedMimeTypes ) ) {
			return true;
		}
		return false;
	}
}

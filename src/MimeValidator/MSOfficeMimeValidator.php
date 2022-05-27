<?php

namespace MediaWiki\Extension\ImportOfficeFiles\MimeValidator;

use MediaWiki\Extension\ImportOfficeFiles\IModuleMimeValidator;

class MSOfficeMimeValidator implements IModuleMimeValidator {

	/**
	 * @return array
	 */
	protected function getSupportedMimeTypes(): array {
		return [
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
		];
	}

	/**
	 * @param SplFile $file
	 * @return bool
	 */
	public function canHandle( $file ): bool {
		$mimeType = mime_content_type( $file->getPathname() );
		$supportedMimeTypes = $this->getSupportedMimeTypes();
		if ( in_array( $mimeType, $supportedMimeTypes ) ) {
			return true;
		}
		return false;
	}
}

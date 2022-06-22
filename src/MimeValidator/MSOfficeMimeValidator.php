<?php

namespace MediaWiki\Extension\ImportOfficeFiles\MimeValidator;

use Config;
use MediaWiki\Extension\ImportOfficeFiles\IModuleMimeValidator;
use MediaWiki\MediaWikiServices;
use MimeAnalyzer;
use SplFileInfo;

class MSOfficeMimeValidator implements IModuleMimeValidator {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

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
		$verifyMimeType = $this->config->get( 'VerifyMimeType' );

		if ( $verifyMimeType === true ) {
			// Mime type check can be disabled because not all docx documents do have the correct mimetype.

			$extension = $file->getExtension();
			if ( strtolower( $extension ) === 'docx' ) {
				return true;
			}

			return false;
		}

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

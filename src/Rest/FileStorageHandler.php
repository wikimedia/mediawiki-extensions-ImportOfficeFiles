<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Rest;

use Config;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use Message;
use MWCryptRand;
use RequestContext;
use UploadBase;
use UploadFromFile;

class FileStorageHandler extends SimpleHandler {

	/**
	 * @var string
	 */
	private $uploadDirectory = '';

	/**
	 *
	 * @param Config $config
	 */
	public function __construct( $config ) {
		$this->uploadDirectory = $config->get( 'UploadDirectory' );
	}

	/**
	 * @return Response
	 */
	public function run(): Response {
		$request = $this->getRequest();
		$files = $request->getUploadedFiles();

		if ( empty( $files ) ) {
			throw new HttpException( Message::newFromKey(
				"importofficefiles-api-storage-error-no-available-file"
			) );
		}

		$context = RequestContext::getMain()->getRequest();
		$upload = $context->getUpload( 'file' );
		$uploadFromFile = new UploadFromFile();
		$uploadFromFile->initialize(
			$files['file']->getClientFilename(),
			$upload
		);
		$res = $uploadFromFile->verifyUpload();

		if ( $res['status'] != 0 ) {
			if ( $res['status'] === UploadBase::VERIFICATION_ERROR && !empty( $res['details'] ) ) {
				$message = Message::newFromKey( $res['details'][0] );
				unset( $res['details'][0] );

				$message->params( ...$res['details'] );
			} else {
				$messageKey = $uploadFromFile->getVerificationErrorCode( $res['status'] );
				$message = Message::newFromKey( $messageKey );
			}

			throw new HttpException( $message );
		}

		// Just to avoid collision case, we need to make sure that this ID is not used yet
		while ( true ) {
			$uniqueID = MWCryptRand::generateHex( 6 );

			$path = $this->uploadDirectory . '/cache/' . $uniqueID . '/';

			if ( !file_exists( $path ) ) {
				break;
			}
		}

		$movable = wfMkdirParents( $path );

		if ( !$movable ) {
			throw new HttpException( Message::newFromKey(
				"importofficefiles-api-storage-error-save-temp"
			) );
		}

		$files['file']->moveTo( $path . $files['file']->getClientFilename() );

		return $this->getResponseFactory()->createJson( [
			'success' => true,
			'uploadId' => $uniqueID,
			'filename' => $files['file']->getClientFilename()
		] );
	}

}

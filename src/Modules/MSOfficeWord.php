<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Modules;

use MediaWiki\Extension\ImportOfficeFiles\Analyzer\MSOfficeWordAnalyzer;
use MediaWiki\Extension\ImportOfficeFiles\Converter\MSOfficeWordConverter;
use MediaWiki\Extension\ImportOfficeFiles\IAnalyzer;
use MediaWiki\Extension\ImportOfficeFiles\IConverter;
use MediaWiki\Extension\ImportOfficeFiles\IModuleMimeValidator;
use MediaWiki\Extension\ImportOfficeFiles\MimeValidator\MSOfficeMimeValidator;

class MSOfficeWord extends ModuleBase {

	public const BUCKET_ANALYZER = 'analyzer';
	public const BUCKET_ANALYZER_PARAMS = 'analyzer-params';
	public const BUCKET_CONVERTER = 'converter';
	public const BUCKET_CONVERTER_PARAMS = 'converter-params';
	public const BUCKET_CONVERTER_MEDIA_MAP = 'converter-media-map';
	public const BUCKET_IMPORT_MEDIA_FILENAME_FILEPATH = 'import-media-filename-filepath';
	public const BUCKET_MEDIA_FILENAME_FILEPATH = 'media-filename-filepath';
	public const BUCKET_MEDIA_ID_FILENAME = 'media-id-filename';
	public const BUCKET_MEDIA_FILE_EXTENSIONS = 'media-file-extensions';
	public const BUCKET_STYLES = 'styles';
	public const BUCKET_RELATIONS = 'relations';
	public const BUCKET_SEGMENTS = 'segments';
	public const BUCKET_TITLE_MAP = 'title-map';
	public const BUCKET_CONVERTED_TITLE_FILEPATH = 'import-title-filepath';

	/**
	 * @return IAnalyzer
	 */
	public function getAnalyzer(): IAnalyzer {
		return new MSOfficeWordAnalyzer();
	}

	/**
	 * @return IConverter
	 */
	public function getConverter(): IConverter {
		// TODO: Do we better use workspace directory instead of file?
		return new MSOfficeWordConverter();
	}

	/**
	 * @return IModuleMimeValidator
	 */
	protected function getMimeValidator(): IModuleMimeValidator {
		return new MSOfficeMimeValidator();
	}
}

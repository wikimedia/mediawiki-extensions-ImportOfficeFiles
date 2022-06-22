<?php

namespace MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor;

use FormatJson;
use MediaWiki\Extension\ImportOfficeFiles\FilenameBuilder;
use MediaWiki\Extension\ImportOfficeFiles\IWikiTextProcessor;
use MediaWiki\Extension\ImportOfficeFiles\Modules\MSOfficeWord;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;

class ImageReplacement implements IWikiTextProcessor {

	/**
	 * @var Workspace
	 */
	private $workspace;

	/**
	 * @var array
	 */
	private $idFilenameMap;

	/**
	 * @var string
	 */
	private $baseTitle = '';

	/**
	 * @var string
	 */
	private $namespace = '';

	/**
	 * @var bool
	 */
	private $nsFileRepoCompat = false;

	/**
	 * @param Workspace $workspace
	 */
	public function __construct( Workspace $workspace ) {
		$this->workspace = $workspace;

		$analyzerBucket = $this->workspace->loadBucket( MSOfficeWord::BUCKET_ANALYZER );
		if ( isset( $analyzerBucket['base-title'] ) ) {
			$this->baseTitle = $analyzerBucket['base-title'];
		}
		if ( isset( $analyzerBucket['namespace'] ) ) {
			$this->namespace = $analyzerBucket['namespace'];
		}

		$nsFileRepoCompatBucket = 'false';
		if ( isset( $analyzerBucket['ns-filerepo-compat'] ) ) {
			$nsFileRepoCompatBucket = $analyzerBucket['ns-filerepo-compat'];
		}
		if ( $nsFileRepoCompatBucket === 'true' ) {
			$this->nsFileRepoCompat === true;
		}

		$this->idFilenameMap = $this->workspace->loadBucket( MSOfficeWord::BUCKET_MEDIA_ID_FILENAME );
	}

	/**
	 * @param string $wikiText
	 * @return string
	 */
	public function process( string $wikiText ): string {
		$matches = [];
		preg_match_all( "/###PRESERVEIMAGE (.*?)###/m", $wikiText, $matches );
		foreach ( $matches[1] as $match ) {
			$imageProps = [];
			if ( count( $matches ) > 0 ) {
				$imageProps = FormatJson::decode( $match, true );
			} else {
				continue;
			}

			if ( !isset( $imageProps['id'] ) ) {
				continue;
			}
			$id = $imageProps['id'];

			$props = '';

			$width = '';
			if ( isset( $imageProps['width'] ) ) {
				$width = (int)$imageProps['width'];
			}

			$height = '';
			if ( isset( $imageProps['height'] ) ) {
				$height = (int)$imageProps['height'];
			}

			// Only image with height < 32 px should be inline
			if ( isset( $imageProps['inline'] ) && ( $height <= 32 ) ) {
				$props .= '|border';
			} else {
				$props .= '|thumb|center';
			}

			if ( !empty( $width ) && !empty( $height ) ) {
				$props .= "|{$width}x{$height}px";
			} elseif ( !empty( $width ) ) {
				$props .= "|x{$width}px";
			} elseif ( !empty( $height ) ) {
				$props .= "|{$height}px";
			}

			if ( isset( $this->idFilenameMap[$id] ) ) {
				$filename = $this->idFilenameMap[$id];
				$filenameBuilder = new FilenameBuilder();
				$wikiFilename = $filenameBuilder->build(
					$this->namespace, $this->baseTitle, $filename, $this->nsFileRepoCompat
				);

				$replacement = "[[File:{$wikiFilename}{$props}]]";

				$regexMatch = preg_quote( $match );
				$wikiText = preg_replace( "/###PRESERVEIMAGE $regexMatch###/m", $replacement, $wikiText );

				$this->workspace->addToBucket(
					MSOfficeWord::BUCKET_CONVERTER_MEDIA_MAP,
					[
						$filename => $wikiFilename
					]
				);
			}
		}
		$this->workspace->saveBucket( MSOfficeWord::BUCKET_CONVERTER_MEDIA_MAP );
		return $wikiText;
	}
}

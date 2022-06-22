<?php

namespace MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor;

use MediaWiki\Extension\ImportOfficeFiles\IWikiTextProcessor;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;

class RemoveIllegalMarkup implements IWikiTextProcessor {

	/**
	 * @var Workspace
	 */
	private $workspace;

	/**
	 * @param Workspace $workspace
	 */
	public function __construct( Workspace $workspace ) {
		$this->workspace = $workspace;
	}

	/**
	 * @param string $wikiText
	 * @return string
	 */
	public function process( string $wikiText ): string {
		$wikiText = $this->removeDoubleBold( $wikiText );
		$wikiText = $this->removeDoubleItalic( $wikiText );
		$wikiText = $this->removeNumberedHeading( $wikiText );

		return $wikiText;
	}

	/**
	 * @param string $wikiText
	 * @return string
	 */
	private function removeDoubleBold( string $wikiText ): string {
		$wikiText = str_replace( "''''''", '', $wikiText );

		return $wikiText;
	}

	/**
	 * @param string $wikiText
	 * @return string
	 */
	private function removeDoubleItalic( string $wikiText ): string {
		$wikiText = str_replace( "''''", '', $wikiText );

		return $wikiText;
	}

	/**
	 * @param string $wikiText
	 * @return string
	 */
	private function removeNumberedHeading( string $wikiText ): string {
		$regEx = "/(#+)(\s*?)(=+)([^=]*)(=+)/s";
		$matches = [];
		$status = preg_match_all( $regEx, $wikiText, $matches );

		if ( !$status || ( $status === 0 ) ) {
			return $wikiText;
		}

		$countMatches = count( $matches[0] );

		for ( $index = 0; $index < $countMatches; $index++ ) {
			$curHeading = $matches[0][$index];

			$curText = $matches[3][$index];
			$curText .= $matches[4][$index];
			$curText .= $matches[5][$index];

			$wikiText = preg_replace( "/$curHeading/s", $curText, $wikiText );
		}

		return $wikiText;
	}
}

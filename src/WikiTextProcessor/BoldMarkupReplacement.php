<?php

namespace MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor;

use MediaWiki\Extension\ImportOfficeFiles\IWikiTextProcessor;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;

class BoldMarkupReplacement implements IWikiTextProcessor {

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
		$wikiText = str_replace( '###PRESERVEBOLD######PRESERVEBOLD###', '', $wikiText );

		$matches = explode( '###PRESERVEBOLD###', $wikiText );
		if ( !$matches || count( $matches ) <= 1 ) {
			return $wikiText;
		}

		$matches = array_filter( $matches, static function ( $item ) {
			return !empty( $item );
		} );

		$wikiTextLength = strlen( $wikiText );
		$placeholderLength = strlen( '###PRESERVEBOLD###' );
		if ( strpos( $wikiText, '###PRESERVEBOLD###', $wikiTextLength - $placeholderLength ) ) {
			array_push( $matches, '' );
		}

		$wikiText = implode( "'''", $matches );

		return $wikiText;
	}
}

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
		$wikiText = str_replace( "''''''", '', $wikiText );
		$wikiText = str_replace( "''''", '', $wikiText );

		return $wikiText;
	}
}

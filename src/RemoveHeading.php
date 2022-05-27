<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

class RemoveHeading {

	/**
	 * @param string $level
	 * @param string $heading
	 * @param string $wikiText
	 * @return string
	 */
	public function execute( $level, $heading, $wikiText ): string {
		$sanitizer = new SanitizeHeading();
		$sanitizedWikiText = $sanitizer->execute( $level, $wikiText );

		$pos = strpos( $heading, ':' );
		if ( $pos ) {
			$heading = substr( $heading, $pos + 1 );
		}

		$headingParts = explode( '/', $heading );
		if ( $headingParts > 1 ) {
			$heading = array_pop( $headingParts );
		}

		$regEx = "/^={" . $level . "}(" . $heading . ")={" . $level . "}/m";
		$newWikiText = preg_replace( $regEx, '', $sanitizedWikiText );

		if ( !$newWikiText ) {
			return $wikiText;
		}

		return $newWikiText;
	}
}

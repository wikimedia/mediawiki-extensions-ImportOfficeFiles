<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

class ImportXmlBuilder {

	/**
	 * @param string $title
	 * @param string $namespace
	 * @param string $wikiText
	 * @return string
	 */
	public function buildPageXml( $title, $namespace, $wikiText ): string {
		$xml = '<page>
		  <title>' . $title . '</title>
		  <ns>' . $namespace . '</ns>
		  <revision>
			<model>wikitext</model>
			<format>text/x-wiki</format>
			<text bytes="' . strlen( $wikiText ) . '" xml:space="preserve">
			' . htmlspecialchars( $wikiText ) . '
			</text>
		  </revision>
		</page>';
		return $xml;
	}

	/**
	 * @param string[] $pageXmls
	 * @return string
	 */
	public function buildImportXml( $pageXmls ): string {
		$xml = '<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.11/" ';
		$xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
		$xml .= 'xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.11/ ';
		$xml .= 'http://www.mediawiki.org/xml/export-0.11.xsd" ';
		$xml .= 'version="0.11" xml:lang="en-GB">';
		foreach ( $pageXmls as $pageXml ) {
			$xml .= "\n$pageXml";
		}
		$xml .= "\n</mediawiki>";
		return $xml;
	}
}

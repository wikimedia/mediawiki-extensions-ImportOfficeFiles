<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

use DOMNode;
use DOMNodeList;
use MediaWiki\Extension\ImportOfficeFiles\Reader\Component\Word2007DocumentData;

interface ITagProcessor {

	/**
	 * @param DOMNode $node
	 * @return string
	 */
	public function process( DOMNode $node ): string;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getNamespace(): string;

	/**
	 * @param DOMNode $node
	 * @return DOMNodeList
	 */
	public function getProcessableElementsFromDocument( $node ): DOMNodeList;

	/**
	 * @param Word2007DocumentData $documentData
	 * @return void
	 */
	public function setDocumentData( Word2007DocumentData $documentData );
}

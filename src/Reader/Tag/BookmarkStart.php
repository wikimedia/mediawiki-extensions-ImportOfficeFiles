<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Reader\Tag;

use DOMNode;

class BookmarkStart extends TagProcessorBase {

	/**
	 * @inheritDoc
	 */
	public function process( DOMNode $node ): string {
		$name = $node->getAttribute( 'w:name' );

		// Prepend "<span>" with anchor (id) to the parent node
		$anchorParentNode = $node->parentNode;

		$anchorNode = $node->ownerDocument->createElement( 'span' );
		$anchorNode->setAttribute( 'class', 'bookmark' );
		$anchorNode->setAttribute( 'id', $name );

		$anchorParentNode->insertBefore( $anchorNode, $node );

		return "";
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return 'bookmarkStart';
	}

	/**
	 * @inheritDoc
	 */
	public function getNamespace(): string {
		return 'w';
	}
}

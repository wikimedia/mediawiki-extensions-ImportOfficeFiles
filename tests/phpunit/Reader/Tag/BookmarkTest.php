<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Tests\Reader\Tag;

use DOMNodeList;

class BookmarkTest extends TagProcessorTestBase {

	/**
	 * @inheritDoc
	 */
	protected function performChecks( string $wikiTextReplacement, DOMNodeList $documentElements ) {
		$bookmarkStart = $documentElements->item( 0 )->getElementsByTagName( 'bookmarkStart' );
		$bookmarkEnd = $documentElements->item( 0 )->getElementsByTagName( 'bookmarkEnd' );
		$anchorSpan = $documentElements->item( 0 )->getElementsByTagName( 'span' );

		// "Bookmark start" node should not be removed by "Bookmark" processor
		// It is replaced with a "<wikitext>" node in conversion process
		$this->assertSame( 1, $bookmarkStart->length );

		$this->assertSame( 1, $anchorSpan->length );

		$anchorId = $anchorSpan->item( 0 )->getAttribute( 'id' );
		$this->assertEquals( '_onnpgudyhc4g', $anchorId );
	}

	/**
	 * @inheritDoc
	 */
	protected function getProcessorName(): string {
		return 'bookmarkStart';
	}

	/**
	 * @inheritDoc
	 */
	protected function getTestInputFileName(): string {
		return 'bookmark.input.xml';
	}

	/**
	 * @inheritDoc
	 */
	protected function getTestResultFileName(): string {
		return '';
	}
}

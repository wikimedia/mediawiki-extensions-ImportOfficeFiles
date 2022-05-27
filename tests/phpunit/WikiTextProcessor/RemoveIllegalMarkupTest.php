<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Tests\WikiTextProcessor;

use MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\RemoveIllegalMarkup;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\RemoveIllegalMarkup
 */
class RemoveIllegalMarkupTest extends TestCase {

	/**
	 * @covers \MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\RemoveIllegalMarkup::process()
	 * @return void
	 */
	public function testProcess() {
		$workspaceMock = $this->createMock( Workspace::class );

		$wikitext = $this->getTestText();

		$imageReplacement = new RemoveIllegalMarkup( $workspaceMock );
		$actualWikitext = $imageReplacement->process( $wikitext );

		$expectedWikitext = $this->getExpectedText();

		$this->assertEquals( $expectedWikitext, $actualWikitext );
	}

	/**
	 * @return string
	 */
	private function getTestText(): string {
		$text = "Lorem ipsum dolor ''''''sit amet,";
		$text .= " consectetuer ''''adipiscing elit.";
		return $text;
	}

/**
 * @return string
 */
	private function getExpectedText(): string {
		$text = "Lorem ipsum dolor sit amet,";
		$text .= " consectetuer adipiscing elit.";
		return $text;
	}
}

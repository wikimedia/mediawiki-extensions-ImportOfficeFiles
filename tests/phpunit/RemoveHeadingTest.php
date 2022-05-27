<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Tests\Reader\PostProcessor;

use MediaWiki\Extension\ImportOfficeFiles\RemoveHeading;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\ImportOfficeFiles\TitleMapBulderTest
 */
class RemoveHeadingTest extends TestCase {
	/**
	 * @covers \MediaWiki\Extension\ImportOfficeFiles\TitleMapBulderTest::build
	 */
	public function testExecute() {
		$removeHeading = new RemoveHeading();

		$expectedWikiText = "\nHello world!\n===Second heading===\nHello again\n===My heading===\nTest";
		$wikiText = '==<span style="abc:def">' . 'My heading</span>==' . "$expectedWikiText";

		$actualWikiText = $removeHeading->execute(
			'2',
			'TestCase:My heading',
			$wikiText
		);

		$this->assertEquals( $expectedWikiText, $actualWikiText );

		$expectedWikiText = "\nHello world!\n===Second heading===\nHello again\n===My heading 2===\nTest";
		$wikiText = '===<span style="abc:def">' . 'My heading</span>===' . "$expectedWikiText";

		$actualWikiText = $removeHeading->execute(
			'3',
			'TestCase:Heading test/My heading',
			$wikiText
		);

		$this->assertEquals( $expectedWikiText, $actualWikiText );
	}
}

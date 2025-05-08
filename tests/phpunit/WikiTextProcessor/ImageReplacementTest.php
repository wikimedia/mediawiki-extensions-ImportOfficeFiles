<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Tests\WikiTextProcessor;

use MediaWiki\Extension\ImportOfficeFiles\Modules\MSOfficeWord;
use MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\ImageReplacement;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\ImageReplacement
 */
class ImageReplacementTest extends TestCase {

	/**
	 * @covers \MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\ImageReplacement::process()
	 * @return void
	 */
	public function testProcess() {
		$workspaceMock = $this->createMock( Workspace::class );
		$workspaceMock->method( 'loadBucket' )->willReturnCallback( static function ( $name ) {
			if ( $name === MSOfficeWord::BUCKET_ANALYZER ) {
				return [
					'base-title' => 'Some base title',
					'namespace'  => 'TestCase',
				];
			}
			if ( $name === MSOfficeWord::BUCKET_MEDIA_ID_FILENAME ) {
				return [
					'rId10' => 'filename1.jpg',
					'rId11' => 'filename2.png'
				];
			}

			return [];
		} );

		$wikitext = 'Hello ###PRESERVEIMAGE {"id":"rId10","inline":true}###';
		$wikitext .= ' world ';
		$wikitext .= '###PRESERVEIMAGE {"width":"100", "height":"200","id":"rId11","anchor":true}### !';
		$wikitext .= ' ###PRESERVEIMAGE {"width":"100", "height":"30","id":"rId11","inline":true}### !';

		$imageReplacement = new ImageReplacement( $workspaceMock, 700 );
		$actualWikitext = $imageReplacement->process( $wikitext );

		$expectedWikitext = 'Hello [[File:TestCase_Some_base_title_filename1.jpg|border]]';
		$expectedWikitext .= ' world ';
		$expectedWikitext .= '[[File:TestCase_Some_base_title_filename2.png|thumb|center|100x200px]] !';
		$expectedWikitext .= ' [[File:TestCase_Some_base_title_filename2.png|border|100x30px]] !';

		$this->assertEquals( $expectedWikitext, $actualWikitext );
	}

	/**
	 * Checks that threshold for images works correctly.
	 * So if width is bigger than this configured threshold - width will be "cut" to threshold value,
	 * and height will be omitted for image to scale properly.
	 *
	 * Here 2 images are processed.
	 * First one is wider than threshold, so will be scaled.
	 * Second one is smaller than threshold, so will be processed normally.
	 *
	 * @covers \MediaWiki\Extension\ImportOfficeFiles\WikiTextProcessor\ImageReplacement::process()
	 * @return void
	 */
	public function testThreshold() {
		$workspaceMock = $this->createMock( Workspace::class );
		$workspaceMock->method( 'loadBucket' )->willReturnCallback( static function ( $name ) {
			if ( $name === MSOfficeWord::BUCKET_MEDIA_ID_FILENAME ) {
				return [
					'rId20' => 'filename1.jpg',
					'rId21' => 'filename2.png'
				];
			}

			return [];
		} );

		$wikitext = '###PRESERVEIMAGE {"width":"800", "height":"200","id":"rId20","anchor":true}###';
		$wikitext .= '###PRESERVEIMAGE {"width":"100", "height":"200","id":"rId21"}###';

		$imageReplacement = new ImageReplacement( $workspaceMock, 700 );
		$actualWikitext = $imageReplacement->process( $wikitext );

		// First image is too wide, so will be scaled to threshold.
		$expectedWikitext = '[[File:_filename1.jpg|thumb|center|x700px]]';
		// Second image will be processed normally.
		$expectedWikitext .= '[[File:_filename2.png|thumb|center|100x200px]]';

		$this->assertEquals( $expectedWikitext, $actualWikitext );
	}
}

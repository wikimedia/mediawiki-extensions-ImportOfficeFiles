<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Tests\Reader\Property;

use MediaWiki\Extension\ImportOfficeFiles\ITagPropertyProcessor;
use MediaWiki\Extension\ImportOfficeFiles\Reader\Property\JustifyContent;
use MediaWiki\Extension\ImportOfficeFiles\Reader\Tag\HtmlWrapper;

/**
 * @covers \MediaWiki\Extension\ImportOfficeFiles\Reader\Property\JustifyContent
 */
class JustifyContentTest extends TagPropertyProcessorTestBase {

	/**
	 * @inheritDoc
	 */
	protected function getProcessor(): ITagPropertyProcessor {
		return new JustifyContent();
	}

	/**
	 * @inheritDoc
	 */
	protected function getProperties(): array {
		return [
			'w:jc' => [
				'w:val' => 'center'
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlWrapper(): ?HtmlWrapper {
		return new HtmlWrapper( 'p' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getExpectedWikiText(): string {
		return '<p style=\'width: 100%;text-align: center;\'> process me</p>';
	}
}

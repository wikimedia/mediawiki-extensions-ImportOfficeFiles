<?php

use MediaWiki\Extension\ImportOfficeFiles\Importer;
use MediaWiki\MediaWikiServices;

return [
	'ImportOfficeFilesImporter' => static function () {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$mimeAnalyzer = MediaWikiServices::getInstance()->getMimeAnalyzer();

		return new Importer( $config, $repoGroup, $hookContainer, $mimeAnalyzer );
	}
];

<?php

namespace MediaWiki\Extension\ImportOfficeFiles\MediaWiki\HookHandler;

use MediaWiki\Hook\BeforePageDisplayHook;

class AddBootstrap implements BeforePageDisplayHook {

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( "ext.importofficefiles.bootstrap" );
	}
}

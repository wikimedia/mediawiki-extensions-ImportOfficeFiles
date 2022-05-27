<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Modules;

use MediaWiki\Extension\ImportOfficeFiles\IModule;
use MediaWiki\Extension\ImportOfficeFiles\Workspace;

abstract class ModuleBase implements IModule {

	/**
	 * @var Workspace
	 */
	protected $workspace = null;

	/**
	 * @return IModule
	 */
	public static function factory(): IModule {
		return new static();
	}

	/**
	 * @return bool
	 */
	public function canHandle(): bool {
		$mimeValidator = $this->getMimeValidator();
		return $mimeValidator->canHandle( $this->workspace->getSourceFile() );
	}

	/**
	 * @param Workspace $workspace
	 * @return void
	 */
	public function setWorkspace( $workspace ) {
		$this->workspace = $workspace;
	}
}

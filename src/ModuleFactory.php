<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

use Exception;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;

class ModuleFactory {

	/**
	 * @param Workspace $workspace
	 * @return IModule|null
	 */
	public function getModule( $workspace ): ?IModule {
		$extensionRegistry = ExtensionRegistry::getInstance();
		$moduleSpecs = $extensionRegistry->getAttribute(
			'ImportOfficeFilesModuleRegistry'
		);

		$module = null;
		$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();
		foreach ( $moduleSpecs as $name => $specs ) {
			$curModule = $objectFactory->createObject( $specs );

			if ( !( $curModule instanceof IModule ) ) {
				throw new Exception( "$name is not instance of IModule" );
			}

			$curModule->setWorkspace( $workspace );
			if ( $curModule->canHandle() ) {
				$module = $curModule;
				break;
			}
		}

		if ( $module === null ) {
			throw new Exception( "No module defined for this file type" );
		}

		return $module;
	}
}

<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\ImportOfficeFiles\MediaWiki\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;

class AddContentActions implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @var PermissionManager
	 */
	private $permissionManager;

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param \SkinTemplate $skinTemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $skinTemplate, &$links ): void {
		$user = $skinTemplate->getUser();
		if ( !$this->permissionManager->userHasRight( $user, 'createpage' ) ) {
			return;
		}

		$links['actions']['import_word'] = [
			'text' => $skinTemplate->getContext()
				->msg( "importofficefiles-ui-action-new-import-text" )->text(),
			'title' => $skinTemplate
				->getContext()->msg( "importofficefiles-ui-action-new-import-title" )->text(),
			'href' => '#',
			'class' => 'new-import',
			'id' => 'ca-import-word'
		];
	}
}

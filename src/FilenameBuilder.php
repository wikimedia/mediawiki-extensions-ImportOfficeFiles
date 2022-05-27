<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

class FilenameBuilder {

	/**
	 * @param string $namespace
	 * @param string $baseTitle
	 * @param string $filename
	 * @param bool $nsFileRepoCompat
	 * @return string
	 */
	public function build(
		string $namespace, string $baseTitle, string $filename, bool $nsFileRepoCompat = false
		): string {
		if ( $namespace !== '' ) {
			if ( $nsFileRepoCompat === true ) {
				$namespace = $namespace . ':';
			} else {
				$namespace = $namespace . '_';
			}
		}
		$title = $namespace . $baseTitle;

		// Remove "." if baseTitle is filename of source document
		$title = str_replace( '.', '_', $title );

		$filename = "$title $filename";
		$filename = str_replace( [ ' ', '/' ], '_', $filename );

		// TODO: Use WindowsFilename builder of HalloWelt\MediaWiki\Lib\Migration\WindowsFilename
		#$filename = new WindowsFilename( $filename );

		return (string)$filename;
	}
}

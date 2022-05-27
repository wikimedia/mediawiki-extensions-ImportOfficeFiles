<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

use MediaWiki\MediaWikiServices;

class CollectionBuilder {

	/**
	 * @param SegmentList $segments
	 * @return string
	 */
	public function build( SegmentList $segments ): string {
		$services = MediaWikiServices::getInstance();

		/** var TitleFactory */
		$titleFactory = $services->get( 'TitleFactory' );

		$wikiText = '';
		for ( $index = 0; $index < $segments->count(); $index++ ) {
			$segment = $segments->item( $index );
			// TODO: Improve level handling, for now level 1 is ignored for splitting
			// See WikiPageStructureBuilder
			if ( $segment->getLevel() === 0 ) {
				$wikiText .= "*";
			} elseif ( $segment->getLevel() === 1 ) {
				continue;
			} else {
				$titleParts = explode( '/', $segment->getLabel() );
				$segmentlevel = count( $titleParts );
				for ( $level = 0; $level < $segmentlevel; $level++ ) {
					$wikiText .= "*";
				}
			}

			$title = $titleFactory->newFromText( $segment->getLabel() );
			$target = $title->getFullText();
			$titleText = $title->getText();

			$labelParts = explode( '/', $titleText );
			$label = array_pop( $labelParts );

			$wikiText .= " [[$target|$label]]\n";
		}
		return $wikiText;
	}
}

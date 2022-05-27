<?php

namespace MediaWiki\Extension\ImportOfficeFiles\Reader\Tag;

use DOMNode;
use DOMNodeList;
use DOMXPath;

class Table extends TagProcessorBase {

	/**
	 * @param DOMNode $node
	 * @return string
	 */
	public function process( DOMNode $node ): string {
		$wikitext = $this->processTable( $node );
		$wikitext .= "\n";
		return $wikitext;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'tbl';
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string {
		return 'w';
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNodeList
	 */
	public function getProcessableElementsFromDocument( $node ): DOMNodeList {
		$xpath = new DOMXPath( $node->ownerDocument );
		$xpath->registerNamespace( 'w', 'w' );
		$nodePath = $node->getNodePath();
		$liveList = $xpath->query( $nodePath . '/w:tbl' );
		if ( !$liveList ) {
			return new DOMNodeList();
		}
		return $liveList;
	}

	/**
	 * @param DOMNode $node
	 * @return string
	 */
	private function processTable( $node ): string {
		$node = $this->replaceTableCells( $node );
		$node = $this->replaceTableRows( $node );
		$node = $this->replaceTable( $node );

		$wikiText = '{| class="wikitable"';
		$wikiText .= "\n";
		$wikiText .= $node->textContent;
		$wikiText .= "|}";

		return $wikiText;
	}

	/**
	 * @param DOMNode $node
	 * @param string $path
	 * @return array
	 */
	private function nodesNonLiveList( $node, $path ) {
		$xpath = new DOMXPath( $node->ownerDocument );
		$nodePath = $node->getNodePath();
		$liveList = $xpath->query( $nodePath . $path );
		$nonLiveList = [];
		foreach ( $liveList as $liveNode ) {
			$nonLiveList[] = $liveNode;
		}
		return $nonLiveList;
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	private function replaceTableCells( $node ) {
		$nonLiveList = $this->nodesNonLiveList( $node, '/w:tr/w:tc' );

		foreach ( $nonLiveList as $nonLiveNode ) {
			$relacementNode = $this->replaceTableCell( $nonLiveNode );
			$nonLiveNode->parentNode->replaceChild( $relacementNode, $nonLiveNode );
		}
		return $node;
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	private function replaceTableCell( $node ) {
		$xpath = new DOMXPath( $node->ownerDocument );
		$nodePath = $node->getNodePath();
		$liveList = $xpath->query( $nodePath . '/w:tbl' );
		$nodeText = '';
		if ( $liveList && $liveList->count() > 0 ) {
			$nonLiveList = [];
			foreach ( $liveList as $liveNode ) {
				$nonLiveList[] = $liveNode;
			}
			foreach ( $nonLiveList as $nonLiveNode ) {
				$nodeText .= $this->processTable( $nonLiveNode );
			}
		} else {
			$nodeText = $this->getWikiTextNodeContent( $node );
		}
		$relacementNode = $node->ownerDocument->createElement( 'wikitext', "|$nodeText\n" );
		return $relacementNode;
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	private function replaceTableRows( $node ) {
		$nonLiveList = $this->nodesNonLiveList( $node, '/w:tr' );
		foreach ( $nonLiveList as $nonLiveNode ) {
			$relacementNode = $this->replaceTableRow( $nonLiveNode );
			$nonLiveNode->parentNode->replaceChild( $relacementNode, $nonLiveNode );
		}
		return $node;
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	private function replaceTableRow( $node ) {
		$nodeText = $this->getWikiTextNodeContent( $node );
		$relacementNode = $node->ownerDocument->createElement( 'wikitext', "|-\n$nodeText" );
		return $relacementNode;
	}

	/**
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	private function replaceTable( $node ) {
		$nodeText = $this->getWikiTextNodeContent( $node );
		$relacementNode = $node->ownerDocument->createElement( 'wikitext', "$nodeText" );
		return $relacementNode;
	}
}

<?php

namespace MediaWiki\Extension\ImportOfficeFiles;

class Word2007ConverterParams {

	/**
	 * @var string
	 */
	private $namespace = '';

	/**
	 * @var string[]
	 */
	private $categories = [];

	/**
	 * @var bool
	 */
	private $verbose = false;

	/**
	 * @param array $params
	 */
	public function __construct( $params ) {
		if ( isset( $params['namespace'] ) ) {
			$this->namespace = $params['namespace'];
		}
		if ( isset( $params['categories'] ) ) {
			$this->categories = $params['categories'];
		}
		if ( isset( $params['verbose'] ) ) {
			$this->verbose = $params['verbose'];
		}
	}

	/**
	 * @return int
	 */
	public function getNamespace(): string {
		return $this->namespace;
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return $this->categories;
	}

	/**
	 * @return bool
	 */
	public function getVerbose(): bool {
		return $this->verbose;
	}
}

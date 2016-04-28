<?php
/**
 * Lazy container pattern
 *
 * @author Gabriele Diener <gabriele.diener@caffeina.com>
 */

class AeriaLazy {

	private $dictionary;

	public function __construct( $definitions ){
		$this->dictionary = $definitions;
	}

	public function __get( $n ){
		if ( empty( isset( $this->dictionary[ $n ] ) ) ) return null;
		if ( is_callable( $this->dictionary[ $n ] ) ) {
			$this->dictionary[ $n ] = call_user_func( $this->dictionary[ $n ] );
		}
		return $this->dictionary[ $n ];
	}
}
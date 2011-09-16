<?php
require_once dirname(__FILE__).'/../stream.php';

class infinite_stream_test extends PHPUnit_Framework_TestCase {
	/**
	 * can't actually test this
	 *
	public function test_has_infinite_length() {
		$s = Stream::range();
		$this->assertEquals( 3, $s->length() );
	}
	*/
	
	public function test_taking_n_items_returns_finite_stream() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$vals = array();
		Stream::range()->take( 10 )->walk( $capture );
		$this->assertEquals( range( 1, 10 ), $vals );
	}
	
	public function test_empty_stream_tail_promise() {
		$s = new Stream( 10, function() {
			return new Stream();
		});
		$this->assertEquals( 10, $s->head() );
		$this->assertTrue( $s->tail()->blank() );
	}
	
	/**
	 * @expectedException Exception
	 */
	public function test_get_tail_or_head_of_empty_stream_throws_exception() {
		$s = new Stream( 10, function() {
			return new Stream();
		});
		$this->assertTrue( $s->tail()->tail() );
		$this->assertTrue( $s->tail()->head() );
	}
	
	public function test_chain_finite_streams_into_empty_stream() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$t = new Stream( 10, function () {  
			return new Stream( 20, function () {  
				return new Stream( 30, function () {  
					return new Stream();  
				});  
			});  
		});
		$vals = array();
		$t->walk( $capture );
		$this->assertEquals( array( 10, 20, 30 ), $vals );
	}
	
	public function test_anonymously_recursive_tail_promise_is_ok() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$ones = function() use ( &$ones ) {  
			return new Stream( 1, $ones );  
		};
		$vals = array();
		$ones()->take(5)->walk( $capture );
		$this->assertEquals( array( 1, 1, 1, 1, 1 ), $vals );
	}
	
	function test_anonymously_recursive_tail_promise_with_base_stream_for_adding() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$ones = function() use ( &$ones ) {  
			return new Stream( 1, $ones );  
		};
		$naturals = function() use ( $ones, &$naturals ) {  
			return new Stream( 1,  
				function () use( $ones, &$naturals ) {  
					return $ones()->add( $naturals() );  
				}   
			);  
		};
		$vals = array();
		$naturals()->take(5)->walk( $capture );
		$this->assertEquals( array( 1, 2, 3, 4, 5 ), $vals );
	}
	
	/**
	 * an absolutely necessary unit test
	 */
	function test_eratosthenes_sieve() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		function sieve( $s ) {
			$h = $s->head();
			return new Stream( $h, function() use ( $h, $s ) {
				return sieve( $s->tail()->filter( function( $x ) use ( $h ) {
					return ( $x % $h != 0 );
				}));
			});
		}
		$vals = array();
		sieve( Stream::range( 2 ) )->take( 10 )->walk( $capture );
		$this->assertEquals( array( 2, 3, 5, 7, 11, 13, 17, 19, 23, 29 ), $vals );
	}
}
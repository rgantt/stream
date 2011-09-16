<?php
require_once dirname(__FILE__).'/../stream.php';

class finite_stream_test extends PHPUnit_Framework_TestCase {
	private function make_static_stream() {
		return Stream::make( 10, 20, 30 );
	}
	
	private function make_range_stream() {
		return Stream::range( 10, 20 );
	}

	public function test_has_finite_length() {
		$s = $this->make_static_stream();
		$this->assertEquals( 3, $s->length() );
	}
	
	public function test_has_correct_head() {
		$s = $this->make_static_stream();
		$this->assertEquals( 10, $s->head() );
	}
	
	public function test_has_correct_values() {
		$s = $this->make_static_stream();
		$this->assertEquals( 10, $s->item( 0 ) );
		$this->assertEquals( 20, $s->item( 1 ) );
		$this->assertEquals( 30, $s->item( 2 ) );
	}
	
	public function test_array_access_gives_correct_values() {
		$s = $this->make_static_stream();
		$this->assertEquals( 10, $s[0] );
		$this->assertEquals( 20, $s[1] );
		$this->assertEquals( 30, $s[2] );		
	}
	
	public function test_car_cdr_behavior() {
		$t = $this->make_static_stream()->tail();
		$this->assertEquals( 20, $t->head() );
		$u = $t->tail();
		$this->assertEquals( 30, $u->head() );
		$v = $u->tail();
		$this->assertTrue( $v->blank() );
	}
	
	public function test_range_gives_range() {
		$s = $this->make_range_stream();
		$i = 10;
		foreach( $s as $v ) {
			$this->assertEquals( $i++, $v );
		}
	}
	
	public function test_map_applies_lambda_to_stream() {
		$double = function ( $x ) {
			return 2 * $x;
		};
		$i = 10;
		$d = $this->make_range_stream()->map( $double );
		foreach( $d as $v ) {
			$this->assertEquals( $v, $double( $i++ ) );
		}
	}
	
	public function test_filter_drops_elements_from_stream() {
		$odd = function ( $x ) {
			return !( $x % 2 == 0 );
		};
		$o = Stream::range( 10, 15 )->filter( $odd );
		$filtered = array();
		foreach( $o as $v ) $filtered[] = $v;
		$this->assertEquals( array( 11, 13, 15 ), $filtered );
	}
	
	public function test_walk_runs_lambda_over_every_element() {
		$vals = array();
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$n = $this->make_range_stream()->walk( $capture );
		$this->assertEquals( range( 10, 20 ), $vals );
	}
	
	public function test_take_grabs_n_numbers() {
		$n = Stream::range( 10, 100 );
		$fewer = $n->take( 10 );
		$this->assertEquals( 10, $fewer->length() );
		
		$vals = array();
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$fewer->walk( $capture );
		$this->assertEquals( range( 10, 19 ), $vals );
	}
	
	public function test_scale_multiplies_stream_by_constant() {
		$capture = function ( $x ) use ( &$vals ) {
			$vals[] = $x;
		};
		$s = Stream::range( 1, 3 );
		$m = $s->scale( 10 );
		
		$vals = array();
		$m->walk( $capture );
		$this->assertEquals( array( 10, 20, 30 ), $vals );
		
		$vals = array();
		$s->add( $m )->walk( $capture );
		$this->assertEquals( array( 11, 22, 33 ), $vals );
	}
}
<?php
require_once "stream.php";

echo "\nFirst\n";

$s = Stream::make( 10, 20, 30 );  
echo $s->length()."\n";  // outputs 3  
echo $s->head()."\n";    // outputs 10  
echo $s->item(0)."\n"; // exactly equivalent to the line above  
echo $s->item(1)."\n"; // outputs 20  
echo $s->item(2)."\n"; // outputs 30

echo "\nSecond\n";

$s = Stream::make( 10, 20, 30 );  
$t = $s->tail();         // returns the stream that contains two items: 20 and 30  
echo $t->head()."\n";  // outputs 20  
$u = $t->tail();         // returns the stream that contains one item: 30  
echo $u->head()."\n";  // outputs 30  
$v = $u->tail();         // returns the empty stream  
echo $v->blank()."\n"; // prints true

echo "\nThird\n";

$s = Stream::make( 10, 20, 30 );  
while ( !$s->blank() ) {  
    echo $s->head()."\n";  
    $s = $s->tail();  
}

echo "\nFourth\n";

$s = Stream::range( 10, 20 );  
$s->out(); // prints the numbers from 10 to 20  

echo "\nFifth\n";

$doubleNumber = function( $x ) {  
    return 2 * $x;
};
  
$numbers = Stream::range( 10, 15 );  
$numbers->out(); // prints 10, 11, 12, 13, 14, 15  
$doubles = $numbers->map( $doubleNumber );  
$doubles->out(); // prints 20, 22, 24, 26, 28, 30  

echo "\nSixth\n";

$checkIfOdd = function( $x ) {  
    if ( $x % 2 == 0 ) {  
        return false;  
    } else {  
        return true;  
    }  
};

$numbers = Stream::range( 10, 15 );  
$numbers->out();  // prints 10, 11, 12, 13, 14, 15  
$onlyOdds = $numbers->filter( $checkIfOdd );  
$onlyOdds->out(); // prints 11, 13, 15  

echo "\nSeventh\n";

$printItem = function( $x ) {  
    echo "The element is: {$x}\n";  
};

$numbers = Stream::range( 10, 12 );  
// prints:  
// The element is: 10  
// The element is: 11  
// The element is: 12  
$numbers->walk( $printItem );  

echo "\nEigth:\n";

$numbers = Stream::range( 10, 100 ); // numbers 10...100  
$fewerNumbers = $numbers->take( 10 ); // numbers 10...19  
$fewerNumbers->out();

echo "\nNinth\n";

$numbers = Stream::range( 1, 3 );  
$multiplesOfTen = $numbers->scale( 10 );  
$multiplesOfTen->out(); // prints 10, 20, 30  
$numbers->add( $multiplesOfTen )->out(); // prints 11, 22, 33  

echo "\nTenth\n";

$naturalNumbers = Stream::range(); // returns the stream containing all natural numbers from 1 and up  
$oneToTen = $naturalNumbers->take( 10 ); // returns the stream containing the numbers 1...10  
$oneToTen->out(); 

echo "\nEleventh\n";

$naturalNumbers = Stream::range(); // naturalNumbers is now 1, 2, 3, ...  
$evenNumbers = $naturalNumbers->map( function ( $x ) {  
    return ( 2 * $x );  
} ); // evenNumbers is now 2, 4, 6, ...  
$oddNumbers = $naturalNumbers->filter( function ( $x ) {  
    return ( $x % 2 != 0 );  
} ); // oddNumbers is now 1, 3, 5, ...  
$evenNumbers->take( 3 )->out(); // prints 2, 4, 6  
$oddNumbers->take( 3 )->out(); // prints 1, 3, 5 

echo "\nTwelfth\n";

$s = new Stream( 10, function () {  
    return new Stream();  
});  
// the head of the s stream is 10; the tail of the s stream is the empty stream  
$s->out(); // prints 10  
$t = new Stream( 10, function () {  
    return new Stream( 20, function () {  
        return new Stream( 30, function () {  
            return new Stream();  
        });  
    });  
});  
// the head of the t stream is 10; its tail has a head which is 20 and a tail which  
// has a head which is 30 and a tail which is the empty stream.  
$t->out(); // prints 10, 20, 30  

echo "\nThirteenth\n";

$ones = function() use ( &$ones ) {  
    return new Stream(  
        // the first element of the stream of ones is 1...  
        1,  
        // and the rest of the elements of this stream are given by calling the function ones() (this same function!)  
        $ones  
    );  
};
  
$s = $ones();      // now s contains 1, 1, 1, 1, ...  
$s->take( 3 )->out(); // prints 1, 1, 1 

echo "\nFourteenth\n";

$ones = function() use ( &$ones ) {  
    return new Stream( 1, $ones );  
};
  
$naturalNumbers = function() use ( $ones, &$naturalNumbers ) {  
    return new Stream(  
        // the natural numbers are the stream whose first element is 1...  
        1,  
        function () use( $ones, &$naturalNumbers ) {  
            // and the rest are the natural numbers all incremented by one  
            // which is obtained by adding the stream of natural numbers...  
            // 1, 2, 3, 4, 5, ...  
            // to the infinite stream of ones...  
            // 1, 1, 1, 1, 1, ...  
            // yielding...  
            // 2, 3, 4, 5, 6, ...  
            // which indeed are the REST of the natural numbers after one  
            return $ones()->add( $naturalNumbers() );  
        }   
    );  
};

$naturalNumbers()->take( 5 )->out(); // prints 1, 2, 3, 4, 5  

echo "\nFifteenth\n";

function sieve( $s ) {
	$h = $s->head();
	return new Stream( $h, function() use ( $h, $s ) {
		return sieve( $s->tail()->filter( function( $x ) use ( $h ) {
			return ( $x % $h != 0 );
		}));
	});
}

$primes = sieve( Stream::range( 2 ) )->take( 10 );
foreach ( $primes as $prime ) {
    echo $prime . "\n";
}

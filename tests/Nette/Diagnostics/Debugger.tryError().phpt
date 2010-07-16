<?php

/**
 * Test: Nette\Diagnostics\Debugger::tryError() & catchError.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @subpackage UnitTests
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';


function test1()
{
	try {
		rename('..', '..'); // E_WARNING
	} catch (\ErrorException $e) {
		return $e;
	}
}

/** @warnings */
function test2()
{
	try {
		rename('..', '..'); // E_WARNING
	} catch (\ErrorException $e) {
		return $e;
	}
}

/** @warnings */
function test3()
{
	try {
		@rename('..', '..'); // E_WARNING
	} catch (\ErrorException $e) {
		return $e;
	}
}

/** @warnings */
function test4()
{
	try {
		$a++;
	} catch (\ErrorException $e) {
		return $e;
	}
}

Assert::null( test1() );

Assert::true( test2() instanceof \ErrorException );

Assert::true( test3() instanceof \ErrorException );

Assert::null( test4() );

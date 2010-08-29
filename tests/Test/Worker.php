<?php

/**
 * Test worker.
 *
 * @copyright  Copyright (c) 2010 Jakub Kulhan
 * @package    Nette\Test
 */

require __DIR__ . '/TestCase.php';



function quit($msg)
{
	fprintf(STDERR, "worker(%d): %s\n", getmypid(), $msg);
	die(1);
}



while (TRUE) {
	$r = array(STDIN); $w = NULL; $e = NULL;
	if (($changed = stream_select($r, $w, $e, NULL)) === FALSE) {
		quit('stream_select() failed');
	}

	if (strlen($data = fread(STDIN, 4)) !== 4) {
		if (strlen($data) === 0 && feof(STDIN)) {
			return 0;
		}

		quit('fread(1) failed');
	}

	list(,$n) = unpack('N', $data);

	if (strlen($data = fread(STDIN, $n)) !== $n) {
		quit('fread(2) failed');
	}

	$msg = unserialize($data);
	$response = NULL;

	if ($msg instanceof TestCase) {
		try {
			$msg->run();
			$response = $msg;
		} catch (Exception $e) {
			$response = $e;
		}

	} else {
		quit('Unknown message');
	}

	$serialized = serialize($response);
	$data = pack('N', strlen($serialized)) . $serialized;

	if (fwrite(STDOUT, $data) !== strlen($data)) {
		quit('fwrite() failed.');
	}
}

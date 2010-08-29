<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * @package    Nette\Test
 */

require __DIR__ . '/TestCase.php';



/**
 * Test runner.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestRunner
{
	/** @var string  path to test file/directory */
	public $path;

	/** @var resource */
	private $logFile;

	/** @var string  php-cgi binary */
	public $phpBinary;

	/** @var string  php-cgi command-line arguments */
	public $phpArgs;

	/** @var string  php-cgi environment variables */
	public $phpEnvironment;

	/** @var bool  display skipped tests information? */
	public $displaySkipped = FALSE;

	/** @var int jobs count */
	public $jobs = 1;

	/**
	 * Runs all tests.
	 * @return void
	 */
	public function run()
	{
		$count = 0;
		$failed = $passed = $skipped = array();
		$available = $working = $queued = array();

		// prepare files
		if (is_file($this->path)) {
			$files = array($this->path);

		} else {
			$files = array();

			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path)) as $entry) {
			$entry = (string) $entry;
			$info = pathinfo($entry);
			if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
				continue;
			}

				$files[] = $entry;
			}

			sort($files);
		}

		$files = new ArrayIterator($files);

		// prepare workers
		for ($i = 0; $i < $this->jobs; ++$i) {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				// FIXME: depends on PHP being in %Path
				$cmd = 'php' . ' ' . escapeshellarg(__DIR__ . '\\Worker.php');
			} else {
				$cmd = 'exec ' . escapeshellarg($_SERVER['_']) . ' ' . escapeshellarg(__DIR__ . '/Worker.php');
			}

			$proc = proc_open($cmd, array(
				0 => array('pipe', 'r'),
				1 => array('pipe', 'w'),
			), $pipes);

			$available[] = $worker = (object) array();
			$worker->process = $proc;
			list($worker->in, $worker->out) = $pipes;
			$worker->queue = array();
		}

		// run tests
		do {
			// collect results
			while ((empty($available) && empty($queued)) || (!$files->valid() && !empty($working))) {
				$r = array();
				$resourceToIndex = array();
				foreach ($working as $index => $worker) {
					$r[] = $worker->out;
					$resourceToIndex[(string) $worker->out] = $index;
				}
				
				$w = NULL; $e = NULL;
				if (($changed = stream_select($r, $w, $e, NULL)) === FALSE) {
					throw new Exception('stream_select() failed.');
				}

				foreach ($r as $resource) {
					if (strlen($data = fread($resource, 4)) !== 4) {
						throw new Exception('fread() failed.');
					}

					list(,$n) = unpack('N', $data);

					if (strlen($data = fread($resource, $n)) !== $n) {
						throw new Exception('fread() failed.');
					}

					$msg = unserialize($data);

					if ($msg instanceof TestCaseException) {
						if ($msg->getCode() === TestCaseException::SKIPPED) {
					$this->out('s');
							$skipped[] = array($msg->getTestName(), $msg->getTestFile(), $msg->getMessage());

				} else {
					$this->out('F');
									$failed[] = array($msg->getTestName(), $msg->getTestFile(), $msg->getMessage());
				}

					} else if ($msg instanceof TestCase) {
						echo '.';
						$passed[] = array($msg->getName(), $msg->getFile());

					} else if ($msg instanceof Exception) {
						throw $msg;

					} else {
						throw new Exception('Unexpected message.');
			}

					$worker = $working[$resourceToIndex[(string) $resource]];
					unset($working[$resourceToIndex[(string) $resource]]);

					if (empty($worker->queue)) {
						$available[] = $worker;

					} else {
						$queued[] = $worker;
		}
				}
			}

			// send work
			foreach ($queued as $worker) {
				$testCase = new TestCase(array_shift($worker->queue));
				$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment);

				$serialized = serialize($testCase);
				$data = pack('N', strlen($serialized)) . $serialized;

				if (fwrite($worker->in, $data) !== strlen($data)) {
					throw new Exception('fwrite() failed.');
				}

				$working[] = $worker;
			}
			$queued = array();

			// queue work
			while (!empty($available) && $files->valid()) {
				++$count;
				$queue = array($file = (string) $files->current());
				$files->next();

				if (preg_match('~\.[0-9]+\.phpt$~', $file, $matches)) {
					$regex = '~^' . preg_quote(substr($file, 0, -strlen($matches[0]))) . '\.[0-9]+\.phpt$~';
					while ($files->valid() && preg_match($regex, (string) $files->current())) {
						++$count;
						$queue[] = (string) $files->current();
						$files->next();
					}
				}

				$worker = array_shift($available);
				$worker->queue = array_merge($worker->queue, $queue);

				$queued[] = $worker;
			}


		} while ($files->valid() || !empty($working) || !empty($queued));

		// kill workers
		foreach ($available as $worker) {
			fclose($worker->in);
			fclose($worker->out);
			proc_close($worker->process);
		}

		// display statistics
		$failedCount = count($failed);
		$skippedCount = count($skipped);

		if ($this->displaySkipped && $skippedCount) {
			$this->out("\n\nSkipped:\n");
			foreach ($skipped as $i => $item) {
				list($name, $file, $message) = $item;
				$this->out("\n" . ($i + 1) . ") $name\n   $message\n   $file\n");
			}
		}

		if (!$count) {
			$this->out("No tests found\n");

		} elseif ($failedCount) {
			$this->out("\n\nFailures:\n");
			foreach ($failed as $i => $item) {
				list($name, $file, $message) = $item;
				$this->out("\n" . ($i + 1) . ") $name\n   $message\n   $file\n");
			}
			$this->out("\nFAILURES! ($count tests, $failedCount failures, $skippedCount skipped)\n");
			return FALSE;

		} else {
			$this->out("\n\nOK ($count tests, $skippedCount skipped)\n");
		}
		return TRUE;
	}



	/**
	 * Parses command line arguments.
	 * @return void
	 */
	public function parseArguments()
	{
		$this->phpBinary = 'php-cgi';
		$this->phpArgs = '';
		$this->phpEnvironment = '';
		$this->path = getcwd(); // current directory

		$args = new ArrayIterator(array_slice(isset($_SERVER['argv']) ? $_SERVER['argv'] : array(), 1));
		foreach ($args as $arg) {
			if (!preg_match('#^[-/][a-z]+$#', $arg)) {
				if ($path = realpath($arg)) {
					$this->path = $path;
				} else {
					throw new Exception("Invalid path '$arg'.");
				}

			} else switch (substr($arg, 1)) {
				case 'p':
					$args->next();
					$this->phpBinary = $args->current();
					break;
				case 'log':
					$args->next();
					$this->logFile = fopen($args->current(), 'w');
					break;
				case 'c':
				case 'd':
					$args->next();
					$this->phpArgs .= " -$arg[1] " . escapeshellarg($args->current());
					break;
				case 'l':
					$args->next();
					$this->phpEnvironment .= 'LD_LIBRARY_PATH='. escapeshellarg($args->current()) . ' ';
					break;
				case 's':
					$this->displaySkipped = TRUE;
					break;
				case 'j':
					$args->next();
					$this->jobs = (int) $args->current();
					if (((string) $this->jobs) !== $args->current()) {
						throw new Exception('Number of jobs has to be a number.');
					}

					if ($this->jobs < 1) {
						throw new Exception('There has to be at least one job.');
					}
					break;
				default:
					throw new Exception("Unknown option $arg.");
					exit;
			}
		}
	}


	/**
	 * Writes to display and log
	 * @return void
	 */
	private function out($s)
	{
		echo $s;
		if ($this->logFile) {
			fputs($this->logFile, $s);
		}
	}

}

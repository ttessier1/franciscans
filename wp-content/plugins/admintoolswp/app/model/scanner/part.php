<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner;


use Akeeba\AdminTools\Admin\Model\Scanner\Exception\ErrorException;
use Akeeba\AdminTools\Admin\Model\Scanner\Logger\Logger;
use Akeeba\AdminTools\Admin\Model\Scanner\Logger\LogLevel;
use Akeeba\AdminTools\Admin\Model\Scanner\Util\Configuration;
use Akeeba\AdminTools\Admin\Model\Scanner\Util\Session;
use Akeeba\AdminTools\Library\Timer\Timer;
use Exception;
use Throwable;

defined('ADMINTOOLSINC') or die;

abstract class Part
{
	const STATE_INIT = 0;
	const STATE_PREPARED = 1;
	const STATE_RUNNING = 2;
	const STATE_POSTRUN = 3;
	const STATE_FINISHED = 4;
	const STATE_ERROR = 99;

	/**
	 * The current state of this part; see the constants at the top of this class
	 *
	 * @var int
	 */
	protected $currentState = self::STATE_INIT;

	/**
	 * The name of the engine part (a.k.a. Domain), used in return table
	 * generation.
	 *
	 * @var string
	 */
	protected $activeDomain = "";

	/**
	 * The step this engine part is in. Used verbatim in return table and
	 * should be set by the code in the _run() method.
	 *
	 * @var string
	 */
	protected $activeStep = "";

	/**
	 * A more detailed description of the step this engine part is in. Used
	 * verbatim in return table and should be set by the code in the _run()
	 * method.
	 *
	 * @var string
	 */
	protected $activeSubstep = "";

	/**
	 * Any configuration parameters
	 *
	 * @var array
	 */
	protected $configurationParameters = [];

	/**
	 * Embedded installer preferences
	 *
	 * @var  object
	 */
	protected $installerSettings;

	/**
	 * How much milliseconds should we wait to reach the min exec time
	 *
	 * @var  int
	 */
	protected $waitTimeMsec = 0;

	/**
	 * Should I ignore the minimum execution time altogether?
	 *
	 * @var  bool
	 */
	protected $ignoreMinimumExecutionTime = false;

	/**
	 * The last exception thrown during the tick() method's execution.
	 *
	 * @var null|Exception
	 */
	protected $lastException = null;
	/**
	 * The timer which controls the execution of this part
	 *
	 * @var Timer
	 */
	protected $timer;

	protected $configuration;
	protected $session;
	protected $logger;

	public function __construct(Configuration $configuration, Session $session, Logger $logger, Timer $timer)
	{
		$this->configuration = $configuration;
		$this->session       = $session;
		$this->logger        = $logger;
		$this->timer         = $timer;

		$this->logger->debug(get_class($this) . " :: new instance");
	}

	/**
	 * The public interface to an engine part. This method takes care for
	 * calling the correct method in order to perform the initialisation -
	 * run - finalisation cycle of operation and return a proper response array.
	 *
	 * @param   int  $nesting
	 *
	 * @return  array  A response array
	 */
	public function tick($nesting = 0)
	{
		$this->waitTimeMsec  = 0;
		$this->lastException = null;

		/**
		 * Call the right action method, depending on engine part state.
		 *
		 * The action method may throw an exception to signal failure, hence the try-catch. If there is an exception we
		 * will set the part's state to STATE_ERROR and store the last exception.
		 */
		try
		{
			switch ($this->getState())
			{
				case self::STATE_INIT:
					$this->_prepare();
					break;

				case self::STATE_PREPARED:
				case self::STATE_RUNNING:
					$this->_run();
					break;

				case self::STATE_POSTRUN:
					$this->_finalize();
					break;
			}
		}
		catch (Exception $e)
		{
			$this->lastException = $e;
			$this->setState(self::STATE_ERROR);
		}

		// Return the output array
		$out = $this->makeReturnTable();

		return $out;
	}

	/**
	 * Returns a copy of the class's status array
	 *
	 * @return  array  The response array
	 */
	public function getStatusArray()
	{
		return $this->makeReturnTable();
	}

	/**
	 * Sends any kind of setup information to the engine part. Using this,
	 * we avoid passing parameters to the constructor of the class. These
	 * parameters should be passed as an indexed array and should be taken
	 * into account during the preparation process only. This function will
	 * set the error flag if it's called after the engine part is prepared.
	 *
	 * @param   array  $parametersArray  The parameters to be passed to the engine part.
	 *
	 * @return  void
	 */
	public function setup($parametersArray)
	{
		if ($this->currentState == self::STATE_PREPARED)
		{
			$this->setState(self::STATE_ERROR);

			throw new ErrorException(__CLASS__ . ":: Can't modify configuration after the preparation of " . $this->activeDomain);
		}

		$this->configurationParameters = $parametersArray;
	}

	/**
	 * Returns the state of this engine part.
	 *
	 * @return  int  The state of this engine part.
	 */
	public function getState()
	{
		if (!is_null($this->lastException))
		{
			$this->currentState = self::STATE_ERROR;
		}

		return $this->currentState;
	}

	/**
	 * Translate the integer state to a string, used by consumers of the public Engine API.
	 *
	 * @param   int  $state  The part state to translate to string
	 *
	 * @return  string
	 */
	public function stateToString($state)
	{
		switch ($state)
		{
			case self::STATE_ERROR:
				return 'error';
				break;

			case self::STATE_INIT:
				return 'init';
				break;

			case self::STATE_PREPARED:
				return 'prepared';
				break;

			case self::STATE_RUNNING:
				return 'running';
				break;

			case self::STATE_POSTRUN:
				return 'postrun';
				break;

			case self::STATE_FINISHED:
				return 'finished';
				break;
		}

		return 'init';
	}

	/**
	 * Get the current domain of the engine
	 *
	 * @return  string  The current domain
	 */
	public function getDomain()
	{
		return $this->activeDomain;
	}

	/**
	 * Get the current step of the engine
	 *
	 * @return  string  The current step
	 */
	public function getStep()
	{
		return $this->activeStep;
	}

	/**
	 * Get the current sub-step of the engine
	 *
	 * @return  string  The current sub-step
	 */
	public function getSubstep()
	{
		return $this->activeSubstep;
	}

	/**
	 * Implement this if your Engine Part can return the percentage of its work already complete
	 *
	 * @return  float  A number from 0 (nothing done) to 1 (all done)
	 */
	public function getProgress()
	{
		return 0;
	}

	/**
	 * Get the value of the minimum execution time ignore flag.
	 *
	 * DO NOT REMOVE. It is used by the Engine consumers.
	 *
	 * @return boolean
	 */
	public function isIgnoreMinimumExecutionTime()
	{
		return $this->ignoreMinimumExecutionTime;
	}

	/**
	 * Set the value of the minimum execution time ignore flag. When set, the nested logging parts (basically,
	 * Kettenrad) will ignore the minimum execution time parameter.
	 *
	 * DO NOT REMOVE. It is used by the Engine consumers.
	 *
	 * @param   boolean  $ignoreMinimumExecutionTime
	 */
	public function setIgnoreMinimumExecutionTime($ignoreMinimumExecutionTime)
	{
		$this->ignoreMinimumExecutionTime = $ignoreMinimumExecutionTime;
	}

	/**
	 * Nested logging of exceptions
	 *
	 * The message is logged using the specified log level. The detailed information of the Throwable and its trace are
	 * logged using the DEBUG level.
	 *
	 * If the Throwable is nested, its parents are logged recursively. This should create a thorough trace leading to
	 * the root cause of an error.
	 *
	 * @param   Exception|Throwable  $exception  The Exception or Throwable to log
	 * @param   int                  $logLevel   The log level to use, default ERROR
	 */
	protected function logErrorsFromException($exception, $logLevel = LogLevel::ERROR)
	{
		$this->logger->log($logLevel, $exception->getMessage());
		$this->logger->debug(sprintf('[%s] %s(%u) – #%u ‹%s›', get_class($exception), $exception->getFile(), $exception->getLine(), $exception->getCode(), $exception->getMessage()));

		foreach (explode("\n", $exception->getTraceAsString()) as $line)
		{
			$this->logger->debug(rtrim($line));
		}

		$previous = $exception->getPrevious();

		if (!is_null($previous))
		{
			$this->logErrorsFromException($previous, $logLevel);
		}
	}

	/**
	 * Runs any initialization code. Must set the state to STATE_PREPARED.
	 *
	 * @return  void
	 */
	abstract protected function _prepare();

	/**
	 * Runs any finalisation code. Must set the state to STATE_FINISHED.
	 *
	 * @return  void
	 */
	abstract protected function _finalize();

	/**
	 * Performs the main objective of this part. While still processing the state must be set to STATE_RUNNING. When the
	 * main objective is complete and we're ready to proceed to finalization the state must be set to STATE_POSTRUN.
	 *
	 * @return  void
	 */
	abstract protected function _run();

	/**
	 * Sets the BREAKFLAG, which instructs this engine part that the current step must break immediately,
	 * in fear of timing out.
	 *
	 * @return  void
	 */
	protected function setBreakFlag()
	{
		$this->session->set('breakFlag', true);
	}

	/**
	 * Sets the engine part's internal state, in an easy to use manner
	 *
	 * @param   int  $state  The part state to set
	 *
	 * @return  void
	 */
	protected function setState($state = self::STATE_INIT)
	{
		$this->currentState = $state;
	}

	/**
	 * Constructs a Response Array based on the engine part's state.
	 *
	 * @return  array  The Response Array for the current state
	 */
	protected function makeReturnTable()
	{
		$errors = [];
		$e      = $this->lastException;

		while (!empty($e))
		{
			$errors[] = $e->getMessage();
			$e        = $e->getPrevious();
		}

		return [
			'HasRun'         => $this->currentState != self::STATE_FINISHED,
			'Domain'         => $this->activeDomain,
			'Step'           => $this->activeStep,
			'Substep'        => $this->activeSubstep,
			'Error'          => implode("\n", $errors),
			'Warnings'       => [],
			'ErrorException' => $this->lastException,
		];
	}

	/**
	 * Set the current domain of the engine
	 *
	 * @param   string  $new_domain  The domain to set
	 *
	 * @return  void
	 */
	protected function setDomain($new_domain)
	{
		$this->activeDomain = $new_domain;
	}

	/**
	 * Set the current step of the engine
	 *
	 * @param   string  $new_step  The step to set
	 *
	 * @return  void
	 */
	protected function setStep($new_step)
	{
		$this->activeStep = $new_step;
	}

	/**
	 * Set the current sub-step of the engine
	 *
	 * @param   string  $new_substep  The sub-step to set
	 *
	 * @return  void
	 */
	protected function setSubstep($new_substep)
	{
		$this->activeSubstep = $new_substep;
	}
}
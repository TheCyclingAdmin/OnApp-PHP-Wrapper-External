<?php
/**
 * Errors handler
 *
 * @package     OnApp
 * @subpackage  Helper
 * @author      Lev Bartashevsky
 * @copyright   (c) 2012 OnApp
 * @link        http://www.onapp.com/
 */
class OnApp_Helper_Handler_Errors {
	/**
	 * @var OnApp_Helper_Handler_Errors
	 */
	private static $instance;

	/**
	 * @var string store display_errors setting
	 */
	private $displayErrors;

	/**
	 * @var array set errors description
	 * @link http://php.net/manual/en/errorfunc.constants.php
	 */
	private $errorsDescriptions = array();

	/**
	 * @var OnApp_Helper_Handler_Log
	 */
	private $logger;

	private function __construct() {
		$this->setErrorsDescriptions();
		$this->displayErrors = ini_get( 'display_errors' );

		// set error reporting level
		error_reporting( E_ALL | E_STRICT );

		// switch off displaying errors
		ini_set( 'display_errors', 0 );
		// set errors handlers
		set_error_handler( array( $this, 'errorHandler' ), E_ALL );
		set_exception_handler( array( $this, 'exceptionHandler' ) );
		register_shutdown_function( array( $this, 'shutdownHandler' ) );

		if( ONAPP_CLI_MODE ) {
			ob_start( array( $this, 'obHandler' ) );
		}
	}

	public function errorHandler( $errorCode, $errorText, $errorFile, $errorLine ) {
		$msg = $this->errorsDescriptions[ $errorCode ] . ': ' . $errorText;
		$msg .= ' in ' . $errorFile . ' on line ' . $errorLine;

		switch( $errorCode ) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_PARSE:
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
				$this->addToLog( $msg, ONAPP_LOGGER_VALUE_ERROR );
				$this->halt();
				break;

			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
			case E_NOTICE:
			case E_USER_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$this->addToLog( $msg, ONAPP_LOGGER_VALUE_WARNING );
				break;

			default:
				$msg .= PHP_EOL . "\t Unknown error type: " . $errorCode;
				$this->addToLog( $msg );
		}
	}

	public function exceptionHandler( Exception $e ) {
		$msg  = 'Uncaught ' . get_class( $e );
		$emsg = $e->getMessage();
		if( ! empty( $emsg ) ) {
			$msg .= ' (' . $emsg . ')';
		}
		$msg .= ' in ' . $e->getFile() . ' on line ' . $e->getLine();

		$this->addToLog( $msg, ONAPP_LOGGER_VALUE_ERROR );
		exit( $msg . PHP_EOL );
	}

	public function obHandler( $ob ) {
		$e = error_get_last();
		if( ! is_null( $e ) && $this->displayErrors ) {
			$msg = $this->errorsDescriptions[ $e[ 'type' ] ] . ': ' . $e[ 'message' ];
			$msg .= ' in ' . $e[ 'file' ] . ' on line ' . $e[ 'line' ] . PHP_EOL;
			$this->addToLog( $msg );
		}
		return $ob;
	}

	public function shutdownHandler() {
		$e = error_get_last();
		if( ! is_null( $e ) ) {
			$msg = $this->errorsDescriptions[ $e[ 'type' ] ] . ': ' . $e[ 'message' ];
			$msg .= ' in ' . $e[ 'file' ] . ' on line ' . $e[ 'line' ];

			switch( $e[ 'type' ] ) {
				case E_ERROR:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_PARSE:
				case E_USER_ERROR:
				case E_RECOVERABLE_ERROR:
					$msg = str_replace( $this->errorsDescriptions[ $e[ 'type' ] ], $this->errorsDescriptions[ $e[ 'type' ] ] . ' (fatal)', $msg );
					$this->addToLog( $msg, ONAPP_LOGGER_VALUE_ERROR );
					$this->halt();
					break;

				case E_WARNING:
				case E_CORE_WARNING:
				case E_COMPILE_WARNING:
				case E_USER_WARNING:
				case E_NOTICE:
				case E_USER_NOTICE:
				case E_STRICT:
				case E_DEPRECATED:
				case E_USER_DEPRECATED:
					$this->addToLog( $msg, ONAPP_LOGGER_VALUE_WARNING );
					break;

				default:
					$msg .= PHP_EOL . "\t Unknown error type: " . $e[ 'type' ];
					$this->addToLog( $msg );
			}
		}
	}

	public function setLog( OnApp_Helper_Handler_Log $log ) {
		$this->logger = $log;
	}

	public static function init() {
		if( is_null( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	private function addToLog( $msg, $type = ONAPP_LOGGER_VALUE_MESSAGE ) {
		if( ! is_null( $this->logger ) ) {
			$call = 'log' . ucfirst( strtolower( $type ) );
			$this->logger->$call( $msg );
		}
	}

	private function halt() {
		if( $this->displayErrors ) {
			if( ONAPP_CLI_MODE ) {
				echo $this->logger->getLog();
			}
			else {
				echo $this->logger->getLogAsHMTL();
			}
			exit;
		}
		else {
			exit;
		}
	}

	private function setErrorsDescriptions() {
		$errorLevels = array(
			'E_ALL',
			'E_USER_DEPRECATED',
			'E_DEPRECATED',
			'E_RECOVERABLE_ERROR',
			'E_STRICT',
			'E_USER_NOTICE',
			'E_USER_WARNING',
			'E_USER_ERROR',
			'E_COMPILE_WARNING',
			'E_COMPILE_ERROR',
			'E_CORE_WARNING',
			'E_CORE_ERROR',
			'E_NOTICE',
			'E_PARSE',
			'E_WARNING',
			'E_ERROR'
		);

		foreach( $errorLevels as $level ) {
			if( defined( $level ) ) {
				$this->errorsDescriptions[ constant( $level ) ] = $level;
			}
		}
	}
}
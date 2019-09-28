<?php

namespace Kyt\Helpers;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Logger
 * @package Kyt\Helpers
 *
 * Standard Singleton
 */
class Logger
{
    /**
     * Whether or not logging is enabled
     * @var bool
     */
    private $_logEnabled = false;

    /**
     * Holds the selected log level
     * @var int
     */
    private $_logLevel = 0;

    /**
     * Holds the system path to the logging directory if logging enabled
     * @var string
     * @see Logger::__construct()
     */
    private $_logDirPath = '';

    /**
     * Internal placeholder
     * @var string
     */
    const PLACEHOLDER = 'x00001';

    /**
     * Holds the reference to the instance of this class
     * @var null|\Kyt\Helpers\Logger
     * @see Logger::getInstance()
     */
    private static $_instance = null;

    /**
     * Logger constructor.
     * @param bool $enableLogging Whether or not to enable logging
     * @param int $logLevel The logging level
     * @throws \Exception
     */
    private function __construct( $enableLogging = LOG_ENABLE, $logLevel = LOG_LEVEL_ERROR )
    {
        $this->_logEnabled = $enableLogging;
        $this->_logLevel = $logLevel;

        if ( $this->_logEnabled ) {
            $this->_logDirPath = LOG_DIR_PATH;
            $this->__checkLogDir();
            ini_set( 'error_log', trailingslashit( $this->_logDirPath ) . 'app.log' );
        }
    }

    /**
     * Retrieve the reference to the instance of this class
     * @param bool $enableLogging Whether or not to enable logging
     * @param int $logLevel The logging level
     * @return Logger|null
     * @throws \Exception
     */
    public static function getInstance( $enableLogging = LOG_ENABLE, $logLevel = LOG_LEVEL_ERROR )
    {
        if ( !self::$_instance || !( self::$_instance instanceof self ) ) {
            self::$_instance = new self( $enableLogging, $logLevel );
        }
        return self::$_instance;
    }

    /**
     * Check to see if the logging directory is accessible
     * @throws \Exception
     */
    private function __checkLogDir()
    {
        if ( !empty( $this->_logDirPath ) ) {
            if ( !is_writable( $this->_logDirPath ) ) {
                throw new \Exception( 'Logger: The specified log directory is not writeable.' );
            }
            elseif ( !is_readable( $this->_logDirPath ) ) {
                throw new \Exception( 'Logger: The specified log directory is not readable.' );
            }
        }
    }

    /**
     * Write a log entry into the appropriate log file
     * @param string $message
     * @param mixed $data
     * @param int $loglevel
     */
    public function write( $message = '', $data = self::PLACEHOLDER, $loglevel = LOG_LEVEL_ERROR )
    {
        if ( !$this->_logEnabled ) {
            return;
        }
        if ( empty( $message ) && ( $data == self::PLACEHOLDER ) ) {
            return;
        }
        if ( $loglevel < $this->_logLevel ) {
            return;
        }
        $logFileName = $this->__getLogFileName( $loglevel );
        if ( !$this->__checkLogFile( $logFileName ) ) {
            return;
        }
        $message = $this->__buildMessage( $message, $data );
        file_put_contents( trailingslashit( $this->_logDirPath ) . $logFileName, $message, FILE_APPEND );
    }

    /**
     * Utility method to output data
     * @param string $message
     * @param string $data
     */
    public function output( $message = '', $data = self::PLACEHOLDER )
    {
        echo '<p><strong>' . $message . '</strong> <pre>' . var_export( $data, 1 ) . '</pre></p>';
    }

    /**
     * Build the log entry message
     * @param string $message
     * @param string $data
     * @return string
     */
    private function __buildMessage( $message, $data = self::PLACEHOLDER )
    {
        $m = '[' . date( 'M j, Y' ) . ']';
        if ( !empty( $message ) ) {
            $m .= " {$message}";
        }
        if ( $data !== self::PLACEHOLDER ) {
            $m .= ' Data: ';
            if ( is_scalar( $data ) ) {
                $m .= $data;
            }
            elseif ( is_resource( $data ) ) {
                $m .= 'RESOURCE';
            }
            else {
                $m .= var_export( $data, 1 );
            }
        }
        return $m . PHP_EOL;
    }

    /**
     * Ensure the log file is accessible
     * @param string $logFileName
     * @return bool
     */
    private function __checkLogFile( $logFileName )
    {
        if ( empty( $logFileName ) ) {
            return false;
        }
        $logFilePath = trailingslashit( $this->_logDirPath ) . $logFileName;
        if ( is_file( $logFilePath ) ) {
            if ( !is_readable( $logFilePath ) ) {
                return false;
            }
            if ( !is_writable( $logFilePath ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retrieve the name of the log file based on the specified log level
     * @param int $loglevel
     * @return string
     */
    private function __getLogFileName( $loglevel = LOG_LEVEL_ERROR )
    {
        if ( !$this->_logEnabled ) {
            return '';
        }
        $logFileName = $this->__translateLogLevel( $loglevel );
        if ( empty( $logFileName ) ) {
            return '';
        }
        return "{$logFileName}.log";
    }

    /**
     * Translate the log level into a human readable form
     * @param int $loglevel
     * @return string
     */
    private function __translateLogLevel( $loglevel = LOG_LEVEL_ERROR )
    {
        global $configLogLevels;
        if ( !is_array( $configLogLevels ) || !isset( $configLogLevels[ 'translation' ] ) ) {
            return '';
        }
        return strtolower( $configLogLevels[ 'translation' ][ $loglevel ] );
    }
}

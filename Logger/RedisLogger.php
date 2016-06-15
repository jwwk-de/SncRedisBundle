<?php

/*
 * This file is part of the SncRedisBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snc\RedisBundle\Logger;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Predis\Command\CommandInterface;

/**
 * RedisLogger
 */
class RedisLogger
{
    const STOPWATCH_CATEGORY = 'redis';

    protected $logger;
    protected $stopwatch;
    protected $nbCommands = 0;
    protected $commands = array();
    protected $start;
    protected $profiledCommand;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger A LoggerInterface instance
     * @param Stopwatch $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger = null, $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * Logs a command
     *
     * @param string      $command    Redis command
     * @param float       $duration   Duration in milliseconds
     * @param string      $connection Connection alias
     * @param bool|string $error      Error message or false if command was successful
     */
    public function logCommand($command, $duration, $connection, $error = false)
    {
        ++$this->nbCommands;

        if (null !== $this->logger) {
            $this->commands[] = array('cmd' => $command, 'executionMS' => $duration, 'conn' => $connection, 'error' => $error);
            if ($error) {
                $this->logger->err('Command "' . $command . '" failed (' . $error . ')');
            } else {
                $this->logger->info('Executing command "' . $command . '"');
            }
        }
    }

    /**
     * Start profiling a new command
     *
     * @param CommandInterface $command A CommandInterface instance
     */
    public function startProfiling(CommandInterface $command){
        $this->profiledCommand = $command;

        if ($this->stopwatch) {
            $this->stopwatch->start($this->profiledCommand->getId(), self::STOPWATCH_CATEGORY);
        }
    }
    /**
     * Stop the active profiling
     */
    public function stopProfiling(){
        if ($this->stopwatch && $this->profiledCommand) {
            $this->stopwatch->stop($this->profiledCommand->getId());
        }
    }

    /**
     * Returns the number of logged commands.
     *
     * @return integer
     */
    public function getNbCommands()
    {
        return $this->nbCommands;
    }

    /**
     * Returns an array of the logged commands.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
}

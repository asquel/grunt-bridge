<?php
namespace AsQuel\Grunt;

use Icecave\Isolator\Isolator;
use RuntimeException;

use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\IO\IOInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;


class GruntBridge
{

    private $executableFinder;
    private $isolator;
    private $gruntPath;

    /**
     * @param GruntBridge|null $instance
     *
     * @return GruntBridge
     */
    public static function get(GruntBridge $instance = null)
    {
        if (null === $instance) {
            $instance = new GruntBridge;
        }

        return $instance;
    }

    /**
     * @param Event $event
     * @param GruntBridge|null $instance
     */
    public static function handle(
        Event $event,
        GruntBridge $instance = null
    ) {
        $instance = static::get($instance);

        switch ($event->getName()) {
            case ScriptEvents::POST_INSTALL_CMD:
                $instance->postInstall($event);
                break;
            case ScriptEvents::POST_UPDATE_CMD:
                $instance->postUpdate($event);
        }
    }

    /**
     * @param ExecutableFinder|null $executableFinder
     * @param Isolator|null $isolator
     */
    public function __construct(
        ExecutableFinder $executableFinder = null,
        Isolator $isolator = null
    ) {
        if (null === $executableFinder) {
            $executableFinder = new ExecutableFinder;
        }

        $this->executableFinder = $executableFinder;
        $this->isolator = Isolator::get($isolator);
    }

    /**
     * @return ExecutableFinder
     */
    public function executableFinder()
    {
        return $this->executableFinder;
    }

    /**
     * @param Event $event
     */
    public function postInstall(Event $event)
    {
        $io = $event->getIO();

        $io->write('<info>Running Grunt default tasks</info>');
        $this->executeGrunt(array(), $io);

    }

    /**
     * @param Event $event
     */
    public function postUpdate(Event $event)
    {
        $io = $event->getIO();

        $io->write('<info>Running Grunt default tasks</info>');
        $this->executeGrunt(array(), $io);
    }

    /**
     * @param array<string> $arguments
     *
     * @return Process
     */
    protected function createGruntProcess(array $arguments)
    {
        array_unshift($arguments, $this->gruntPath());
        $processBuilder = $this->createProcessBuilder($arguments);

        return $processBuilder->getProcess();
    }

    /**
     * @return string
     */
    protected function gruntPath()
    {
        if (null === $this->gruntPath) {
            $this->gruntPath = $this->executableFinder()->find('grunt');
            if (null === $this->gruntPath) {
                throw new RuntimeException('Unable to locate grunt executable.');
            }
        }

        return $this->gruntPath;
    }

    /**
     * @param array<string> $arguments
     * @param IOInterface $io
     */
    protected function executeGrunt(array $arguments, IOInterface $io)
    {
        $gruntProcess = $this->createGruntProcess($arguments);
        $isolator = $this->isolator;
        $gruntProcess->run(function ($type, $buffer) use ($isolator, $io) {
            if (Process::ERR === $type) {
                $isolator->fwrite(STDERR, $buffer);
            } else {
                $io->write($buffer, false);
            }
        });
    }

    /**
     * @param array<string> $arguments
     *
     * @return ProcessBuilder
     */
    protected function createProcessBuilder(array $arguments)
    {
        return new ProcessBuilder($arguments);
    }
}
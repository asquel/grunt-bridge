<?php
/**
 * Created by PhpStorm.
 * User: axelbarbier
 * Date: 3/27/14
 * Time: 11:22 AM
 */

namespace AsQuel\Grunt;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\EventDispatcher\Event;

class GruntBridgePlugin implements PluginInterface{

    public function __construct()
    {
        //nothing to do
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        //nothing to do
    }

    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        );
    }

    public function onPostInstallCmd(Event $event)
    {
        $gruntBridge = new GruntBridge();
        $gruntBridge->postInstall($event);

    }

    public function onPostUpdateCmd(Event $event)
    {
        $gruntBridge = new GruntBridge();
        $gruntBridge->postUpdate($event);
    }
}
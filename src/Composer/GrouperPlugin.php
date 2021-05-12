<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : MIT
 * @update  : 6 mai 2021
 */

namespace Arvodia\Grouper\Composer;

use Arvodia\Grouper\Task;
use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Description
 *
 * @name    : GrouperPlugin
 * @see     :
 * @todo    : add installer
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperPlugin implements PluginInterface, Capable, EventSubscriberInterface {

    private $task;

    public function activate(Composer $composer, IOInterface $io) {
        $this->task = new Task($composer, $io);
    }

    public function deactivate(Composer $composer, IOInterface $io) {
        
    }

    public function uninstall(Composer $composer, IOInterface $io) {
        if (($grouper = new JsonFile(str_replace('composer.json', 'grouper.json', Factory::getComposerFile())))->exists()) {
            @unlink($grouper->getPath());
        }
    }

    public function getCapabilities(): array {
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'Arvodia\Grouper\Composer\CommandProvider',
        );
    }

    public static function getSubscribedEvents() {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackage',
            // run uninstall task, executed by deactivate group
            // PackageEvents::POST_PACKAGE_UNINSTALL => 'onPostPackage',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        ];
    }

    public function onPostPackage(PackageEvent $event) {
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            $package = $operation->getTargetPackage();
        } else {
            $package = $operation->getPackage();
        }
        if ($this->task->hasPackageTask($package->getName())) {
            if ($operation instanceof UpdateOperation) {
                $this->task->setPackagesUpdated($package->getName());
            }
            $this->task->runTasks($package, $operation instanceof UninstallOperation);
        }
    }

    /**
     * @var Event
     * @var ScriptEvents
     * @param Event $event
     */
    public function onPostUpdateCmd(Event $event) {
        $this->task->runGroupsTasks();
    }

}

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

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Composer\Factory;
use Composer\Json\JsonFile;

/**
 * Description
 *
 * @name    : GrouperPlugin
 * @see     :
 * @todo    : add installer
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperPlugin implements PluginInterface, Capable {

    public function activate(Composer $composer, IOInterface $io) {

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

}

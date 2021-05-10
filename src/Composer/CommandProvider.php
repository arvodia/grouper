<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : MIT License
 * @update  : 7 mai 2021
 */

namespace Arvodia\Grouper\Composer;

use Arvodia\Grouper\Command\GrouperCommand;
use Arvodia\Grouper\Command\GrouperGroupCommand;
use Arvodia\Grouper\Command\GrouperInitCommand;
use Arvodia\Grouper\Command\GrouperTaskCommand;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

/**
 * Description
 * 
 * @name    : CommandProvider
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class CommandProvider implements CommandProviderCapability {

    public function getCommands() {
        return [
            new GrouperCommand,
            new GrouperInitCommand,
            new GrouperGroupCommand,
            new GrouperTaskCommand,
        ];
    }

}

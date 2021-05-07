<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : tous droits réservés
 * @update  : 7 mai 2021
 */

namespace Arvodia\Grouper\Composer;

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
        return array(new Command);
    }

}

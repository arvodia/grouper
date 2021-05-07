<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : tous droits réservés
 * @update  : 6 mai 2021
 */

namespace Arvodia\Grouper\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use InvalidArgumentException;

/**
 * Description
 * 
 * @name    : Grouper
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Grouper extends LibraryInstaller {

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package) {
        $prefix = substr($package->getPrettyName(), 0, 23);
        if ('phpdocumentor/template-' !== $prefix) {
            throw new InvalidArgumentException(
                            'Unable to install template, phpdocumentor templates '
                            . 'should always start their package name with '
                            . '"phpdocumentor/template-"'
            );
        }

        return 'data/templates/' . substr($package->getPrettyName(), 23);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType) {
        return 'phpdocumentor-template' === $packageType;
    }

}

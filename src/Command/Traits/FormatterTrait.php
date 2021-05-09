<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : MIT License
 * @update  : 9 mai 2021
 */

namespace Arvodia\Grouper\Command\Traits;

use Arvodia\Grouper\Grouper;

/**
 * Description
 * 
 * @name    : FormatterTrait
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
trait FormatterTrait {

    private function formatGroups(Grouper $grouper, string $group = null, bool $enabled = false): array {
        if ($group && !$grouper->hasGroup($group)) {
            return [];
        }
        $groups = $group ? [$group => $grouper->getGroup($group)] : $grouper->getGroups();
        $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $locker = $this->getComposer()->getLocker();
        $lockData = $locker->getLockData();

        $packages = array_merge($lockData['packages'], $lockData['packages-dev']);

        foreach ($groups as $group => $groupParams) {
            $groupStatus = [];
            foreach ($groups[$group]['require'] = $groupParams['require'] ?? [] as $package => $packageParams) {
                $status = $groupStatus[] = is_null($installedRepo->findPackage($package, '*')) ? 'no installed' : 'installed';
                $groups[$group]['require'][$package] = is_array($packageParams) ? array_merge($packageParams, ['status' => $status]) : [
                    'version' => $packageParams,
                    'status' => $status,
                ];
            }
            $groups[$group]['status'] = empty($groupStatus) || in_array('no installed', $groupStatus) ? 'disable' : 'enabled';
            if ($enabled && 'disable' == $groups[$group]['status']) {
                unset($groups[$group]);
            }
        }
        return $groups;
    }

}

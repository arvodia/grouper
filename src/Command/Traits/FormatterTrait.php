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
        foreach ($groups as $group => $groupConfig) {
            $groupStatus = [];
            foreach ($groups[$group]['require'] = $groupConfig['require'] ?? [] as $package => $packageConfig) {
                $status = $groupStatus[] = is_null($installedRepo->findPackage($package, '*')) ? 'no installed' : 'installed';
                $groups[$group]['require'][$package] = is_array($packageConfig) ? array_merge($packageConfig, ['status' => $status]) : [
                    'version' => $packageConfig,
                    'status' => $status,
                ];
            }
            $groups[$group]['status'] = empty($groupStatus) || in_array('no installed', $groupStatus) ? 'disable' : 'enabled';
            if (!is_null($grouper->isGroupActivated($group)) && $grouper->isGroupActivated($group) !== ('enabled' == $groups[$group]['status'] ? true : false)) {
                $this->getIO()->alert(sprintf('group "%1$s" in grouper.json "%2$s" but these dependencies are "%3$s", use "composer grouper:groups %1$s -vv" for more detail.', $group, $grouper->isGroupActivated($group) ? 'enabled' : 'disable', $groups[$group]['status']));
            }
            if ($enabled && 'disable' == $groups[$group]['status']) {
                unset($groups[$group]);
            }
        }
        return $groups;
    }

}

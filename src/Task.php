<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : MIT License
 * @update  : 10 mai 2021
 */

namespace Arvodia\Grouper;

use Arvodia\Grouper\FileSystems;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Console\Terminal;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Symfony\Component\Console\Input\InputInterface;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

/**
 * Description
 * 
 * @name    : Task
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 * 
 * @var CSS
 * @var JS
 */
class Task {

    private $composer;
    private $rootDir;
    private $vendorDir;
    private $io;
    private $groups = [];
    private $fileSystems;

    public function __construct(Composer $composer, IOInterface $io, InputInterface $input = null) {
        $this->composer = $composer;
        $this->io = $io;
        $this->vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        if (($grouper = new Grouper($input))->exists()) {
            $this->rootDir = $grouper->getRootDir();
            $this->fileSystems = new FileSystems($this->rootDir, $io);
            foreach ($grouper->getGroups() as $group => $groupConfig) {
                if ($grouper->getGroupEnabled($group)) {
                    if (isset($groupConfig['tasks'])) {
                        $this->groups['groups'][$group]['tasks'] = [$groupConfig['tasks']];
                    }
                    foreach ($groupConfig['require'] ?? [] as $package => $packageConfig) {
                        if (isset($packageConfig['tasks'])) {
                            $this->groups['packages'][$package]['tasks'] = array_merge($this->groups['packages'][$package]['tasks'] ?? [], [$packageConfig['tasks']]);
                        }
                    }
                }
            }
        }
    }

    public function hasPackageTask(string $name): bool {
        return isset($this->groups['packages'][$name]['tasks']);
    }

    public function runTasks($toApply, bool $remove = false) {
        if ($this->groups) {
            $type = $toApply instanceof PackageInterface ? 'packages' : 'groups';
            $name = 'groups' == $type ? $toApply : $toApply->getName();
            $packageDir = 'groups' == $type ? null : $this->composer->getInstallationManager()->getInstallPath($toApply);
            $from = 'groups' == $type ? $this->rootDir : $packageDir;
            if (isset($this->groups[$type][$name]['tasks'])) {
                foreach ($this->groups[$type][$name]['tasks'] as $tasks) {
                    foreach ($tasks as $task => $items) {
                        $manifest = [];
                        foreach ($items as $item) {
                            list($source, $dest) = $item;
                            $manifest[$source] = $dest;
                        }
                        if ($manifest) {
                            if ($remove) {
                                $this->fileSystems->removeFiles($manifest, $this->rootDir);
                            } elseif ('file-mapping' == $task) {
                                $this->fileSystems->copyFiles($manifest, $from);
                            } elseif ('css-minifying' == $task) {
                                $this->minifying('css', $manifest, $from);
                            } elseif ('js-minifying' == $task) {
                                $this->minifying('js', $manifest, $from);
                            }
                        }
                    }
                }
            }
        }
    }

    private function minifying(string $minifier, array $manifest, string $from) {
        $minifierBin = $this->vendorDir . '/bin/minify' . $minifier;

        if (!file_exists($minifierBin)) {
            $this->io->warning('[WARNING] The "Minify" bin is part of the MatthiasMullie, which is not installed/enabled; try running "composer require matthiasmullie/minify".');
            $this->io->alert('[Fake] Create Minify Files');
            $this->fileSystems->copyFiles($manifest, $from);
            return;
        }

        $to = $this->rootDir;
        foreach ($manifest as $source => $target) {

            $sourcePath = $this->fileSystems->concatenate([$from, $source]);
            $targetPath = $this->fileSystems->concatenate([$to, $target]);

            if (!$this->fileSystems->checkOverwrite($targetPath)) {
                unset($manifest[$source]);
                continue;
            }

            if (is_file($sourcePath)) {

                if (!is_dir(\dirname($targetPath))) {
                    mkdir(\dirname($targetPath), 0777, true);
                    $this->io->write(sprintf('  [Created] <fg=green>"%s"</>', $this->fileSystems->relativize(\dirname($targetPath))));
                }
                $exec = $minifierBin . ' ' . $sourcePath . ' > ' . $targetPath;

                if (Terminal::exec($exec, null, null, null, null, false)) {
                    @chmod($targetPath, fileperms($targetPath) | (fileperms($sourcePath) & 0111));
                    $this->io->write(sprintf('  [Created] min : <fg=green>"%s"</>', $this->fileSystems->relativize($target)));
                } else {
                    $this->io->error(sprintf('  [Not Copied] Minify File : "%s"', $source));
                    if (FileSystems::FORCE_EXIT) {
                        exit(1);
                    }
                    unset($manifest[$source]);
                    continue;
                }
            } else {
                $this->io->error(sprintf('[Not exist] File : "%s"', $sourcePath));
                if (FileSystems::FORCE_EXIT) {
                    exit(1);
                }
                unset($manifest[$source]);
                continue;
            }
        }
    }

}

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
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;
use Symfony\Component\Console\Input\InputInterface;
use function str_ends_with;
use function str_starts_with;

/**
 * Description
 *
 * @name    : Task
 * @see     :
 * @todo    :
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Task {

    private $composer;
    private $rootDir;
    private $vendorDir;
    private $minify;
    private $io;
    private $groups = [];
    private $fileSystems;
    private $packagesUpdated;
    private $grouper;

    public function __construct(Composer $composer, IOInterface $io, InputInterface $input = null) {
        $this->composer = $composer;
        $this->io = $io;
        $this->vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        if (($this->grouper = $grouper = new Grouper($input))->exists()) {
            $this->rootDir = $grouper->getRootDir();
            $this->fileSystems = new FileSystems($this->rootDir, $io);
            foreach ($grouper->getGroups() as $group => $groupConfig) {
                if ($grouper->isGroupActivated($group)) {
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

    public function setPackagesUpdated(string $package): void {
        $this->packagesUpdated[$package] = true;
    }

    public function runGroupsTasks() {
        foreach ($this->groups['groups'] ?? [] as $group => $values) {
            foreach ($this->grouper->getPackagesByGroup($group) as $package => $value) {
                if ($this->isPackagesUpdated($package)) {
                    $this->runTasks($group);
                    break;
                }
            }
        }
    }

    public function runTasks($toApply, bool $remove = false): void {
        if ($this->groups) {
            $type = $toApply instanceof PackageInterface || $toApply instanceof CompletePackage ? 'packages' : 'groups';
            $name = 'groups' == $type ? $toApply : $toApply->getName();
            $packageDir = 'groups' == $type ? null : $this->composer->getInstallationManager()->getInstallPath($toApply);
            $from = 'groups' == $type ? $this->rootDir : $packageDir;
            if (isset($this->groups[$type][$name]['tasks'])) {
                foreach ($this->groups[$type][$name]['tasks'] as $tasks) {
                    foreach ($tasks as $task => $items) {
                        $manifest = [];
                        foreach ($items as $item) {
                            list($source, $dest) = $item;
                            if (is_array($source)) {
                                // swap
                                $destTmp = $dest;
                                $dest = $source;
                                $source = $destTmp;
                            }
                            $manifest[$source] = $dest;
                        }
                        if ($manifest) {
                            $this->fileSystems->setOverwrite(str_ends_with($task, '-overwrite'));
                            if ($remove) {
                                $this->fileSystems->removeFiles($manifest, $this->rootDir);
                            } elseif (str_starts_with($task, 'file-mapping')) {
                                $this->fileSystems->copyFiles($manifest, $from);
                            } elseif (str_starts_with($task, 'css-minifying')) {
                                $this->minifying('css', $manifest, $from);
                            } elseif (str_starts_with($task, 'js-minifying')) {
                                $this->minifying('js', $manifest, $from);
                            }
                        }
                    }
                }
            }
        }
    }

    private function isPackagesUpdated(string $package): bool {
        return $this->packagesUpdated[$package] ?? false;
    }

    private function minifying(string $minifier, array $manifest, string $from): void {

        if (is_null($this->getMinify($minifier))) {
            $this->io->alert('  [WARNING] The "Minify" class is part of the MatthiasMullie, which is not installed/enabled; try running "composer require matthiasmullie/minify".');
        }
        $to = $this->rootDir;
        foreach ($manifest as $source => $target) {
            if (is_array($target)) {
                if (is_null($this->getMinify($minifier))) {
                    $this->io->alert('  [ERROR] Please install "composer require matthiasmullie/minify".');
                    exit(1);
                }
                // swap
                $targetPath = $this->fileSystems->concatenate([$to, $source]);
                $sourcesPaths = [];
                foreach ($target as $path) {
                    if (!$this->fileSystems->checkOverwrite($path = $this->fileSystems->concatenate([$from, $path]))) {
                        continue;
                    }
                    $sourcesPaths[] = $path;
                }
            } else {
                if (is_null($this->getMinify($minifier))) {
                    $this->io->warning('  [Minify][Fake][Start] Create Fake Minify Files');
                    $this->fileSystems->copyFiles([$source => $target], $from);
                    $this->io->warning('  [Minify][Fake][End]');
                    continue;
                }
                $sourcePath = $this->fileSystems->concatenate([$from, $source]);
                $targetPath = $this->fileSystems->concatenate([$to, $target]);
                if (!$this->fileSystems->checkOverwrite($targetPath)) {
                    continue;
                }
                $sourcesPaths = [$sourcePath];
            }

            if (!is_dir(\dirname($targetPath))) {
                mkdir(\dirname($targetPath), 0777, true);
                $this->io->write(sprintf('  [Created] <fg=green>"%s"</>', $this->fileSystems->relativize(\dirname($targetPath))));
            }

            $minifierClass = 'MatthiasMullie\\Minify\\' . strtoupper($minifier);
            $minifierClass = new $minifierClass();
            $hasMinified = null;
            foreach ($sourcesPaths as $sourcePath) {

                if (!is_file($sourcePath)) {
                    $this->io->error(sprintf('  [NOT EXIST] File : "%s"', $sourcePath));
                    if (FileSystems::FORCE_EXIT) {
                        exit(1);
                    }
                    continue;
                }
                $hasMinified = $hasMinified ?: true;
                $minifierClass->add($sourcePath);
                $this->io->write(sprintf('  [Minify][Add]' . (is_array($target) ? '[Joined]' : '') . ' : <fg=green>"%s"</>', $this->fileSystems->relativize($sourcePath)));
            }

            if ($hasMinified) {
                $minifierClass->minify($targetPath);
                if (is_file($targetPath)) {
                    $this->io->write(sprintf('  [Minify][Created] : <fg=green>"%s"</>', $this->fileSystems->relativize($targetPath)));
                } else {
                    foreach ($sourcesPaths as $sourcePath) {
                        $this->io->error(sprintf('  [Minify][Not Copied] File : "%s"', $this->fileSystems->relativize($sourcePath)));
                        if (FileSystems::FORCE_EXIT) {
                            exit(1);
                        }
                    }
                }
            }
        }
    }

    private function getMinify(string $type): ?string {
        if (is_null($this->minify)) {
            $this->minify = [];
            if (is_file($this->vendorDir . '/autoload.php')) {
                require_once $this->vendorDir . '/autoload.php';
                if (class_exists('MatthiasMullie\\Minify\\CSS')) {
                    $this->minify['css'] = true;
                }
                if (class_exists('MatthiasMullie\\Minify\\JS')) {
                    $this->minify['js'] = true;
                }
            }
        }
        return $this->minify[$type] ?? null;
    }

}

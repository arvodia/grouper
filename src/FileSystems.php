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

use Composer\IO\IOInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Description
 * 
 * @name    : FileSystems
 * @see     : https://github.com/symfony/flex/blob/main/src/Configurator/CopyFromPackageConfigurator.php
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class FileSystems {

    private $rootDir;
    private $io;
    private $overwrite = true;

    /**
     * exit if the file does not exist
     */
    public const FORCE_EXIT = false;

    public function __construct(string $rootDir, IOInterface $io) {
        $this->rootDir = $rootDir;
        $this->io = $io;
    }

    public function copyFiles(array $manifest, string $from) {
        $to = $this->rootDir;
        foreach ($manifest as $source => $target) {
            if (is_dir($sourcePath = $this->concatenate([$from, $source]))) {
                $this->copyDir($sourcePath, $this->concatenate([$to, $target]));
            } else {
                $targetPath = $this->concatenate([$to, $target]);
                if (!is_dir(\dirname($targetPath))) {
                    mkdir(\dirname($targetPath), 0777, true);
                    $this->io->write(sprintf('  [Created] <fg=green>"%s"</>', $this->relativize(\dirname($targetPath))));
                }

                $this->copyFile($sourcePath, $targetPath);
            }
        }
    }

    public function removeFiles(array $manifest, string $workDir) {
        foreach ($manifest as $source => $target) {
            $targetPath = $this->concatenate([$workDir, $target]);
            if (is_dir($targetPath)) {
                $this->removeFilesFromDir($targetPath);
            } else {
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                    $this->io->write(sprintf('  [Removed] <fg=green>"%s"</>', $this->relativize($targetPath)));
                }
            }
        }
    }

    public function relativize(string $absolutePath): string {
        $relativePath = str_replace($this->rootDir, '.', $absolutePath);

        return is_dir($absolutePath) ? rtrim($relativePath, '/') . '/' : $relativePath;
    }

    public function concatenate(array $parts): string {
        $first = array_shift($parts);

        return array_reduce($parts, function (string $initial, string $next): string {
            return rtrim($initial, '/') . '/' . ltrim($next, '/');
        }, $first);
    }

    public function checkOverwrite(string $file): bool {
        if (!file_exists($file)) {
            return true;
        }
        return $this->overwrite;
    }

    private function removeFilesFromDir(string $target) {
        $iterator = $this->createSourceIterator($target, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $targetPath = $this->concatenate([$target, $iterator->getSubPathName()]);
            if ($item->isDir()) {
                @rmdir($targetPath);
                $this->io->write(sprintf('  [Removed] directory <fg=green>"%s"</>', $this->relativize($targetPath)));
            } else {
                @unlink($targetPath);
                $this->io->write(sprintf('  [Removed] <fg=green>"%s"</>', $this->relativize($targetPath)));
            }
        }
    }

    private function copyDir(string $source, string $target) {
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }

        $iterator = $this->createSourceIterator($source, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $targetPath = $this->concatenate([$target, $iterator->getSubPathName()]);
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath);
                    $this->io->write(sprintf('  [Created] <fg=green>"%s"</>', $this->relativize($targetPath)));
                }
            } elseif (!file_exists($targetPath)) {
                $this->copyFile($item, $targetPath);
            }
        }
    }

    private function copyFile(string $source, string $target) {
        if (!$this->checkOverwrite($target)) {
            return;
        }

        if (!file_exists($source)) {
            $this->io->error(sprintf('[Not exist] File : "%s"', $source));
            if (self::FORCE_EXIT) {
                exit(1);
            }
            return;
        }

        if (copy($source, $target)) {
            @chmod($target, fileperms($target) | (fileperms($source) & 0111));
            $this->io->write(sprintf('  [Created] <fg=green>"%s"</>', $this->relativize($target)));
        } else {
            $this->io->error(sprintf('  [Not Copied] File : "%s"', $source));
            if (self::FORCE_EXIT) {
                exit(1);
            }
        }
    }

    private function createSourceIterator(string $source, int $mode): RecursiveIteratorIterator {
        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), $mode);
    }

}

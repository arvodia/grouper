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

namespace Arvodia\Grouper;

use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Description
 * 
 * @name    : Grouper
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Grouper {

    const NAME = 'Grouper';
    const VERSION = '1.1.7';
    const VERSION_NAME = 'started';

    private $json;
    private $grouper = [];
    private $changed = false;
    private $rootDir;

    public function __construct(InputInterface $input = null) {
        $workingDir = $input ? $input->getOption('working-dir') : null;
        $this->rootDir = realpath($workingDir ?: dirname(Factory::getComposerFile()));
        if (($this->json = new JsonFile($this->rootDir . '/grouper.json'))->exists()) {
            $this->grouper = $this->json->read();
        }
        $this->grouper['name'] = $this->grouper['name'] ?? '';
        $this->grouper['groups'] = $this->grouper['groups'] ?? [];
    }

    public static function getLongVersion() {
        return sprintf(
                '<info>%s</info> version <comment>%s</comment> (<comment>%s</comment>)',
                self::NAME,
                self::VERSION,
                self::VERSION_NAME,
        );
    }

    public function getRootDir(): string {
        return $this->rootDir;
    }

    public function exists(): bool {
        return $this->json->exists();
    }

    public function hasChanged(): bool {
        return $this->changed;
    }

    public function getName(): ?string {
        return $this->grouper['name'];
    }

    public function setName(string $name): self {
        $this->grouper['name'] = $name;
        return $this;
    }

    public function hasGroup(string $name): bool {
        return \array_key_exists($name, $this->grouper['groups']);
    }

    public function getGroup(string $name): ?array {
        return $this->grouper['groups'][$name] ?? null;
    }

    public function isGroupActivated(string $name): bool {
        return $this->grouper['groups'][$name]['activated'] ?? false;
    }

    public function setGroupActivated(string $name, bool $enabled): self {
        if (\array_key_exists($name, $this->grouper['groups'])) {
            $this->grouper['groups'][$name]['activated'] = $enabled;
            $this->changed = true;
        }
        return $this;
    }

    public function getGroups(): array {
        return $this->grouper['groups'];
    }

    public function addGroup(string $name, array $data = []): self {
        $current = $this->grouper['groups'][$name] ?? [];
        $this->grouper['groups'][$name] = array_merge($current, $data);
        $this->changed = true;
        return $this;
    }

    public function setGroup(string $name, array $data = []): self {
        if (!\array_key_exists($name, $this->grouper['groups']) || $data !== $this->grouper['groups'][$name]) {
            $this->grouper['groups'][$name] = $data;
            $this->changed = true;
        }
        return $this;
    }

    public function getGroupTaskOption(string $group): array {
        return $this->grouper['groups'][$group]['tasks-option'] ?? [];
    }

    public function setGroupTaskOption(string $group, $option): self {
        $this->grouper['groups'][$group]['tasks-option'] = $option;
        $this->changed = true;
        return $this;
    }

    public function addGroupTask(string $group, string $task, array $data = []): self {
        if (\array_key_exists($group, $this->grouper['groups'])) {
            $current = $this->grouper['groups'][$group]['tasks'][$task] ?? [];
            $this->grouper['groups'][$group]['tasks'][$task] = array_merge($current, [$data]);
            $this->changed = true;
        }
        return $this;
    }

    public function resetGroupTask(string $group): self {
        if (isset($this->grouper['groups'][$group]['tasks'])) {
            unset($this->grouper['groups'][$group]['tasks']);
            $this->changed = true;
        }
        return $this;
    }

    public function addPackageTask(string $group, string $package, string $task, array $data = []): self {
        if (isset($this->grouper['groups'][$group]['require'][$package])) {
            if (!is_array($this->grouper['groups'][$group]['require'][$package])) {
                $this->grouper['groups'][$group]['require'][$package] = [
                    'version' => $this->grouper['groups'][$group]['require'][$package],
                ];
            }
            $current = $this->grouper['groups'][$group]['require'][$package]['tasks'][$task] ?? [];
            $this->grouper['groups'][$group]['require'][$package]['tasks'][$task] = array_merge($current, [$data]);
            $this->changed = true;
        }
        return $this;
    }

    public function resetPackageTask(string $group, string $package): self {
        if (isset($this->grouper['groups'][$group]['require'][$package]['tasks'])) {
            unset($this->grouper['groups'][$group]['require'][$package]['tasks']);
            $this->changed = true;
        }
        return $this;
    }

    public function getPackagesByGroup(string $group): array {
        return $this->grouper['groups'][$group]['require'] ?? [];
    }

    public function addPackage(string $group, $package = []): self {
        $current = $this->grouper['groups'][$group]['require'] ?? [];
        $this->grouper['groups'][$group]['require'] = array_merge($current, $package);
        $this->changed = true;
        return $this;
    }

    public function removePackage(string $group, string $package): self {
        if (\array_key_exists($package, $this->grouper['groups'][$group]['require'])) {
            unset($this->grouper['groups'][$group]['require'][$package]);
            $this->changed = true;
        }
        return $this;
    }

    public function removeGroup(string $name): self {
        if (\array_key_exists($name, $this->grouper['groups'])) {
            unset($this->grouper['groups'][$name]);
            $this->changed = true;
        }
        return $this;
    }

    public function all(): array {
        return $this->grouper;
    }

    public function reset(): void {
        $this->grouper = ['name' => '', 'groups' => []];
    }

    public function save(): void {
        if (!$this->changed) {
            return;
        }

        ksort($this->grouper['groups']);
        $this->json->write($this->grouper);
    }

    public function delete(): void {
        @unlink($this->json->getPath());
    }

}

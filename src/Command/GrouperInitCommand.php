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

namespace Arvodia\Grouper\Command;

use Arvodia\Grouper\Console\Alert;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Text;
use Composer\Json\JsonFile;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description
 *
 * @name    : GrouperInitCommand
 * @see     :
 * @todo    :
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperInitCommand extends RequirementsCommand {

    private $rootDir;
    private $names;
    private $trans;
    private $io;
    private $alert;

    protected function configure() {
        $this->trans = (new Text())->getText('init', 'command');
        $this->setName('grouper:init')
                ->setDescription($this->trans['desc'])
                ->setDefinition([
                    new InputOption('name', null, InputOption::VALUE_REQUIRED, $this->trans['name_option']),
                ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->io = $this->getIO();
        $this->alert = new Alert(new SymfonyStyle($input, $output));
        $formatter = $this->getHelperSet()->get('formatter');

        $this->io->write(array(
            '',
            $formatter->formatBlock($this->trans['welcome'], 'bg=blue;fg=white', true),
            '',
            $this->trans['intro'],
            '',
        ));

        if (($grouper = new Grouper($input))->exists()) {
            $this->alert->warning('file_exists', 'grouper.json');
            $grouper->reset();
        }

        $this->rootDir = $grouper->getRootDir();

        if ($grouperName = $input->getOption('name')) {
            if (!$this->nameValidator($grouperName, 'grouper')) {
                $grouperName = $this->askName('grouper');
            }
            $this->names[] = $grouperName;
            $this->alert->none('ask_name', ['grouper', $grouperName]);
        } else {
            $grouperName = $this->askName('grouper');
        }

        $grouper->setName($grouperName);

        while ($this->io->askConfirmation($this->trans['confirm_add_group_' . (isset($this->names[1]) ? 'another' : 'new')] . PHP_EOL . '>')) {

            $groupName = $this->askName();
            $description = $this->io->ask(
                    $this->trans['ask_group_desc'] . PHP_EOL . '>'
            );

            $data = [];

            $this->io->write(array('', $this->trans['define_dependencies'], ''));
            if ($this->io->askConfirmation($this->trans['confirm_add_require'] . PHP_EOL . '>')) {
                $data['require'] = $this->askRequirements($input, $output);
            }

            $grouper->setGroup($groupName, array_merge(['description' => $description ?: ''], $data));
        }

        $json = JsonFile::encode($grouper->all());

        $this->io->write(array('', $json, ''));

        if (!$this->io->askConfirmation($this->trans['confirm_generation'] . PHP_EOL . '>')) {
            $this->alert->error('command_aborted');
        }

        $grouper->write();

        $this->alert->success('generate_json');
    }

    private function formatDir(string $dir) {
        return strtolower(preg_replace('{(?:([a-z])([A-Z])|([A-Z])([A-Z][a-z]))}', '\\1\\3-\\2\\4', $dir));
    }

    public function nameValidator(string $name, string $type = 'group'): bool {
        if (!preg_match('{^[a-z0-9_.-]+$}D', $name)) {
            $this->alert->error('ask_name_exception', [$type, $name], false);
            return false;
        }
        return true;
    }

    private function askName(string $type = 'group'): string {
        $name = $default = $this->generateName();
        $this->alert->none('ask_name', [$type, $default]);
        $name = $this->io->askAndValidate(
                '>',
                function ($value) use ($name, $type, $default) {
                    if (null === $value) {
                        return $name;
                    }
                    if (!$this->nameValidator($value, $type)) {
                        $this->alert->none('ask_name', [$type, $default]);
                        throw new InvalidArgumentException();
                    }
                    return $value;
                },
                null,
                $default
        );
        return $name;
    }

    private function generateName(): string {
        if (is_null($this->names)) {
            $git = $this->getGitConfig();
            if (!empty($_SERVER['COMPOSER_DEFAULT_VENDOR'])) {
                $name = $_SERVER['COMPOSER_DEFAULT_VENDOR'];
            } elseif (isset($git['github.user'])) {
                $name = $git['github.user'];
            } elseif (!empty($_SERVER['USERNAME'])) {
                $name = $_SERVER['USERNAME'];
            } elseif (!empty($_SERVER['USER'])) {
                $name = $_SERVER['USER'];
            } elseif (get_current_user()) {
                $name = get_current_user();
            } else {
                $name = $this->formatDir(basename($this->rootDir));
            }
            return $this->names[] = $name;
        }
        $name = $tmp = $this->names[1] ?? $this->formatDir(basename($this->rootDir));
        $i = 2;
        while (in_array($name, $this->names)) {
            $name = $tmp . '-' . $i;
            $i++;
        }
        return $this->names[] = $name;
    }

}

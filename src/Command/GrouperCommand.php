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

use Arvodia\Grouper\Command\Traits\FormatterTrait;
use Arvodia\Grouper\Console\Alert;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Text;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description
 * 
 * @name    : GrouperCommand
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperCommand extends BaseCommand {

    use FormatterTrait;

    private $io;
    private $trans;

    protected function configure() {
        $this->trans = (new Text())->getText('groups', 'command');
        $this->setName('grouper:groups')
                ->setAliases(['groups'])
                ->setDescription($this->trans['desc'])
                ->setDefinition([
                    new InputArgument('group', InputArgument::OPTIONAL, $this->trans['argument_group']),
                ])
                ->addOption('enabled', 'e', InputOption::VALUE_NONE, $this->trans['option_enabled'])
                ->addOption('raw', 'r', InputOption::VALUE_NONE, $this->trans['option_raw'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getIO()->write(Grouper::getLongVersion());
        
        $this->io = $this->getIO();
        $formatter = $this->getHelperSet()->get('formatter');
        $alert = new Alert(new SymfonyStyle($input, $output));

        if (!($grouper = new Grouper($input))->exists()) {
            $alert->warning('initiate', null, true);
        }

        $group = strtolower($input->getArgument('group'));

        if ($group && !$grouper->hasGroup($group)) {
            $alert->error('group_not_found', [$group, $grouper->getName()]);
        }

        $groups = $this->formatGroups($grouper, $group, $input->getOption('enabled'));

        $this->io->write(array(
            '',
            $formatter->formatBlock($this->trans[$input->getOption('enabled') ? 'enabled_groups' : 'available_groups'], 'bg=blue;fg=white', true),
            '',
        ));

        if (!$this->io->isVerbose() || !$this->io->isVeryVerbose()) {
            $this->io->writeError([
                'Run command with <info>-v</info> or <info>-vv</info> to see more details',
                '',
            ]);
        }

        if ($input->getOption('raw')) {
            $this->showRawList($groups);
        } else {
            $this->showList($groups);
        }

        $alert->success();
    }

    private function showRawList(array $groups = []): void {
        foreach ($groups as $group => $groupConfigs) {
            $this->io->write(str_pad($group, $pad ?? ($pad = ($this->getMaxStrlen(array_keys($groups)) + 2)), " ") . ($groupConfigs['description'] ?? ''), true);
        }
    }

    private function showList(array $groups = []): void {
        foreach ($groups as $group => $groupConfigs) {
            $this->io->write('<fg=yellow>' . $group . '</>', true);
            $paramsPad = ($this->getMaxStrlen(array_keys($groupConfigs)) + 2);
            ksort($groupConfigs);
            if (isset($groupConfigs['require'])) {
                $tmp = $groupConfigs['require'];
                unset($groupConfigs['require']);
                $groupConfigs['require'] = $tmp;
            }
            foreach ($groupConfigs as $param => $values) {
                $this->io->write(str_repeat(" ", 2) . str_pad($param, $paramsPad, " ") . ': ' . (is_array($values) ? (!$this->io->isVerbose() ? count($values) : '') : (is_bool($values) ? ($values ? 'true' : 'false') : $values)), true);
                if (!$this->io->isVerbose()) {
                    continue;
                }
                if (is_array($values)) {
                    foreach ($values as $key => $value) {
                        $this->io->write(str_repeat(" ", 4) . '<fg=green>' . str_pad($key, $paramsPad - 2, " ") . ': </>' . (is_array($value) ? '' : (is_bool($value) ? ($value ? 'true' : 'false') : $value) ), true);
                        if (!$this->io->isVeryVerbose()) {
                            continue;
                        }
                        if (is_array($value)) {
                            $valuePad = ($this->getMaxStrlen(array_keys($value)) + 2);
                            foreach ($value as $k => $val) {
                                $this->io->write(str_repeat(" ", 6) . str_pad($k, $valuePad, " ") . ': ' . json_encode($val), true);
                            }
                        }
                    }
                }
            }
        }
    }

    private function getMaxStrlen(array $array): int {
        foreach ($array as $string) {
            $max = max(strlen($string), $max ?? 0);
        }
        return $max ?? 0;
    }

}

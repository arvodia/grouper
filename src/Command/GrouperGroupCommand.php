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

namespace Arvodia\Grouper\Command;

use Arvodia\Grouper\Command\RequirementsCommand;
use Arvodia\Grouper\Command\Traits\FormatterTrait;
use Arvodia\Grouper\Console\Alert;
use Arvodia\Grouper\Console\Terminal;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Task;
use Arvodia\Grouper\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description
 *
 * @name    : GrouperGroupCommand
 * @see     :
 * @todo    :
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperGroupCommand extends RequirementsCommand {

    use FormatterTrait;

    private $trans;

    protected function configure() {
        $this->trans = (new Text())->getText('group', 'command');
        $this->setName('grouper:group')
                ->setDescription($this->trans['desc'])
                ->setDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, $this->trans['argument_name']),
                    new InputArgument('action', InputArgument::REQUIRED, $this->trans['argument_action']),
                ])
                ->setHelp($this->trans['help'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = $this->getIO();
        $formatter = $this->getHelperSet()->get('formatter');
        $alert = new Alert($ss = new SymfonyStyle($input, $output));

        if (!($grouper = new Grouper($input))->exists()) {
            $alert->warning('initiate', null, true);
        }

        $task = new Task($this->getComposer(), $io, $input);
        
        $task->runTasks($input->getArgument('name'));

        die;
        
        $group = strtolower($input->getArgument('name'));
        $action = strtolower($input->getArgument('action'));

        if (!$grouper->hasGroup($group)) {
            $alert->error('group_not_found', [$group, $grouper->getName()]);
        }

        if (!in_array($action, ['activate', 'deactivate', 'add', 'remove'])) {
            $alert->error('action_not_found', $input->getArgument('action'));
        }

        $io->write(array(
            '',
            $formatter->formatBlock(sprintf($this->trans['action_' . $action], $group), 'bg=blue;fg=white', true),
            '',
        ));

        if ('activate' == $action || 'deactivate' == $action) {
            $groupDetail = $this->formatGroups($grouper, $group)[$group];
            $activate = ('activate' == $action);

            if (($activate ? 'enabled' : 'disable') == $groupDetail['status']) {
                $alert->warning('already_de_activated', [$group, $this->trans[$action]], true);
            }

            if (empty($groupDetail['require']) || empty($noInstalledPackage = array_filter($groupDetail['require'], function ($package) use ($action, $activate) {
                        return ($activate ? 'no installed' : 'installed') == $package['status'];
                    }, ARRAY_FILTER_USE_BOTH))) {
                $alert->warning('empty_de_activated', [$group, $this->trans[$activate ? 'install' : 'uninstallation']], true);
            }

            $exec = 'composer ' . ($activate ? 'require' : 'remove');
            foreach (array_combine(array_keys($noInstalledPackage), array_column($noInstalledPackage, 'version')) as $package => $version) {
                $exec .= $activate ? ' "' . $package . '":"' . $version . '"' : ' ' . $package;
                $replace[] = '<fg=green>' . $package . '</>';
            }

            // enabled for execute task
            $grouper->setGroupEnabled($group, true)->save();

            if (!Terminal::exec($exec, $grouper->getRootDir(), $io, array_keys($noInstalledPackage), $replace, false)) {
                $grouper->setGroupEnabled($group, !$activate)->save();
                $alert->error();
            }

            $task = new Task($this->getComposer(), $io, $input);

            if (!$activate) {
                $task->runTasks($group, true);
                $grouper->setGroupEnabled($group, $activate)->save();
            } else {
                $task->runTasks($group);
            }

            $alert->success('group_de_activated', [$group, $this->trans[$action]]);
        } elseif ('add' == $action) {
            if ($io->askConfirmation(sprintf($this->trans['confirm_add_package'], $group) . PHP_EOL . '>')) {
                if ($package = $this->askRequirements($input, $output)) {
                    $grouper->addPackage($group, $package);
                }
            }
        } else {
            if (!$packages = $grouper->getPackagesByGroup($group)) {
                $alert->warning('no_dependency', $group, true);
            }

            $question = sprintf($this->trans['confirm_remove_package'], $group);
            while ($io->askConfirmation($question . PHP_EOL . '>')) {
                $package = $ss->choice(sprintf($this->trans['select_remove_package'], $group), array_keys($packages));
                $grouper->removePackage($group, $package);
                unset($packages[$package]);
                $question = $this->trans['confirm_remove_another'];
            }
        }

        if (!$grouper->hasChanged()) {
            $alert->info('no_update');
            $alert->error();
        }

        $grouper->save();
        $alert->success('groups_update');
    }

}

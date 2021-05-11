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
    private $initText;

    protected function configure() {
        $this->trans = ($this->initText = new Text())->getText('group', 'command');
        $this->initText = $this->initText->getText('init', 'command');
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
        $this->getIO()->write(Grouper::getLongVersion());

        $io = $this->getIO();
        $formatter = $this->getHelperSet()->get('formatter');
        $alert = new Alert($ss = new SymfonyStyle($input, $output));

        if (!($grouper = new Grouper($input))->exists()) {
            $alert->warning('initiate', null, true);
        }

        $group = strtolower($input->getArgument('name'));
        $action = strtolower($input->getArgument('action'));

        if ('create' != $action && !$grouper->hasGroup($group)) {
            $alert->error('group_not_found', [$group, $grouper->getName()]);
        }

        if (!in_array($action, ['activate', 'deactivate', 'add', 'remove', 'create', 'delete'])) {
            $alert->error('action_not_found', $input->getArgument('action'));
        }

        $io->write(array(
            '',
            $formatter->formatBlock(sprintf($this->trans['action_' . $action], $group), 'bg=blue;fg=white', true),
            '',
        ));

        if ('create' == $action) {
            if (!preg_match('{^[a-z0-9_.-]+$}D', $group)) {
                $this->alert->error('ask_name_exception', [$type, $name]);
            }

            if ($grouper->hasGroup($group)) {
                $alert->error('group_exists', $group);
            }

            $description = $io->ask(
                    $this->initText['ask_group_desc'] . PHP_EOL . '>'
            );

            $grouper->setGroup($group, ['description' => $description ?: '']);

            $action = 'add';
        }

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
            $grouper->setGroupActivated($group, true)->save();

            if (!$activate) {
                $taskOption = $grouper->getGroupTaskOption($group);
                if ($taskOption['uninstall'] ?? false) {
                    $uninstall = true;
                    $packagesObject = [];
                    $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();
                    foreach ($grouper->getPackagesByGroup($group) as $package => $packageConfig) {
                        if (!is_null($packageObject = $installedRepo->findPackage($package, '*'))) {
                            $packagesObject[] = $packageObject;
                        }
                    }
                }
            }

            if (!Terminal::exec($exec, $grouper->getRootDir(), $io, array_keys($noInstalledPackage), $replace, false)) {
                $grouper->setGroupActivated($group, !$activate)->save();
                $alert->error();
            }

            $task = new Task($this->getComposer(), $io, $input);

            if (!$activate) {
                if (isset($uninstall)) {
                    foreach ($packagesObject as $package) {
                        $task->runTasks($package, true);
                    }
                    $task->runTasks($group, true);
                }
                $grouper->setGroupActivated($group, $activate)->save();
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
        } elseif ('remove' == $action) {
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
        } elseif ('delete' == $action && $io->askConfirmation(sprintf($this->trans['action_' . $action], $group) . ' [<comment>yes</comment>]?' . PHP_EOL . '>')) {
            if ($grouper->isGroupActivated($group)) {
                $alert->error('delete_exception');
            }
            $grouper->removeGroup($group);
        }

        if (!$grouper->hasChanged()) {
            $alert->info('no_update');
            $alert->error();
        }

        $grouper->save();
        $alert->success('groups_update');
    }

}

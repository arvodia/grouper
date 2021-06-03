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

use Arvodia\Grouper\Console\Alert;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Task;
use Arvodia\Grouper\Text;
use Composer\Command\BaseCommand;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function str_ends_with;

/**
 * Description
 * 
 * @name    : GrouperTaskCommand
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class GrouperTaskCommand extends BaseCommand {

    private const TASKS = [
        'file-mapping',
        'file-mapping-overwrite',
        'css-minifying',
        'css-minifying-overwrite',
        'js-minifying',
        'js-minifying-overwrite'
    ];

    protected function configure() {
        $this->trans = (new Text())->getText('task', 'command');
        $this->setName('grouper:task')
                ->setDescription($this->trans['desc'])
                ->setDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, $this->trans['argument_name']),
                ])
                ->addOption('run', 'r', InputOption::VALUE_NONE, $this->trans['option_run'])
                ->addOption('uninstall', 'u', InputOption::VALUE_NONE, $this->trans['option_run_uninstall'])
                ->addOption('delete', null, InputOption::VALUE_NONE, $this->trans['option_delete'])
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

        if (!$grouper->hasGroup($group)) {
            $alert->error('group_not_found', [$group, $grouper->getName()]);
        }

        $io->write(array(
            '',
            $formatter->formatBlock($this->trans['intro'], 'bg=blue;fg=white', true),
            '',
        ));

        $choices = array_merge([0 => $group], array_keys($grouper->getPackagesByGroup($group)));

        if ($input->getOption('run') || $input->getOption('uninstall')) {
            if (!$grouper->isGroupActivated($group)) {
                $alert->error('run_tasks_exception');
            }

            $task = new Task($this->getComposer(), $io, $input);
            $io->write(sprintf('Run %s packages Tasks...', $group));

            $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();
            foreach ($grouper->getPackagesByGroup($group) as $package => $packageConfig) {
                if (!is_null($packageObject = $installedRepo->findPackage($package, '*'))) {
                    $task->runTasks($packageObject, $input->getOption('uninstall'));
                }
            }

            $io->write(sprintf('Run %s group Tasks...', $group));
            $task->runTasks($group, $input->getOption('uninstall'));
            $alert->success();
        } elseif ($input->getOption('delete')) {
            $toApply = $ss->choice($this->trans['question_add_delete'], $choices);
            if (strpos($toApply, '/')) {
                $grouper->resetPackageTask($group, $toApply);
            } else {
                $grouper->resetGroupTask($group);
            }
        } else {
            $isMinify = null;

            $taskOption = $grouper->getGroupTaskOption($group);
            if (!array_key_exists('uninstall', $taskOption)) {
                $taskOption['uninstall'] = $io->askConfirmation($this->trans['ask_uninstall_remove'] . PHP_EOL . '>');
                $grouper->setGroupTaskOption($group, $taskOption);
            }

            while (!isset($toApply) || $io->askConfirmation($this->trans['confirm_add_anothe_task'] . PHP_EOL . '>')) {
                $toApply = $ss->choice($this->trans['question_add_task'], $choices);
                $task = $ss->choice($this->trans['question_type_task'], self::TASKS);
                $isMinify = $isMinify ?: str_ends_with($task, 'minifying');
                $compared = strpos($toApply, '/') ? 'package directory' : 'composer.json';
                $source = [];
                $source[] = $this->askPath($io, sprintf($this->trans['question_add_source'], $compared));
                if (strpos($task, 'minifying')) {
                    while ($io->askConfirmation($this->trans['confirm_add_joine'] . PHP_EOL . '>')) {
                        $source[] = $this->askPath($io, sprintf($this->trans['question_add_source'], $compared));
                    }
                }
                $source = count($source) == 1 ? $source[0] : $source;
                $dest = $this->askPath($io, sprintf($this->trans['question_add_destination'], $compared));
                if (strpos($toApply, '/')) {
                    $grouper->addPackageTask($group, $toApply, $task, [$source, $dest]);
                } else {
                    $grouper->addGroupTask($group, $task, [$source, $dest]);
                }
            }

            if ($isMinify) {
                $vendorDir = rtrim($this->getComposer()->getConfig()->get('vendor-dir'), '/');
                $minifierBin = $vendorDir . '/bin/minifycss';
                if (!file_exists($minifierBin)) {
                    $alert->warning('The "Minify" bin is part of the MatthiasMullie, which is not installed/enabled; try running "composer require matthiasmullie/minify".');
                }
            }
        }

        if ($grouper->hasChanged()) {
            $grouper->save();
            $alert->success('groups_update');
        }

        $alert->info('no_update');

        $alert->success();
    }

    private function askPath($io, string $question) {
        return $io->askAndValidate($question . PHP_EOL . '>',
                        function ($value) use ($io) {
                            if ('/' == $value[0] || '/' == substr($value, -1)) {
                                $io->alert($this->trans['not_valid_path_slash']);
                                throw new InvalidArgumentException();
                            }
                            if (!filter_var('http://fake.com/' . $value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                                $io->alert($this->trans['not_valid_path']);
                                throw new InvalidArgumentException();
                            }
                            return $value;
                        },);
    }

}

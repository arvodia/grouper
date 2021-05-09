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

use Arvodia\Grouper\Command\Traits\FormatterTrait;
use Arvodia\Grouper\Console\Alert;
use Arvodia\Grouper\Command\RequirementsCommand;
use Arvodia\Grouper\Grouper;
use Arvodia\Grouper\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

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

            if (('activate' == $action ? 'enabled' : 'disable') == $groupDetail['status']) {
                $alert->warning('already_de_activated', [$group, $this->trans[$action]], true);
            }

            if (empty($groupDetail['require']) || empty($noInstalledPackage = array_filter($groupDetail['require'], function ($package) use ($action) {
                        return ('activate' == $action ? 'no installed' : 'installed') == $package['status'];
                    }, ARRAY_FILTER_USE_BOTH))) {
                $alert->warning('empty_de_activated', [$group, $this->trans['activate' == $action ? 'install' : 'uninstallation']], true);
            }

            $exec = 'composer ' . ('activate' == $action ? 'require' : 'remove');
            foreach (array_combine(array_keys($noInstalledPackage), array_column($noInstalledPackage, 'version')) as $package => $version) {
                $exec .= 'activate' == $action ? ' "' . $package . '":"' . $version . '"' : ' ' . $package;
                $replace[] = '<fg=green>' . $package . '</>';
            }

            $this->terminal($exec, $grouper->getRootDir(), $output, array_keys($noInstalledPackage), $replace);

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

        if ($grouper->hasChanged()) {
            $grouper->write();
            $alert->success('groups_update');
        }

        $alert->info('no_update');

        $alert->success();
    }

    public function terminal(string $exec, string $rootDir, OutputInterface $output, $search = null, $replace = null): void {
        $process = new Process($exec, $rootDir);

        if ($process->run(function ($type, $buffer) use ($output, $search, $replace) {
                    $output->write(str_replace($search, $replace, $buffer));
                }) != 0) {
            throw new \RuntimeException('Can\'t run ' . $exec);
        }
    }

}

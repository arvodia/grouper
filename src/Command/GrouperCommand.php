<?php

/**
 * @author  : Sidi Said Redouane <sidisaidredouane@live.com>
 * @agency  : EURL ARVODIA
 * @email   : arvodia@hotmail.com
 * @project : Webfony
 * @date    : 2021
 * @license : tous droits réservés
 * @update  : 7 mai 2021
 */

namespace Arvodia\Grouper\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;

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

    protected function configure() {
        $this->setName('grouper:groups')
                ->setAliases(['groups'])
                ->setDescription('Shows information about all available groups.')
                ->setDefinition([
                    new InputArgument('group', InputArgument::OPTIONAL, 'Group to inspect, if not provided all groups are.'),
                ])
                ->addOption('enabled', 'a', InputOption::VALUE_NONE, 'Show only enabled groups')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Executing');
        return BaseCommand::SUCCESS;
    }

}

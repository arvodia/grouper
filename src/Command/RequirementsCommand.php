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

use Composer\Command\InitCommand;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * Description
 * 
 * @name    : RequirementsCommand
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class RequirementsCommand extends InitCommand {

    protected $repos;
    private $platformRepo;
    private $preferredStability;

    protected function askRequirements(InputInterface $input, OutputInterface $output) {
        if (is_null($this->repos)) {
            $composer = $this->getComposer();
            $repos = $this->repos = new CompositeRepository($composer->getRepositoryManager()->getRepositories());
            $this->preferredStability = $composer->getPackage()->getPreferStable() ? 'stable' : $composer->getPackage()->getMinimumStability();
            $this->platformRepo = null;
            if ($repos instanceof CompositeRepository) {
                foreach ($repos->getRepositories() as $candidateRepo) {
                    if ($candidateRepo instanceof PlatformRepository) {
                        $this->platformRepo = $candidateRepo;
                        break;
                    }
                }
            }
        }
        return $this->formatRequirements($this->determineRequirements($input, $output, [], $this->platformRepo, $this->preferredStability));
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        
    }

}

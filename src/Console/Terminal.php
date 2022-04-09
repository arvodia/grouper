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

namespace Arvodia\Grouper\Console;

use Composer\IO\IOInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Description
 * 
 * @name    : Terminal
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Terminal {

    public static function exec(string $exec, string $cwd = null, IOInterface $io = null, $search = null, $replace = null, bool $exit = true): bool {
        $command = explode(' ', $exec);
        $process = new Process($command, $cwd);
        $process->setTimeout(0);

        if ($process->run(function ($type, $buffer) use ($io, $search, $replace) {
                    if ($io) {
                        if ($search && $replace) {
                            $buffer = str_replace($search, $replace, $buffer);
                        }
                        $buffer = preg_replace(['#\[(.*)\]#U', '#\((.*)\)#U'], ['[<fg=yellow>$1</>]', '(<fg=yellow>$1</>)'], $buffer);
                        $buffer = str_replace('[<fg=yellow>NOT EXIST</>]', '[<fg=red>NOT EXIST</>]', $buffer);
                        $buffer = str_replace('[<fg=yellow>Not Copied</>]', '[<fg=red>Not Copied</>]', $buffer);
                        $io->write($buffer, false);
                    }
                }) != 0) {
            if ($exit) {
                throw new RuntimeException('Can\'t run ' . $exec);
            } else {
                $io->error('Can\'t run ' . $exec);
            }
            return false;
        }
        return true;
    }

}

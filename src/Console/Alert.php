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

namespace Arvodia\Grouper\Console;

use Symfony\Component\Console\Style\SymfonyStyle;
use Arvodia\Grouper\Text;

/**
 * Description
 *
 * @name    : Alert
 * @see     :
 * @todo    :
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Alert extends Text {

    private const COLORS = [
        'info' => 'fg=green',
        'warning' => 'fg=black;bg=yellow',
        'success' => 'fg=black;bg=green',
        'error' => 'fg=white;bg=red',
    ];

    private $io;

    public function __construct(SymfonyStyle $io) {
        $this->io = $io;
    }

    public function info($key = null, $detail = null): void {
        $this->alert('info', $key, $detail);
    }

    public function warning($key = null, $detail = null, bool $exit = false): void {
        $this->alert('warning', $key, $detail, $exit);
    }

    public function success($key = null, $detail = null, bool $exit = true): void {
        $this->alert('success', $key, $detail, $exit);
    }

    public function error($key = null, $detail = null, bool $exit = true): void {
        $this->alert('error', $key, $detail, $exit);
    }

    public function none($key = null, $detail = null, bool $newline = true): void {
        if ($key) {
            $method = $newline ? 'writeln' : 'write';
            $this->io->$method(sprintf($this->getText('none')[$key] ?? $key, ...(is_array($detail) ? $detail : [$detail])));
        }
    }

    private function alert(string $type = null, $key = null, $detail = null, bool $exit = false): void {
        if ($key) {
            $this->io->block(sprintf($this->getText($type)[$key] ?? $key, ...(is_array($detail) ? $detail : [$detail])), strtoupper($type), self::COLORS[$type], ' ', true);
        }
        if ($exit) {
            $this->exit('success' == $type ? 0 : 1);
        }
    }

    private function exit(int $code = 1) {
        exit($code);
    }

}

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

namespace Arvodia\Grouper;

use Composer\Json\JsonFile;

/**
 * Description
 * 
 * @name    : Text
 * @see     : 
 * @todo    : 
 *
 * @author Sidi Said Redouane <sidisaidredouane@live.com>
 */
class Text {

    private $locale = 'en';
    private $messages = [
        'alert' => null,
        'command' => null,
    ];

    public function __construct(string $locale = 'en') {
        $this->locale = $locale;
    }

    public function getText(string $domain = null, string $category = 'alert'): ?array {
        if (is_null($this->messages[$category])) {
            foreach (scandir($alertDir = __DIR__ . '/Resources/translations/' . $this->locale . '/' . $category . '/') as $alertFile) {
                if ('.json' == substr($alertFile, -5) && ($json = new JsonFile($alertDir . $alertFile))->exists()) {
                    $this->messages[$category][substr($alertFile, 0, -5)] = $json->read();
                }
            }
        }
        return $domain ? $this->messages[$category][$domain] ?? null : $this->messages[$category];
    }

}

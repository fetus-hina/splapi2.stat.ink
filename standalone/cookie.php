<?php
/**
 * @copyright Copyright (C) 2017 AIZAWA Hina
 * @license https://github.com/fetus-hina/splapi2.stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@bouhime.com>
 */

$length = 32;
$binary = random_bytes(ceil($length * 3 / 4));
$string = strtr(
    rtrim(base64_encode($binary), '='),
    ['+' => '-', '/' => '_']
);
printf("<?php\nreturn '%s';\n", $string);

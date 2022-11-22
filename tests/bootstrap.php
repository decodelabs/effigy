<?php
/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Genesis;
use DecodeLabs\Effigy\Hub;

require_once 'vendor/autoload.php';

Genesis::initialize(Hub::class, [
    'analysis' => true
]);

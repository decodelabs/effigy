<?php
/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

require_once 'vendor/autoload.php';

use DecodeLabs\Genesis\Bootstrap\Analysis;
use DecodeLabs\Effigy\Hub;

new Analysis(
    hubClass: Hub::class
)->initializeOnly();

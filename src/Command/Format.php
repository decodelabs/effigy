<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus as Cli;

class Format implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::Runtime('Unable to find or create an ecs.php config');
        }

        Cli::getCommandDefinition()
            ->addArgument('-check|c', 'Check standards only')
            ->addArgument('-headless|h', 'No interaction mode');

        Cli::prepareArguments();


        $args = ['composer', 'exec', 'ecs'];

        if (Cli::getArgument('headless')) {
            $args[] = '--no-interaction';
        }

        if (!Cli::getArgument('check')) {
            $args[] = '-- --fix';
        }

        return $this->controller->run(...$args);
    }

    protected function ensureInstalled(): bool
    {
        // Dependencies
        $pkgDir = $this->controller->rootDir->getDir('vendor/symplify/easy-coding-standard');

        if (!$pkgDir->exists()) {
            $this->controller->run('composer', 'require', 'symplify/easy-coding-standard', '--dev');
        }

        // ECS file
        $ecsFile = $this->controller->rootDir->getFile('ecs.php');

        if (!$ecsFile->exists()) {
            $dirs = $this->controller->getCodeDirs();

            if (empty($dirs)) {
                return false;
            }

            $paths = [];

            foreach ($dirs as $name => $dir) {
                $paths[] = '__DIR__.\'/' . $name . '\'';
            }

            $pathString = '[' . implode(', ', $paths) . ']';

            $content = <<<ECS
<?php

// ecs.php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig \$ecsConfig): void {
    \$ecsConfig->paths($pathString);
    \$ecsConfig->sets([SetList::CLEAN_CODE, SetList::PSR_12]);
};
ECS;

            $ecsFile->putContents($content);
        }

        return true;
    }
}

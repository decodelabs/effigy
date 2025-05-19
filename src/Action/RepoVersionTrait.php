<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Chronicle\ChangeLog\Document;

trait RepoVersionTrait
{
    private function askRepoVersion(
        Document $doc,
        ?string $version = null
    ): string {
        $origVersion = $version;

        while (true) {
            if (null !== ($lastVersion = $doc->getLastVersion())) {
                $this->io->newLine();
                $this->io->write('Last version: ');
                $this->io->{'.brightRed'}($lastVersion);
            } else {
                $this->io->info('This is the first release');
                $this->io->write('New version: ');
                $this->io->{'.brightGreen'}('v0.1.0');
            }

            if (
                $version !== null &&
                $doc->hasVersion($version)
            ) {
                $this->io->warning('Version ' . $version . ' already exists');
                $version = null;
            }


            if ($version !== null) {
                return $version;
            }

            if (!$lastVersion) {
                return 'v0.1.0';
            }

            $breaking = $this->io->confirm('Does this release have any breaking changes?');

            $version = $doc->validateNextVersion(
                $breaking ? 'breaking' : 'feature'
            );

            $this->io->newLine();
            $this->io->newLine();
            $this->io->write('New version: ');
            $this->io->{$breaking ? '.brightYellow' : '.brightGreen'}($version);

            $confirm = $this->io->confirm(
                'Is this version correct? ' . $version,
                true
            );

            if (!$confirm) {
                $version = $origVersion;
                continue;
            }

            break;
        }

        return $version;
    }
}

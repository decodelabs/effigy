<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas;
use DecodeLabs\Dovetail\Env;
use DecodeLabs\Effigy;
use DecodeLabs\Systemic;
use Dotenv\Dotenv;

trait RepoAuthTrait
{
    protected Effigy $effigy;
    protected Systemic $systemic;

    private function askGithubToken(): void
    {
        if (null !== Env::tryString('GITHUB_TOKEN')) {
            return;
        }

        $result = $this->systemic->capture([
            'composer',
            'config',
            'home',
            '--global'
        ]);

        if ($result->wasSuccessful()) {
            $composerPath = trim((string)$result->getOutput());

            $env = Dotenv::createArrayBacked($composerPath)->safeLoad();

            if (isset($env['GITHUB_TOKEN'])) {
                $_ENV['GITHUB_TOKEN'] = $env['GITHUB_TOKEN'];
                $this->io->newLine();
                $this->io->info('GitHub token found in global composer .env');
                $this->io->newLine();
                return;
            }
        } else {
            $composerPath = null;
        }

        $this->io->newLine();
        $this->io->warning('GitHub token not found in environment');
        $this->io->newLine();

        $token = $this->io->ask('Please enter your GitHub token');

        if ($this->io->confirm('Would you like to save this token globally?')) {
            $path = $composerPath . '/.env';
        } else {
            $path = $this->effigy->project->rootDir . '/.env';
        }

        $file = Atlas::getFile($path);

        if ($file->exists()) {
            $contents = $file->getContents();
            $contents .= "\n";
        } else {
            $contents = '';
        }

        $contents .= 'GITHUB_TOKEN = \'' . $token . '\'' . "\n";
        $file->putContents($contents);
        $_ENV['GITHUB_TOKEN'] = $token;
    }
}

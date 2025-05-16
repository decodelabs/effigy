<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Chronicle\ChangeLog\Document;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Dovetail;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Monarch;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;
use Dotenv\Dotenv;

class WriteChangelog implements Task
{
    public function execute(): bool
    {
        Cli::$command
            ->addArgument('?version', 'Version to write')
            ->addArgument('-rewrite|r', 'Rewrite the changelog');

        $repo = new Repository(
            Effigy::$project->rootDir
        );

        $rewrite = Coercion::toBool(
            Cli::$command['rewrite']
        );

        $doc = $repo->parseChangeLog(
            rewrite: $rewrite
        );

        if(!$rewrite) {
            $this->askGithubToken();

            $version = $this->askVersion(
                $doc,
                Coercion::tryString(
                    Cli::$command['version']
                )
            );

            $doc->generateNextRelease(
                version: $version,
                repository: $repo
            );
        }

        $doc->save();

        Cli::newLine();
        Cli::success('Changelog written');
        Cli::newLine();

        return true;
    }

    private function askVersion(
        Document $doc,
        ?string $version = null
    ): string {
        if(null !== ($lastVersion = $doc->getLastVersion())) {
            Cli::newLine();
            Cli::write('Last version: ');
            Cli::{'.brightRed'}($lastVersion);
        } else {
            Cli::info('This is the first release');
            Cli::write('New version: ');
            Cli::{'.brightGreen'}('v0.1.0');
        }

        if(
            $version !== null &&
            $doc->hasVersion($version)
        ) {
            Cli::warning('Version ' . $version . ' already exists');
            $version = null;
        }


        if($version !== null) {
            return $version;
        }

        if(!$lastVersion) {
            return 'feature';
        }

        $breaking = Cli::confirm('Does this release have any breaking changes?');

        $version = $doc->validateNextVersion(
            $breaking ? 'breaking' : 'feature'
        );

        Cli::newLine();
        Cli::newLine();
        Cli::write('New version: ');
        Cli::{$breaking ? '.brightYellow' : '.brightGreen'}($version);

        $confirm = Cli::confirm(
            'Is this version correct? ' . $version,
            true
        );

        if(!$confirm) {
            $version = $this->askVersion($doc);
        }

        return $version;
    }

    private function askGithubToken(): void
    {
        if(null !== Dovetail::envString('GITHUB_TOKEN')) {
            return;
        }

        $result = Systemic::capture([
            'composer',
            'config',
            'home',
            '--global'
        ]);

        if($result->wasSuccessful()) {
            $composerPath = trim((string)$result->getOutput());

            $env = Dotenv::createArrayBacked($composerPath)->safeLoad();

            if(isset($env['GITHUB_TOKEN'])) {
                $_ENV['GITHUB_TOKEN'] = $env['GITHUB_TOKEN'];
                Cli::newLine();
                Cli::info('GitHub token found in global composer .env');
                Cli::newLine();
                return;
            }
        } else {
            $composerPath = null;
        }

        Cli::newLine();
        Cli::warning('GitHub token not found in environment');
        Cli::newLine();

        $token = Cli::ask('Please enter your GitHub token');

        if(Cli::confirm('Would you like to save this token globally?')) {
            $path = $composerPath . '/.env';
        } else {
            $path = Effigy::$project->rootDir . '/.env';
        }

        $file = Atlas::file($path);

        if($file->exists()) {
            $contents = $file->getContents();
            $contents .= "\n";
        } else {
            $contents = '';
        }

        $contents .= 'GITHUB_TOKEN = \'' . $token . '\''."\n";
        $file->putContents($contents);
        $_ENV['GITHUB_TOKEN'] = $token;
    }
}

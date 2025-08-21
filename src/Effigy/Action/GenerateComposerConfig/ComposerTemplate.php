<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action\GenerateComposerConfig;

use DecodeLabs\Dictum;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Integra\Structure\Author;
use DecodeLabs\Terminus\Session;

class ComposerTemplate extends Template
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io
    ) {
        parent::__construct(
            __DIR__ . '/composer.template',
            $effigy,
            $io
        );
    }

    protected function generateSlot(
        string $name
    ): ?string {
        switch ($name) {
            case 'pkgAuthors':
                $manifest = $this->effigy->project->getLocalManifest();
                $authors = $manifest->getAuthors();

                if (!empty($authors)) {
                    return $this->generateAuthorsJson($authors);
                } else {
                    return $this->generateAuthorsJson($this->generateAuthors());
                }

                // no break
            case 'classRoot':
                return $this->generateClassRoot();
        }

        return parent::generateSlot($name);
    }

    /**
     * Generate json for authors list
     *
     * @param array<Author> $authors
     */
    protected function generateAuthorsJson(
        array $authors
    ): string {
        $list = [];

        foreach ($authors as $author) {
            $list[] = '{' . "\n        " . '"name": "' . $author->name . '",' . "\n        " . '"email": "' . $author->email . '"' . "\n    }";
        }

        return '[' . implode(', ', $list) . ']';
    }

    /**
     * Generate authors list
     *
     * @return array<Author>
     */
    protected function generateAuthors(): array
    {
        $name = exec('git config user.name');

        if (empty($name)) {
            return [];
        }

        return [new Author(
            $name,
            (string)exec('git config user.email')
        )];
    }


    /**
     * Generate class root
     */
    protected function generateClassRoot(): string
    {
        $parts = explode('/', (string)$this->getSlot('pkgName'));

        foreach ($parts as $i => $part) {
            if ($part === 'decodelabs') {
                $part = 'DecodeLabs';
            } else {
                $part = Dictum::id($part);
            }

            $parts[$i] = $part;
        }

        return implode('\\\\', $parts) . '\\\\';
    }
}

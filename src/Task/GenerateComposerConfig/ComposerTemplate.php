<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task\GenerateComposerConfig;

use DecodeLabs\Coercion;
use DecodeLabs\Dictum;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;

/**
 * @phpstan-type TAuthors array<array{
 *     'name': string,
 *     'email': string
 * }>
 */
class ComposerTemplate extends Template
{
    public function __construct()
    {
        parent::__construct(
            __DIR__ . '/composer.template'
        );
    }

    protected function generateSlot(string $name): ?string
    {
        switch ($name) {
            case 'pkgAuthors':
                $config = Effigy::getComposerConfig();

                if (isset($config['authors'])) {
                    /** @phpstan-var TAuthors $authors */
                    $authors = Coercion::toArray($config['authors']);
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
     * @phpstan-param TAuthors $authors
     */
    protected function generateAuthorsJson(array $authors): string
    {
        $list = [];

        foreach ($authors as $author) {
            $list[] = '{' . "\n        " . '"name": "' . $author['name'] . '",' . "\n        " . '"email": "' . $author['email'] . '"' . "\n    }";
        }

        return '[' . implode(', ', $list) . ']';
    }

    /**
     * Generate authors list
     *
     * @phpstan-return TAuthors
     */
    protected function generateAuthors(): array
    {
        $name = exec('git config user.name');

        if (empty($name)) {
            return [];
        }

        return [[
            'name' => $name,
            'email' => (string)exec('git config user.email')
        ]];
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

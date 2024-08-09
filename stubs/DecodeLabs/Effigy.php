<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Effigy\Controller as Inst;
use DecodeLabs\Effigy\Config as ConfigPlugin;
use DecodeLabs\Atlas\File as Ref0;

class Effigy implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Effigy';
    const VENEER_TARGET = Inst::class;
    const USER_FILENAME = Inst::USER_FILENAME;

    public static Inst $instance;
    public static ConfigPlugin $config;

    public static function isLocal(): bool {
        return static::$instance->isLocal();
    }
    public static function setCiMode(bool $mode): Inst {
        return static::$instance->setCiMode(...func_get_args());
    }
    public static function isCiMode(): bool {
        return static::$instance->isCiMode();
    }
    public static function run(string $name, string ...$args): bool {
        return static::$instance->run(...func_get_args());
    }
    public static function runGit(string $name, string ...$args): bool {
        return static::$instance->runGit(...func_get_args());
    }
    public static function askGit(string $name, string ...$args): ?string {
        return static::$instance->askGit(...func_get_args());
    }
    public static function canRun(string $name): bool {
        return static::$instance->canRun(...func_get_args());
    }
    public static function getComposerScripts(): array {
        return static::$instance->getComposerScripts();
    }
    public static function hasComposerScript(string $name): bool {
        return static::$instance->hasComposerScript(...func_get_args());
    }
    public static function runComposerScript(string $name, string ...$args): bool {
        return static::$instance->runComposerScript(...func_get_args());
    }
    public static function getVendorBins(): array {
        return static::$instance->getVendorBins();
    }
    public static function hasVendorBin(string $name): bool {
        return static::$instance->hasVendorBin(...func_get_args());
    }
    public static function getEntryFile(): ?Ref0 {
        return static::$instance->getEntryFile();
    }
    public static function getCodeDirs(): array {
        return static::$instance->getCodeDirs();
    }
    public static function getExportsWhitelist(): array {
        return static::$instance->getExportsWhitelist();
    }
    public static function getExecutablesWhitelist(): array {
        return static::$instance->getExecutablesWhitelist();
    }
    public static function glitchDump(): iterable {
        return static::$instance->glitchDump();
    }
    public static function hasTask(string $name): bool {
        return static::$instance->hasTask(...func_get_args());
    }
    public static function runTask(string $name, array $args = []): bool {
        return static::$instance->runTask(...func_get_args());
    }
    public static function getTaskClass(string $name): ?string {
        return static::$instance->getTaskClass(...func_get_args());
    }
};

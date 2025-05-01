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
use DecodeLabs\Integra\Project as ProjectPlugin;
use DecodeLabs\Atlas\File as Ref0;

class Effigy implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Effigy';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    public static ConfigPlugin $config;
    public static ProjectPlugin $project;

    public static function isLocal(): bool {
        return static::$_veneerInstance->isLocal();
    }
    public static function setCiMode(bool $mode): Inst {
        return static::$_veneerInstance->setCiMode(...func_get_args());
    }
    public static function isCiMode(): bool {
        return static::$_veneerInstance->isCiMode();
    }
    public static function run(string $name, string ...$args): bool {
        return static::$_veneerInstance->run(...func_get_args());
    }
    public static function runGit(string $name, string ...$args): bool {
        return static::$_veneerInstance->runGit(...func_get_args());
    }
    public static function askGit(string $name, string ...$args): ?string {
        return static::$_veneerInstance->askGit(...func_get_args());
    }
    public static function canRun(string $name): bool {
        return static::$_veneerInstance->canRun(...func_get_args());
    }
    public static function getComposerScripts(): array {
        return static::$_veneerInstance->getComposerScripts();
    }
    public static function hasComposerScript(string $name): bool {
        return static::$_veneerInstance->hasComposerScript(...func_get_args());
    }
    public static function runComposerScript(string $name, string ...$args): bool {
        return static::$_veneerInstance->runComposerScript(...func_get_args());
    }
    public static function getVendorBins(): array {
        return static::$_veneerInstance->getVendorBins();
    }
    public static function hasVendorBin(string $name): bool {
        return static::$_veneerInstance->hasVendorBin(...func_get_args());
    }
    public static function getEntryFile(): ?Ref0 {
        return static::$_veneerInstance->getEntryFile();
    }
    public static function hasAppTask(string $name): bool {
        return static::$_veneerInstance->hasAppTask(...func_get_args());
    }
    public static function runAppTask(string $name, string ...$args): bool {
        return static::$_veneerInstance->runAppTask(...func_get_args());
    }
    public static function getCodeDirs(): array {
        return static::$_veneerInstance->getCodeDirs();
    }
    public static function getExportsWhitelist(): array {
        return static::$_veneerInstance->getExportsWhitelist();
    }
    public static function getExecutablesWhitelist(): array {
        return static::$_veneerInstance->getExecutablesWhitelist();
    }
    public static function getGlobalPath(): string {
        return static::$_veneerInstance->getGlobalPath();
    }
    public static function glitchDump(): iterable {
        return static::$_veneerInstance->glitchDump();
    }
    public static function hasTask(string $name): bool {
        return static::$_veneerInstance->hasTask(...func_get_args());
    }
    public static function runTask(string $name, array $args = []): bool {
        return static::$_veneerInstance->runTask(...func_get_args());
    }
    public static function getTaskClass(string $name): ?string {
        return static::$_veneerInstance->getTaskClass(...func_get_args());
    }
};

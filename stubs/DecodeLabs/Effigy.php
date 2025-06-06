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
use DecodeLabs\Nuance\Entity\NativeObject as Ref1;
use DecodeLabs\Terminus\Session as Ref2;
use DecodeLabs\Slingshot as Ref3;
use DecodeLabs\Commandment\Request as Ref4;
use DecodeLabs\Commandment\Middleware as Ref5;

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
    public static function hasAppAction(string $name): bool {
        return static::$_veneerInstance->hasAppAction(...func_get_args());
    }
    public static function runAppAction(string $name, string ...$args): bool {
        return static::$_veneerInstance->runAppAction(...func_get_args());
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
    public static function toNuanceEntity(): Ref1 {
        return static::$_veneerInstance->toNuanceEntity();
    }
    public static function runAction(string $name, array $args = []): bool {
        return static::$_veneerInstance->runAction(...func_get_args());
    }
    public static function getIoSession(): Ref2 {
        return static::$_veneerInstance->getIoSession();
    }
    public static function newRequest(string $command, array $arguments = [], array $attributes = [], ?array $server = NULL, ?Ref3 $slingshot = NULL): Ref4 {
        return static::$_veneerInstance->newRequest(...func_get_args());
    }
    public static function addMiddleware(Ref5 $middleware): void {}
    public static function dispatch(Ref4 $request): bool {
        return static::$_veneerInstance->dispatch(...func_get_args());
    }
    public static function getActionAttributes(string $class): array {
        return static::$_veneerInstance->getActionAttributes(...func_get_args());
    }
    public static function hasAction(string $name): bool {
        return static::$_veneerInstance->hasAction(...func_get_args());
    }
    public static function getActionClass(string $name): ?string {
        return static::$_veneerInstance->getActionClass(...func_get_args());
    }
};

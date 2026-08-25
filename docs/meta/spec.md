# Effigy — Package Specification

> **Cluster:** `tooling`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/effigy`
> **Role:** CLI entry point

This document describes the purpose, contracts, and design of **Effigy** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Effigy in their own applications or libraries.
- Contributors **maintaining or extending** Effigy.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Effigy provides a universal CLI entry point for PHP projects. It simplifies running tasks in projects from the command line by locating and loading the main entry point via a globally installed executable. It provides entry point resolution with template parameters, composer script passthrough, vendor bin execution, application action delegation, local installation support, per-project PHP binary configuration, package mounting for local development, and comprehensive project initialization and maintenance tools.

### 1.2 Non-Goals

Effigy does **not**:

- Provide task execution logic — it delegates to application entry points
- Handle task scheduling — it's a command dispatcher
- Provide task dependencies — tasks are independent
- Handle task parallelization — tasks run sequentially
- Provide task caching — tasks always execute
- Handle task queuing — tasks execute immediately
- Provide task monitoring — it's a simple dispatcher
- Handle task logging — logging is handled by application

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `tooling` (see Chorus taxonomy)
- Effigy is a tooling package that provides a universal CLI entry point for the Decode Labs ecosystem. It sits in the tooling cluster alongside other development and maintenance tools. It depends on Archetype, Atlas, Chronicle, Clip, Coercion, Commandment, Dictum, Exceptional, Integra, Genesis, Hatch, Lucid, Monarch, Nuance, Systemic, Terminus, and external packages (ondram/ci-detector, php-parallel-lint/php-parallel-lint, symplify/easy-coding-standard). It's used throughout the ecosystem for running CLI commands, initializing projects, managing releases, and mounting local packages.

### 2.2 Typical Usage Contexts

Typical places Effigy appears:

- Global CLI tool installation
- Project entry point resolution
- Composer script execution
- Vendor bin execution
- Application action delegation
- Local package mounting
- Project initialization
- Release management
- Code generation
- Configuration file generation

Effigy is intended to be used whenever code needs a simple, unified way to run CLI commands in PHP projects.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Effigy`
  Main service class extending `Clip`. Provides command execution, entry point resolution, composer script passthrough, vendor bin execution, and application action delegation.

- `DecodeLabs\Effigy\Hub`
  Genesis hub implementation extending `Clip\Hub`. Configures Effigy as the Clip service and maps Action interface to Effigy actions.

- `DecodeLabs\Effigy\Config`
  Configuration manager for Effigy. Handles entry point configuration, PHP binary configuration, template parameters, code directories, ignored bins, exports whitelist, executables whitelist, and local repos. Saves to `effigy.json` file.

- `DecodeLabs\Effigy\Template`
  File template implementation extending `Hatch\FileTemplate`. Provides slots for package metadata, PHP extensions, git branch, and Effigy version.

- `DecodeLabs\Effigy\Action\*`
  Various action classes for Effigy commands:
  - `InitPackage` — Initialize new package
  - `InitRepo` — Initialize git repository
  - `InstallLocal` — Install Effigy locally
  - `SetPhp` — Set PHP binary for project
  - `Mount` — Mount local packages
  - `Release` — Create and publish releases
  - `Version` — Display version
  - `GenerateReadme` — Generate README.md
  - `GenerateComposerConfig` — Generate composer.json
  - `GenerateEditorConfig` — Generate .editorconfig
  - `GenerateGitattributes` — Generate .gitattributes
  - `GenerateGitignore` — Generate .gitignore
  - `GenerateChangelog` — Generate CHANGELOG.md
  - `GenerateAgents` — Generate AGENTS.md
  - `GenerateEcsConfig` — Generate ECS config
  - `GeneratePhpstanConfig` — Generate PHPStan config
  - `GenerateGithubWorkflow` — Generate GitHub workflow
  - `Analyze` — Run PHPStan analysis
  - `Lint` — Run parallel lint
  - `Format` — Run ECS formatting
  - `Eclint` — Run eclint
  - `Prep` — Prepare for release
  - `Reinstall` — Reinstall dependencies
  - `SelfUpdate` — Update Effigy itself
  - `UpdateDevVersion` — Update dev version
  - `WriteChangelog` — Write changelog
  - `CheckExecutablePermissions` — Check executable permissions
  - `CheckGitExports` — Check git exports
  - `Deploy\Upgrade` — Upgrade deployment
  - `VeneerStub` — Generate Veneer stubs
  - `Unmount` — Unmount local packages
  - `RemoveLocal` — Remove local installation

### 3.2 Main Entry Points

The main usage pattern is through the `effigy` executable:

```bash
effigy run-task
```

For local installation:

```bash
./effigy run-task
```

For composer passthrough:

```bash
effigy composer require decodelabs/atlas
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/archetype` (required)
  Used for resolving action classes.

- `decodelabs/atlas` (required)
  Used for file operations when generating config files and managing project structure.

- `decodelabs/chorus` (required)
  Used for accessing ecosystem metadata and templates.

- `decodelabs/chronicle` (required)
  Used for release management and changelog generation.

- `decodelabs/clip` (required)
  Used as base class for Effigy, providing CLI runtime functionality.

- `decodelabs/coercion` (required)
  Used for type coercion when parsing configuration.

- `decodelabs/commandment` (required)
  Used for action interface and request handling.

- `decodelabs/dictum` (required)
  Used for text formatting in templates.

- `decodelabs/exceptional` (required)
  Used for exception handling.

- `decodelabs/integra` (required)
  Used for Composer project inspection and package management.

- `decodelabs/genesis` (required)
  Used for application bootstrapping and hub configuration.

- `decodelabs/hatch` (required)
  Used for file template generation.

- `decodelabs/lucid` (required)
  Used for value sanitization (if needed for future extensions).

- `decodelabs/monarch` (required)
  Used for accessing paths and runtime information.

- `decodelabs/nuance` (required)
  Used for debugging and entity dumping.

- `decodelabs/systemic` (required)
  Used for system command execution and OS information.

- `decodelabs/terminus` (required)
  Used for CLI I/O operations.

### 4.2 External

- `ondram/ci-detector` (required)
  Used for detecting CI environment.

- `php-parallel-lint/php-parallel-lint` (required)
  Used for PHP linting.

- `symplify/easy-coding-standard` (required)
  Used for code formatting and style checking.

### 4.3 Optional Integrations

None — all dependencies are required.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Entry point is resolved from composer.json `extra.effigy.entry` or defaults to `entry.php`
- Entry point template parameters are resolved interactively on first use
- Entry point template parameters are saved to `effigy.json`
- PHP binary is configurable per project
- PHP binary is stored in `effigy.json`
- Composer scripts are passed through directly
- Vendor bins are executed if available
- Application actions are delegated to entry point
- Local installation creates `effigy` executable in project root
- Local installation sets executable permissions
- Config file `effigy.json` is added to `.gitignore`
- Mounted packages are added to composer repositories
- Mounted packages use path repositories with symlinks
- Release process checks for uncommitted changes
- Release process validates version doesn't exist
- Release process generates changelog from Chronicle
- Release process creates git tags and pushes to remote
- Executable permission checks inspect tracked and non-ignored untracked files only
- CI mode is detected automatically

### 5.2 Input & Output Contracts

**Effigy Service Operations:**
- `__construct(Project $project, Archetype $archetype, Session $io, Systemic $systemic, Paths $paths)` — Creates service with dependencies
- `run(string $name, string ...$args): bool` — Runs command (composer, composer script, action, vendor bin, or app action)
- `hasAction(string $name): bool` — Checks if Effigy action exists
- `runGit(string $name, string ...$args): bool` — Runs git command
- `askGit(string $name, string ...$args): ?string` — Runs git command and returns output
- `canRun(string $name): bool` — Checks if command can be run
- `getComposerScripts(): array` — Gets composer scripts
- `hasComposerScript(string $name): bool` — Checks if composer script exists
- `runComposerScript(string $name, string ...$args): bool` — Runs composer script
- `getVendorBins(): array` — Gets vendor bin files
- `hasVendorBin(string $name): bool` — Checks if vendor bin exists
- `getEntryFile(): ?File` — Gets entry file (resolves template parameters)
- `hasAppAction(string $name): bool` — Checks if app action exists
- `runAppAction(string $name, string ...$args): bool` — Runs app action
- `getCodeDirs(): array` — Gets code directories
- `getExportsWhitelist(): array` — Gets exports whitelist
- `getExecutablesWhitelist(): array` — Gets executables whitelist
- `getGlobalPath(): string` — Gets global composer path
- `toNuanceEntity(): NuanceEntity` — Creates Nuance entity for debugging
- Properties:
  - `Config $config` — Configuration
  - `bool $local` — Whether running locally
  - `bool $ciMode` — Whether in CI mode

**Config Operations:**
- `__construct(Project $project)` — Creates config with project
- `getFile(): File` — Gets config file
- `toArray(): array` — Gets config as array
- `set(string $key, mixed $value): void` — Sets config value
- `getParam(string $slug, string|Closure $default): string` — Gets template parameter
- `getPhpBinary(): ?string` — Gets PHP binary
- `hasEntry(): bool` — Checks if entry is configured
- `getEntry(): ?string` — Gets entry path
- `getCodeDirs(): array` — Gets code directories
- `getIgnoredBins(): array` — Gets ignored bins
- `getExportsWhitelist(): array` — Gets exports whitelist
- `getExecutablesWhitelist(): array` — Gets executables whitelist
- `getLocalRepos(): array` — Gets local repos
- `save(): void` — Saves config to file

**Template Operations:**
- `__construct(string|File $file, Effigy $effigy, Session $io)` — Creates template
- `generateSlot(string $name): ?string` — Generates slot value
- Slots: `pkgName`, `pkgTitle`, `pkgDescription`, `pkgType`, `pkgLicense`, `pkgIntro`, `phpExtensions`, `gitBranch`, `__effigyVersion`

**Action Operations:**
- `execute(Request $request): bool` — Executes action
- Actions implement `Commandment\Action` interface

### 5.3 Command Resolution

Commands are resolved in this order:
1. Direct composer command (`composer`)
2. Application action (via entry point)
3. Composer script
4. Effigy action
5. Vendor bin
6. Application action (fallback)

### 5.4 Entry Point Resolution

Entry point is resolved from:
1. `composer.json` `extra.effigy.entry`
2. Default `entry.php` in project root

If entry contains template parameters (e.g., `{{env}}`), they are resolved interactively on first use and saved to `effigy.json`.

### 5.5 Application Action Delegation

Application actions are delegated to entry point:
- Entry file is executed with `action-exists` command to check existence
- Entry file is executed with action name and arguments
- Uses configured PHP binary
- Handles signals (SIGINT, SIGTERM, SIGQUIT)

### 5.6 Composer Passthrough

Composer commands are passed through directly:
- `effigy composer <command>` executes `composer <command>`
- Uses configured PHP binary
- Useful when project has custom PHP version

### 5.7 Vendor Bin Execution

Vendor bins are executed if available:
- Checks `vendor/bin/{name}` exists
- Respects ignored bins from config
- Executes with configured working directory
- Handles signals

### 5.8 Local Installation

Local installation:
- Copies `bin/effigy` to project root
- Sets executable permissions (0777)
- Installs dev dependencies (phpstan, effigy, phpstan-extension-installer)
- Creates `./effigy` executable

### 5.9 Package Mounting

Package mounting:
- Adds path repository to composer.json
- Uses symlinks for local packages
- Supports per-package path configuration
- Supports wildcard mounting (`vendor/*`)
- Updates composer dependencies
- Clears opcache

### 5.10 Release Process

Release process:
1. Runs `prep` action
2. Checks for uncommitted changes
3. Validates branch setup (develop/release)
4. Loads changelog
5. Asks for version
6. Validates version doesn't exist
7. Generates release notes
8. Confirms release
9. Updates dev version
10. Commits changelog
11. Merges to release branch
12. Creates git tag
13. Pushes to remote
14. Publishes GitHub release

### 5.11 Template System

Templates use Hatch file template system:
- Slots are generated from project metadata
- Supports package information
- Supports PHP extensions
- Supports git branch
- Supports Effigy version

### 5.12 CI Detection

CI mode is detected automatically:
- Uses `ondram/ci-detector`
- Cached after first detection
- May affect interactive prompts

---

## 6. Error Handling

- Missing entry file throws `Exceptional::NotFound`
- Invalid entry template throws `Exceptional::UnexpectedValue`
- Failed git operations return false or null
- Failed composer operations return false
- Failed system commands return false
- Invalid PHP binary may cause execution failures
- Missing packages throw `Exceptional::InvalidArgument`
- Uncommitted changes prevent release
- Existing version tags prevent release
- Invalid branch setup prevents release
- Template generation failures may throw exceptions

---

## 7. Configuration & Extensibility

- Entry point can be configured in `composer.json` or `effigy.json`
- PHP binary can be configured per project
- Template parameters can be configured
- Code directories can be configured
- Ignored bins can be configured
- Exports whitelist can be configured
- Executables whitelist can be configured
- Local repos can be configured
- Actions can be extended via Archetype
- Templates can be customized
- Entry point can handle custom actions

---

## 8. Interactions with Other Packages

### 8.1 Clip

Effigy extends Clip:
- Inherits CLI runtime functionality
- Uses Clip's action system
- Uses Clip's hub system
- Provides Effigy-specific command resolution

### 8.2 Integra

Effigy uses Integra for:
- Composer project inspection
- Package management
- Script execution
- Bin discovery
- Manifest parsing

### 8.3 Chronicle

Effigy uses Chronicle for:
- Changelog parsing
- Release note generation
- GitHub release publishing
- Version management

### 8.4 Commandment

Effigy uses Commandment for:
- Action interface
- Request handling
- Argument parsing
- Command dispatching

### 8.5 Systemic

Effigy uses Systemic for:
- System command execution
- Process management
- Signal handling
- OS information
- Binary path resolution

### 8.6 Terminus

Effigy uses Terminus for:
- CLI I/O operations
- User prompts
- Output formatting
- Error display

### 8.7 Hatch

Effigy uses Hatch for:
- File template generation
- Slot resolution
- Template rendering

### 8.8 Atlas

Effigy uses Atlas for:
- File operations
- Directory management
- Path resolution

### 8.9 Dictum

Effigy uses Dictum for:
- Text formatting in templates
- Name formatting

### 8.10 Nuance

Effigy uses Nuance for:
- Debugging support
- Entity dumping
- Development tools

### 8.11 Genesis

Effigy uses Genesis for:
- Application bootstrapping
- Hub configuration
- Service container

### 8.12 Monarch

Effigy uses Monarch for:
- Path access
- Runtime information

### 8.13 Archetype

Effigy uses Archetype for:
- Action class resolution
- Interface mapping

### 8.14 Coercion

Effigy uses Coercion for:
- Type conversion when parsing configuration
- JSON parsing

### 8.15 Exceptional

Effigy uses Exceptional for:
- All exception handling
- Error reporting

### 8.16 Lucid

Effigy depends on Lucid (though usage is not immediately apparent in the codebase). It may be used for value sanitization in future extensions.

### 8.17 External Packages

- `ondram/ci-detector` — CI environment detection
- `php-parallel-lint/php-parallel-lint` — PHP linting
- `symplify/easy-coding-standard` — Code formatting

---

## 9. Usage Examples

### 9.1 Basic Usage

```bash
# Run application action
effigy run-task

# Run composer script
effigy analyze

# Run composer command
effigy composer require decodelabs/atlas

# Run vendor bin
effigy phpstan
```

### 9.2 Entry Point Configuration

```json
{
    "extra": {
        "effigy": {
            "entry": "webroot/index.php"
        }
    }
}
```

### 9.3 Template Parameters

```json
{
    "extra": {
        "effigy": {
            "entry": "entry/{{env}}.php"
        }
    }
}
```

On first run, Effigy will ask for `env` parameter and save it to `effigy.json`.

### 9.4 Local Installation

```bash
composer require decodelabs/effigy
vendor/bin/effigy install-local
./effigy run-task
```

### 9.5 PHP Binary Configuration

```bash
effigy set-php
> php8.1
```

### 9.6 Package Mounting

```bash
# Mount single package
effigy mount decodelabs/atlas

# Mount all packages from vendor
effigy mount decodelabs/*

# Mount globally
effigy mount -g decodelabs/atlas
```

### 9.7 Project Initialization

```bash
# Initialize new package
effigy init-package

# Initialize git repository
effigy init-repo
```

### 9.8 Release Management

```bash
# Prepare for release
effigy prep

# Create release
effigy release 1.0.0
```

### 9.9 Code Generation

```bash
# Generate README
effigy generate-readme

# Generate composer.json
effigy generate-composer-config

# Generate GitHub workflow
effigy generate-github-workflow
```

### 9.10 Code Quality

```bash
# Run PHPStan
effigy analyze

# Run linter
effigy lint

# Run formatter
effigy format
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Command Resolution Order

Commands are resolved in specific order:
1. Direct composer command
2. Application action (checked first)
3. Composer script
4. Effigy action
5. Vendor bin
6. Application action (fallback)

This ensures proper delegation and fallback behavior.

### 10.2 Entry Point Template Resolution

Entry point templates use `{{param}}` syntax:
- Parameters are extracted via regex
- Parameters are resolved interactively on first use
- Parameters are saved to `effigy.json`
- Saved entry path is used on subsequent runs

### 10.3 Application Action Checking

Application actions are checked via entry point:
- Executes `php entry.php action-exists <name>`
- Returns `true` if action exists
- Caches result for performance

### 10.4 Config File Management

Config file `effigy.json`:
- Stored in project root
- Added to `.gitignore` automatically
- Merges composer.json config with local config
- Saves only new/changed values

### 10.5 Package Mounting

Package mounting:
- Adds path repository to composer.json
- Uses symlinks for performance
- Supports wildcard patterns
- Updates dependencies automatically
- Clears opcache after mounting

### 10.6 Release Process

Release process:
- Validates git state
- Generates changelog from Chronicle
- Creates git tags
- Publishes GitHub releases
- Handles branch management

### 10.7 Template System

Templates use Hatch:
- Slots generated from project metadata
- Supports package information
- Supports dynamic values
- Extensible via Archetype

### 10.8 CI Detection

CI mode detection:
- Uses `ondram/ci-detector`
- Cached after first check
- May skip interactive prompts

### 10.9 Local Installation

Local installation:
- Copies executable to project root
- Sets permissions (0777)
- Installs dev dependencies
- Creates local `./effigy` executable

### 10.10 Action System

Actions implement `Commandment\Action`:
- Resolved via Archetype
- Receive `Request` object
- Return boolean success
- Can use traits for common functionality

---

## 11. Testing & Quality

- **Code Quality Score:** 4/5
- **README Quality Score:** 3/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Add more code generation templates
- Improve test coverage
- Add more release management features
- Consider adding task dependencies
- Consider adding task parallelization
- Consider adding task caching
- Consider adding task queuing
- Consider adding task monitoring
- Consider adding task logging
- Improve documentation and usage examples
- Add more configuration options
- Consider adding plugin system
- Consider adding task scheduling
- Consider adding task history

---

## 13. References

- [Clip Package](https://github.com/decodelabs/clip) — CLI runtime
- [Integra Package](https://github.com/decodelabs/integra) — Composer integration
- [Chronicle Package](https://github.com/decodelabs/chronicle) — Release management
- [Commandment Package](https://github.com/decodelabs/commandment) — CLI dispatcher
- [Systemic Package](https://github.com/decodelabs/systemic) — System commands
- [Terminus Package](https://github.com/decodelabs/terminus) — CLI I/O
- [Hatch Package](https://github.com/decodelabs/hatch) — File templates
- [Atlas Package](https://github.com/decodelabs/atlas) — File operations
- [Dictum Package](https://github.com/decodelabs/dictum) — Text formatting
- [Nuance Package](https://github.com/decodelabs/nuance) — Debugging
- [Genesis Package](https://github.com/decodelabs/genesis) — Application bootstrap
- [Monarch Package](https://github.com/decodelabs/monarch) — Runtime
- [Archetype Package](https://github.com/decodelabs/archetype) — Class resolution
- [Coercion Package](https://github.com/decodelabs/coercion) — Type conversion
- [Exceptional Package](https://github.com/decodelabs/exceptional) — Exception handling
- [Lucid Package](https://github.com/decodelabs/lucid) — Value sanitization
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata


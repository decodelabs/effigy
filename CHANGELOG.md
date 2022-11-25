* Moved GenerateFileTrait to Clip
* Added signals to app scripts

## v0.3.1 (2022-11-25)
* Use run dir for cwd when calling bins
* Moved body of Template to Genesis
* Improved version task
* Added signal handlers to bin launcher

## v0.3.0 (2022-11-24)
* Switched composer integration to Integra
* Moved config handling to standalone class
* Added version task
* Improved self-update task
* Simplified arg handling
* Added CI mode detection
* Simplified bin path detection

## v0.2.2 (2022-11-23)
* Fixed arg passthrough to entry
* Improved fall-through error handling

## v0.2.1 (2022-11-22)
* Fixed error handling

## v0.2.0 (2022-11-22)
* Switched to Clip for process initialisation
* Renamed Command to Task
* Register Controller as Veneer facade
* Passthrough to available vendor bins
* Create src folder during init-package

## v0.1.14 (2022-11-22)
* Fixed check for phpstan-decodelabs
* Added null check in template config lookups
* Init repo before init-package
* Reload composer config after init-package
* Generate CI workflow after init-package
* Replaced template comment pattern
* Fixed phpstan-decodelabs install check

## v0.1.13 (2022-11-21)
* Fixed local specialised phpstan script calls

## v0.1.12 (2022-11-21)
* Fixed phpstan dependency handling

## v0.1.11 (2022-11-21)
* Fixed analyze command
* Fixed remove-local command

## v0.1.10 (2022-11-21)
* Use effigy dependencies for prep

## v0.1.9 (2022-11-21)
* Added Template and file generator structure
* Added package initiator commands
* Added check for local-install mode
* Migrated to use effigy in CI workflow

## v0.1.8 (2022-11-19)
* Fixed headless options in analyze and format commands
* Updated self-update command

## v0.1.7 (2022-11-19)
* Added veneer-stub command

## v0.1.6 (2022-11-19)
* Added full composer scripts replacement commands
* Added codeDir config support
* Added proper process termination handling

## v0.1.5 (2022-11-08)
* Added composer passthrough command

## v0.1.4 (2022-11-08)
* Added custom PHP bin config
* Added self-update command
* Standardised composer launcher

## v0.1.3 (2022-11-07)
* Improved exception handling

## v0.1.2 (2022-11-07)
* Added Command structure

## v0.1.1 (2022-11-07)
* Added bin to composer.json

## v0.1.0 (2022-11-07)
* Built initial codebase

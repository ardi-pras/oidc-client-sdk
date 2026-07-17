# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-07-17

### Added
- Framework-agnostic `OidcService` wrapper for simpler integrations.
- Integration bindings and controller updates for Laravel and CodeIgniter 3.
- Detailed integration documentation for CodeIgniter 3 and Laravel in `docs/integration/`.
- `composer.json` examples for application integration.

### Changed
- Refactored `OidcController` for CodeIgniter 3 to use `OidcService`.
- Refactored `OidcController` for Laravel to use `OidcService` and provider to bind the service.

### Fixed
- Suppressed PHPUnit mock notices by adding `AllowMockObjectsWithoutExpectations` attribute to tests.
- Tests: all unit tests pass (`8 tests, 26 assertions`).

### Notes
- See `docs/integration/` for step-by-step integration instructions for each framework.

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.1.0] - 2026-04-18

### Added
- Installer script (`script.php`) for clean deploy/uninstall of element files
- Element files are now copied to `media/zoo/custom_elements/timezone/` for guaranteed ZOO discovery
- Runtime fallback path registration for non-ZOOlanders sites

### Fixed
- Plugin bootstrap: removed `bootComponent('com_zoo')` call that silently killed element registration on Joomla 5 (ZOO is a legacy component, not a modern service provider)
- Removed `bindData()` override that conflicted with base Element class patterns (following ZOOlanders Essentials conventions)
- Fixed `hasValue()` method signature to match parent `Element` class (removed `array` type hint that caused PHP compile error)

## [1.0.0] - 2026-04-18

### Added
- Initial release
- Continent-grouped timezone dropdown with city search filter
- Live preview swatch in admin editor
- Multiple render output formats (timezone_id, timezone_name, abbreviation, utc_offset_hours, utc_offset_minutes, utc_offset_string, continent, city, full)
- DST-aware live offset computation at render time

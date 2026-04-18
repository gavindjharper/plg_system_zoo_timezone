# ZOO Timezone Element for Joomla

A custom [YOOtheme ZOO CCK](https://yootheme.com/support/zoo) element that provides a timezone picker with continent-grouped dropdown and city search. Stores a PHP timezone identifier and derives display values (name, abbreviation, UTC offset) dynamically at render time — so daylight saving transitions are always accurate without re-saving items.

## Features

- **Continent-grouped dropdown** — all PHP timezones organised by region (Africa, America, Asia, etc.)
- **City search filter** — type to instantly filter the dropdown by city or timezone name
- **Live preview swatch** — shows the selected timezone's abbreviation, UTC offset, and derived values in the admin editor
- **DST-aware rendering** — offset values are always computed live from the current date, not stored statically
- **Multiple output formats** — render as timezone ID, display name, abbreviation, UTC offset (hours, minutes, or formatted string), continent, or city
- **Clean install/uninstall** — installer script deploys element files to `media/zoo/custom_elements/` for guaranteed ZOO discovery, and removes them on uninstall

## Requirements

- Joomla 4.x or 5.x
- YOOtheme ZOO CCK
- PHP 8.0+

Works with ZOOlanders Essentials but does not require it.

## Installation

1. Download the [latest release ZIP](../../releases/latest) from the Releases page
2. In Joomla, go to **System → Install → Extensions → Upload Package File**
3. Upload the ZIP file
4. Go to **System → Plugins**, search for "zoo_timezone", and **enable** the plugin
5. The "Timezone" element will now appear in the **3rd Party** group when editing a ZOO Type's elements

## Usage

### Adding to a Type

1. Go to **ZOO → [Your App] → [Your Type] → Edit Elements**
2. Find "Timezone" in the **3rd Party** group on the right-hand panel
3. Drag it onto your Type
4. Optionally set a default timezone in the element configuration (e.g. `Europe/London`)

### Editing Items

When editing a ZOO item, the Timezone element shows:

- A **search box** — type a city name to filter the dropdown
- A **grouped select dropdown** — timezones organised by continent, each showing city name, abbreviation, and current UTC offset
- A **preview swatch** — displays the selected timezone's full details

### Rendering Output

The element stores a single value — the PHP timezone identifier (e.g. `Europe/London`). All other values are derived live at render time.

In ZOO templates, use the `output` parameter to control what's rendered:

| `output` value | Example output | Description |
|---|---|---|
| `full` (default) | `Europe/London (BST, UTC+01:00)` | Complete display string |
| `timezone_id` | `Europe/London` | PHP timezone identifier |
| `timezone_name` | `Europe / London` | Human-readable name |
| `abbreviation` | `BST` | Current timezone abbreviation |
| `utc_offset_hours` | `+1` | Signed hour offset from UTC |
| `utc_offset_minutes` | `+60` | Signed total minutes from UTC |
| `utc_offset_string` | `UTC+01:00` | Formatted UTC offset string |
| `continent` | `Europe` | Continent/region portion |
| `city` | `London` | City portion |

### ZOO Template Usage

```php
// Full display (default)
<?php echo $this->element->render(); ?>

// Specific output
<?php echo $this->element->render(array('output' => 'abbreviation')); ?>

// UTC offset as integer
<?php echo $this->element->render(array('output' => 'utc_offset_hours')); ?>
```

### YOOtheme Pro Dynamic Content

When using ZOOlanders Essentials to expose ZOO items as YOOtheme Pro dynamic content sources, the stored `timezone_id` value will be available for mapping. The derived values (abbreviation, offset, etc.) are computed at render time via the element's `render()` method.

## How It Works

The element uses PHP's built-in `DateTimeZone` and `DateTime` classes. Only the timezone identifier string is stored in the database. On every render call, the current UTC offset, abbreviation, and other metadata are computed from the live system clock — meaning:

- **BST/GMT transitions** (and all other DST changes worldwide) are handled automatically
- **No cron jobs or re-saves needed** when clocks change
- **Half-hour and 45-minute offsets** (India UTC+05:30, Nepal UTC+05:45, etc.) are fully supported

## File Structure

```
plg_system_zoo_timezone/
├── zoo_timezone.php          # Joomla system plugin — bootstraps ZOO and registers element path
├── zoo_timezone.xml          # Joomla plugin manifest
├── script.php                # Installer script — deploys/removes files in custom_elements
└── elements/
    └── timezone/
        ├── timezone.php      # ZOO element class (ElementTimezone extends Element)
        └── timezone.xml      # ZOO element metadata and configuration
```

On installation, the element files are copied to `media/zoo/custom_elements/timezone/` for ZOO's element discovery. The plugin also registers the path at runtime as a fallback.

## Technical Notes

- **Method signatures** match the parent `Element` class exactly (no added type hints) to avoid PHP strict mode errors
- **No `bindData()` override** — the base Element class handles sub-key storage automatically, following ZOOlanders' own element patterns
- **Plugin bootstrap** uses `App::getInstance('zoo')` after requiring `com_zoo/config.php`, not `bootComponent()` (ZOO is a legacy component, not a Joomla 5 service provider)
- **Element discovery** works via `media/zoo/custom_elements/` deployment, not solely via `$zoo->path->register()` (which helps ZOO load elements but doesn't populate the admin "Add Element" panel)

## Building from Source

To create an installable ZIP from this repository:

```bash
git clone https://github.com/YOUR_USERNAME/plg_system_zoo_timezone.git
cd plg_system_zoo_timezone
zip -r plg_system_zoo_timezone.zip . -x ".git/*" ".gitignore" "README.md" "CHANGELOG.md" "LICENSE"
```

## Uninstallation

1. Go to **System → Manage → Extensions**
2. Search for "zoo_timezone"
3. Select and click **Uninstall**

The installer script will automatically remove the element files from `media/zoo/custom_elements/timezone/`. If any orphan files remain, delete `media/zoo/custom_elements/timezone/` manually via your file manager.

> **Note:** Uninstalling the plugin will remove the element definition. Any ZOO items that had a timezone value stored will retain the data in the database, but the element will no longer render until the plugin is reinstalled.

## License

MIT License — see [LICENSE](LICENSE).

## Credits

Built for [gavin.wales](https://gavin.wales) by Gavin D. J. Harper.

Developed with guidance from the ZOOlanders Essentials element architecture patterns.

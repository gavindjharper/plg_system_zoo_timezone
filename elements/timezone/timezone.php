<?php
/**
 * ZOO Timezone Element
 *
 * Provides a continent-grouped timezone picker with city search.
 * Stores the PHP timezone identifier and derives all other values
 * (name, abbreviation, UTC offset) dynamically — so DST transitions
 * are always accurate without re-saving the item.
 *
 * Stored sub-keys (via base Element class — no bindData override):
 *   timezone_id          — e.g. "Europe/London"
 *
 * Derived at render time (always live, DST-accurate):
 *   timezone_name        — e.g. "Europe / London"
 *   abbreviation         — e.g. "BST"
 *   utc_offset_hours     — e.g. "+1"  (integer string, signed)
 *   utc_offset_minutes   — e.g. "60"  (total minutes, signed)
 *   utc_offset_string    — e.g. "UTC+01:00"
 *   continent            — e.g. "Europe"
 *   city                 — e.g. "London"
 *
 * @package  plg_system_zoo_timezone
 */

defined('_JEXEC') or die;

class ElementTimezone extends Element
{
    /**
     * Check whether this element instance has a value.
     */
    public function hasValue($params = [])
    {
        $tzId = $this->get('timezone_id', '');
        return !empty($tzId);
    }

    /* ──────────────────────────────────────────────────────────
       EDIT FORM
    ────────────────────────────────────────────────────────── */

    public function edit()
    {
        $tzId     = $this->get('timezone_id', $this->config->get('default_timezone', ''));
        $nameCtrl = $this->getControlName('timezone_id');

        // Build grouped timezone list from PHP
        $groups = $this->_getGroupedTimezones();

        // Current preview data
        $preview = $this->_deriveValues($tzId);

        // Unique ID for this instance's DOM elements
        $uid = 'tz_' . md5($nameCtrl);

        ob_start();
        ?>
        <div id="<?php echo $uid; ?>_wrap" class="zoo-tz-picker" style="max-width:520px;">

            <!-- Search filter -->
            <input
                type="text"
                id="<?php echo $uid; ?>_search"
                placeholder="Search city or timezone…"
                autocomplete="off"
                style="width:100%; padding:6px 10px; margin-bottom:6px;
                       border:1px solid #ccc; font-family:'Montserrat',sans-serif;
                       font-size:12px; text-transform:uppercase; letter-spacing:0.06em;"
            />

            <!-- Grouped select -->
            <select
                name="<?php echo $nameCtrl; ?>"
                id="<?php echo $uid; ?>_select"
                style="width:100%; padding:6px 10px; border:1px solid #ccc;
                       font-family:'Montserrat',sans-serif; font-size:12px;"
            >
                <option value="">— Select timezone —</option>
                <?php foreach ($groups as $continent => $zones): ?>
                    <optgroup label="<?php echo htmlspecialchars($continent); ?>">
                        <?php foreach ($zones as $zone): ?>
                            <option
                                value="<?php echo htmlspecialchars($zone['id']); ?>"
                                data-offset="<?php echo $zone['offset_string']; ?>"
                                data-abbr="<?php echo htmlspecialchars($zone['abbreviation']); ?>"
                                data-city="<?php echo htmlspecialchars($zone['city']); ?>"
                                <?php echo ($zone['id'] === $tzId) ? 'selected' : ''; ?>
                            ><?php echo htmlspecialchars($zone['label']); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>

            <!-- Live preview swatch -->
            <div id="<?php echo $uid; ?>_preview" style="
                margin-top:8px; padding:10px 14px;
                background:#f4f3f6; border-left:3px solid #392570;
                font-family:'Montserrat',sans-serif; font-size:11px;
                display:<?php echo $tzId ? 'block' : 'none'; ?>;
            ">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                    <div>
                        <span style="font-weight:700; color:#392570; text-transform:uppercase; letter-spacing:0.08em; font-size:10px;">TIMEZONE</span><br>
                        <span id="<?php echo $uid; ?>_pv_name" style="font-size:13px; color:#333;"><?php echo htmlspecialchars($preview['timezone_name']); ?></span>
                    </div>
                    <div style="text-align:right;">
                        <span id="<?php echo $uid; ?>_pv_abbr" style="font-weight:600; color:#2f6150; font-size:14px;"><?php echo htmlspecialchars($preview['abbreviation']); ?></span>
                        <span id="<?php echo $uid; ?>_pv_offset" style="color:#666; margin-left:4px;"><?php echo htmlspecialchars($preview['utc_offset_string']); ?></span>
                    </div>
                </div>
                <div style="margin-top:6px; font-size:10px; color:#999;">
                    Offset: <span id="<?php echo $uid; ?>_pv_hours"><?php echo $preview['utc_offset_hours']; ?></span>h
                    (<span id="<?php echo $uid; ?>_pv_mins"><?php echo $preview['utc_offset_minutes']; ?></span> min)
                </div>
            </div>

        </div>

        <script>
        (function() {
            var uid    = '<?php echo $uid; ?>';
            var search = document.getElementById(uid + '_search');
            var select = document.getElementById(uid + '_select');
            var pvWrap = document.getElementById(uid + '_preview');

            // ── City search filter ──
            // Store original options & optgroups
            var origHTML = select.innerHTML;

            search.addEventListener('input', function() {
                var q = this.value.toLowerCase().replace(/[^a-z0-9\s\/]/g, '');
                if (!q) {
                    select.innerHTML = origHTML;
                    // Restore previous selection
                    restoreSelection();
                    return;
                }

                var parser = document.createElement('div');
                parser.innerHTML = origHTML;
                var groups = parser.querySelectorAll('optgroup');
                var html = '<option value="">— Select timezone —</option>';

                groups.forEach(function(g) {
                    var opts = g.querySelectorAll('option');
                    var matched = [];
                    opts.forEach(function(o) {
                        var text = (o.textContent + ' ' + o.value + ' ' + (o.dataset.city || '')).toLowerCase();
                        if (text.indexOf(q) !== -1) {
                            matched.push(o.outerHTML);
                        }
                    });
                    if (matched.length) {
                        html += '<optgroup label="' + g.label + '">' + matched.join('') + '</optgroup>';
                    }
                });

                select.innerHTML = html;
                restoreSelection();
            });

            function restoreSelection() {
                var stored = pvWrap.dataset.currentTz || '';
                if (stored) {
                    var opt = select.querySelector('option[value="' + stored + '"]');
                    if (opt) opt.selected = true;
                }
            }

            pvWrap.dataset.currentTz = select.value;

            // ── Selection change → update preview ──
            select.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                pvWrap.dataset.currentTz = this.value;

                if (!this.value) {
                    pvWrap.style.display = 'none';
                    return;
                }

                pvWrap.style.display = 'block';
                document.getElementById(uid + '_pv_name').textContent   = this.value.replace(/\//g, ' / ').replace(/_/g, ' ');
                document.getElementById(uid + '_pv_abbr').textContent   = opt.dataset.abbr || '';
                document.getElementById(uid + '_pv_offset').textContent = opt.dataset.offset || '';

                // Parse offset string like "UTC+05:30" to hours and minutes
                var offStr = opt.dataset.offset || 'UTC+00:00';
                var m = offStr.match(/UTC([+-])(\d{2}):(\d{2})/);
                if (m) {
                    var sign = m[1] === '+' ? 1 : -1;
                    var h = parseInt(m[2], 10) * sign;
                    var totalMin = (parseInt(m[2], 10) * 60 + parseInt(m[3], 10)) * sign;
                    document.getElementById(uid + '_pv_hours').textContent = (h >= 0 ? '+' : '') + h;
                    document.getElementById(uid + '_pv_mins').textContent  = (totalMin >= 0 ? '+' : '') + totalMin;
                }
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /* ──────────────────────────────────────────────────────────
       RENDER (front-end output)
    ────────────────────────────────────────────────────────── */

    /**
     * Render the element.
     *
     * Supported $params['output'] values:
     *   timezone_id          — "Europe/London"
     *   timezone_name        — "Europe / London"
     *   abbreviation         — "BST"
     *   utc_offset_hours     — "+1"
     *   utc_offset_minutes   — "+60"
     *   utc_offset_string    — "UTC+01:00"
     *   continent            — "Europe"
     *   city                 — "London"
     *   full                 — "Europe/London (BST, UTC+01:00)" (default)
     */
    public function render($params = [])
    {
        $tzId = $this->get('timezone_id', '');
        if (empty($tzId)) {
            return '';
        }

        $output = isset($params['output']) ? $params['output'] : 'full';
        $values = $this->_deriveValues($tzId);

        if (isset($values[$output])) {
            return $values[$output];
        }

        // Default: full display
        return $values['full'];
    }

    /* ──────────────────────────────────────────────────────────
       PRIVATE HELPERS
    ────────────────────────────────────────────────────────── */

    /**
     * Derive all timezone metadata from a timezone identifier.
     * Always uses the CURRENT offset (DST-aware).
     */
    private function _deriveValues($tzId)
    {
        $defaults = [
            'timezone_id'        => '',
            'timezone_name'      => '',
            'abbreviation'       => '',
            'utc_offset_hours'   => '0',
            'utc_offset_minutes' => '0',
            'utc_offset_string'  => 'UTC+00:00',
            'continent'          => '',
            'city'               => '',
            'full'               => '',
        ];

        if (empty($tzId)) {
            return $defaults;
        }

        try {
            $tz  = new DateTimeZone($tzId);
            $now = new DateTime('now', $tz);

            $offsetSec  = $tz->getOffset($now);
            $offsetMin  = (int) ($offsetSec / 60);
            $offsetHrs  = (int) ($offsetSec / 3600);
            $remainMin  = abs($offsetMin) % 60;

            $sign = $offsetSec >= 0 ? '+' : '-';
            $offsetStr = sprintf('UTC%s%02d:%02d', $sign, abs($offsetHrs), $remainMin);

            $parts     = explode('/', $tzId, 2);
            $continent = $parts[0];
            $city      = isset($parts[1]) ? str_replace(['/', '_'], [' / ', ' '], $parts[1]) : $continent;
            $tzName    = str_replace(['/', '_'], [' / ', ' '], $tzId);

            $abbr = $now->format('T');

            return [
                'timezone_id'        => $tzId,
                'timezone_name'      => $tzName,
                'abbreviation'       => $abbr,
                'utc_offset_hours'   => ($offsetHrs >= 0 ? '+' : '') . $offsetHrs,
                'utc_offset_minutes' => ($offsetMin >= 0 ? '+' : '') . $offsetMin,
                'utc_offset_string'  => $offsetStr,
                'continent'          => $continent,
                'city'               => $city,
                'full'               => $tzId . ' (' . $abbr . ', ' . $offsetStr . ')',
            ];
        } catch (Exception $e) {
            return $defaults;
        }
    }

    /**
     * Build a continent-grouped array of all PHP timezones
     * with labels showing city name + current UTC offset.
     */
    private function _getGroupedTimezones()
    {
        $identifiers = DateTimeZone::listIdentifiers();
        $groups = [];

        foreach ($identifiers as $tzId) {
            $parts = explode('/', $tzId, 2);

            // Skip non-geographic timezones (UTC, GMT, etc.)
            if (!isset($parts[1])) {
                continue;
            }

            $continent = $parts[0];
            $city      = str_replace('_', ' ', $parts[1]);

            try {
                $tz  = new DateTimeZone($tzId);
                $now = new DateTime('now', $tz);

                $offsetSec = $tz->getOffset($now);
                $sign      = $offsetSec >= 0 ? '+' : '-';
                $absH      = abs((int) ($offsetSec / 3600));
                $absM      = abs((int) ($offsetSec / 60)) % 60;
                $offsetStr = sprintf('UTC%s%02d:%02d', $sign, $absH, $absM);

                $abbr = $now->format('T');

                $groups[$continent][] = [
                    'id'            => $tzId,
                    'city'          => $city,
                    'abbreviation'  => $abbr,
                    'offset_string' => $offsetStr,
                    'offset_sec'    => $offsetSec,
                    'label'         => $city . ' (' . $abbr . ', ' . $offsetStr . ')',
                ];
            } catch (Exception $e) {
                continue;
            }
        }

        // Sort continents alphabetically
        ksort($groups);

        // Sort zones within each continent by UTC offset, then city name
        foreach ($groups as &$zones) {
            usort($zones, function ($a, $b) {
                if ($a['offset_sec'] !== $b['offset_sec']) {
                    return $a['offset_sec'] - $b['offset_sec'];
                }
                return strcmp($a['city'], $b['city']);
            });
        }

        return $groups;
    }
}

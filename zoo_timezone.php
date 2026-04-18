<?php
/**
 * @package     plg_system_zoo_timezone
 * @description Registers a custom Timezone element for YOOtheme ZOO CCK.
 *              Copies element files to media/zoo/custom_elements/ for
 *              guaranteed discovery by ZOO's element scanner, and also
 *              registers the plugin path as a fallback.
 * @license     MIT
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

class PlgSystemZoo_timezone extends CMSPlugin
{
    /**
     * Ensure element files are deployed and registered with ZOO.
     */
    public function onAfterInitialise()
    {
        // Bootstrap ZOO — bail if not installed
        if (!class_exists('App')) {
            $zoo_bootstrap = JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
            if (!file_exists($zoo_bootstrap)) {
                return;
            }
            require_once $zoo_bootstrap;
        }

        $zoo = App::getInstance('zoo');

        // ── Strategy 1: Deploy to media/zoo/custom_elements/ ──
        // ZOOlanders pre-registers this path, so ZOO's element
        // scanner will discover elements placed here automatically.
        $source = __DIR__ . '/elements/timezone';
        $dest   = JPATH_ROOT . '/media/zoo/custom_elements/timezone';

        if (is_dir($source) && !is_dir($dest)) {
            Folder::copy($source, $dest);
        }

        // ── Strategy 2: Also register the plugin's own path ──
        // Belt-and-suspenders — in case custom_elements doesn't
        // exist or isn't registered on a non-ZOOlanders site.
        $zoo->path->register(
            __DIR__ . '/elements',
            'elements:'
        );

        // Also ensure custom_elements is registered (for sites
        // where ZOOlanders may not have registered it yet).
        $customDir = JPATH_ROOT . '/media/zoo/custom_elements';
        if (is_dir($customDir)) {
            $zoo->path->register($customDir, 'elements:');
        }
    }
}

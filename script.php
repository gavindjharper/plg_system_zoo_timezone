<?php
/**
 * Installer script for plg_system_zoo_timezone.
 * Handles deployment and cleanup of element files in media/zoo/custom_elements/.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;

class PlgSystemZoo_timezoneInstallerScript
{
    /**
     * Runs after install or update.
     * Copies element files to media/zoo/custom_elements/ for ZOO discovery.
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'uninstall') {
            return true;
        }

        $source = $adapter->getParent()->getPath('source') . '/elements/timezone';
        $dest   = JPATH_ROOT . '/media/zoo/custom_elements/timezone';

        // Create custom_elements dir if it doesn't exist
        $customDir = JPATH_ROOT . '/media/zoo/custom_elements';
        if (!is_dir($customDir)) {
            Folder::create($customDir);
        }

        // Copy element (overwrite on update)
        if (is_dir($source)) {
            if (is_dir($dest)) {
                Folder::delete($dest);
            }
            Folder::copy($source, $dest);
        }

        return true;
    }

    /**
     * Runs on uninstall.
     * Removes element files from media/zoo/custom_elements/.
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        $dest = JPATH_ROOT . '/media/zoo/custom_elements/timezone';

        if (is_dir($dest)) {
            Folder::delete($dest);
        }

        return true;
    }
}

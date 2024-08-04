<?php

/**
 * @file SiteStatisticsPlugin.php
 *
 * Copyright (c) 2013-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SiteStatisticsPlugin
 *
 * @brief This plugin creates a public site wide statistics page.
 */

namespace APP\plugins\generic\siteStatistics;

use APP\core\Application;
use APP\plugins\generic\siteStatistics\SiteStatisticsPageHandler;
use PKP\core\PKPApplication;
use PKP\core\PKPPageRouter;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

define('SITESTATISTICS_NMI_TYPE', 'SITESTATISTICS_NMI');

class SiteStatisticsPlugin extends GenericPlugin
{

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return $success;
        }
        
        // Only show for site level
        if ($this->getCurrentContextId() != Application::CONTEXT_ID_NONE) {
            return $success;
        }

        if ($success) {
            Hook::add('LoadHandler', $this->setPageHandler(...));
            Hook::add('NavigationMenus::itemTypes', $this->addMenuItemTypes(...));
            Hook::add('NavigationMenus::displaySettings', $this->setMenuItemDisplayDetails(...));
        }
        return $success;
    }

    /**
     * Route requests for the site statistics page handler
     */
    public function setPageHandler(string $hookName, array $args): bool
    {
        $page = & $args[0];
        $handler = & $args[3];

        if ($this->getEnabled() && $page === 'statistics') {
            $handler = new SiteStatisticsPageHandler($this);
            return true;
        }
        return false;
    }

    /**
     * Add Navigation Menu Item type
     *
     * @param $hookName string
     * @param $params array [
     *        @option array Existing menu item types
     * ]
     */
    public function addMenuItemTypes(string $hookName, array $args)
    {
        $types =& $args[0];
        $types[SITESTATISTICS_NMI_TYPE] = [
            'title' => __('plugins.generic.siteStatistics.navMenuItem'),
            'description' => __('plugins.generic.siteStatistics.navMenuItem.description'),
        ];
    }

    /**
     * Set the display details for the custom menu item types
     *
     * @param $hookName string
     * @param $args array [
     *        @option NavigationMenuItem
     * ]
     */
    public function setMenuItemDisplayDetails(string $hookName, array $args)
    {
        $navigationMenuItem =& $args[0];
        if ($navigationMenuItem->getType() === SITESTATISTICS_NMI_TYPE) {
            $request = Application::get()->getRequest();
            $dispatcher = $request->getDispatcher();
            $navigationMenuItem->setUrl($dispatcher->url(
                $request,
                PKPApplication::ROUTE_PAGE,
                'index',
                'statistics',
                null,
                null
            ));
        }
    }

    /**
     * @copydoc Plugin::isSitePlugin()
     */
    public function isSitePlugin(): bool
    {
        // This is a site-wide plugin.
        return true;
    }

    /**
     * @copydoc LazyLoadPlugin::getName()
     */
    public function getName(): string
    {
        return 'siteStatisticsPlugin';
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.siteStatistics.name');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription(): string
    {
        return __('plugins.generic.siteStatistics.description');
    }

}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\siteStatistics\SiteStatisticsPlugin', '\SiteStatisticsPlugin');
}

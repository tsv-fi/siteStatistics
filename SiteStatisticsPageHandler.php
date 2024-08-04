<?php

/**
 * @file plugins/generic/siteStatistics/SiteStatisticsPageHandler.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3 or later. For full terms see the file docs/COPYING.
 *
 * @class SiteStatisticsPageHandler
 *
 * @ingroup plugins_generic_citationStyleLanguage
 *
 * @brief Handle router requests for the citation style language plugin
 */

namespace APP\plugins\generic\siteStatistics;

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\journal\JournalDAO;
use APP\submission\Submission;
use APP\statistics\StatisticsHelper;
use APP\template\TemplateManager;
use Illuminate\Support\Collection;
use PKP\cache\CacheManager;
use PKP\cache\FileCache;
use PKP\controllers\page\PageHandler;
use PKP\db\DAORegistry;
use PKP\submission\PKPSubmission;

class SiteStatisticsPageHandler extends Handler
{

    public SiteStatisticsPlugin $plugin;

    /**
     * Constructor
     */
    public function __construct(SiteStatisticsPlugin $plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
    }

    public function index($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $url = $request->getBaseUrl() . '/' . $this->plugin->getPluginPath() . '/css/siteStatistics.css';
        $templateMgr->addStyleSheet('siteStatisticsStyles', $url);

        // Fetch and cache values
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getCache('siteStatistics', 0, array($this, 'getSiteStatisticsCache'));

        $daysToStale = 1;
        $metrics = [];

        if (time() - $cache->getCacheTime() > 60 * 60 * 24 * $daysToStale) {
            $cache->flush();
        }

        $metrics = $cache->getContents();

        // Assign values
        $this->setupTemplate($request);
        $templateMgr->assign('siteName', $request->getSite()->getLocalizedTitle());
        $templateMgr->assign('journalCount', $metrics['journalCount']);
        $templateMgr->assign('issueCount', $metrics['issueCount']);
        $templateMgr->assign('submissionCount', $metrics['submissionCount']);
        $templateMgr->assign('allTimeDownloads', $metrics['allTimeDownloads']);
        $templateMgr->assign('allTimeMostRead', $this->getLocalizedMostReadArray($metrics['allTimeMostRead']));
        $templateMgr->assign('lastMonthMostRead', $this->getLocalizedMostReadArray($metrics['lastMonthMostRead']));

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'siteStatistics.tpl'
            )
        );
    }

    /**
    * Set cache.
    *
    * @param FileCache $cache
    * @return array site statistics in an array
    */
    public function getSiteStatisticsCache(FileCache $cache): array
    {

        $metrics = [];

        // Get number of enabled journals
        $journalDao = DAORegistry::getDAO('JournalDAO'); /** @var JournalDAO $journalDao */
        $metrics['journalCount'] = count($journalDao->getAll(true)->toArray());

        // Get number of published submission
        $metrics['submissionCount'] = Repo::submission()
            ->getCollector()
            ->filterByContextIds([Application::CONTEXT_ID_ALL])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getCount();

        // Get number of published issues
        $metrics['issueCount'] = Repo::issue()
            ->getCollector()
            ->filterByContextIds([Application::CONTEXT_ID_ALL])
            ->filterByPublished(true)
            ->getCount();

        // Get total download count for all submissions
        $allTimeDownloadsFilters = [
            'dateStart' => StatisticsHelper::STATISTICS_EARLIEST_DATE,
            'dateEnd' => date('Y-m-d', strtotime('yesterday')),
            'contextIds' => [],
            'assocTypes' => [Application::ASSOC_TYPE_SUBMISSION_FILE],
        ];
        $metrics['allTimeDownloads'] = Services::get('publicationStats')
            ->getQueryBuilder($allTimeDownloadsFilters)
            ->getSum([])
            ->value('metric');

        // Get list of 10 all time most read submissions
        $getAllTimeMostRead = Services::get('publicationStats')->getTotals([
            'contextIds' => [],
            'count' => 10,
            'assocTypes' => [Application::ASSOC_TYPE_SUBMISSION_FILE],
        ]);
        $metrics['allTimeMostRead'] = (new Collection($getAllTimeMostRead))
            ->map(function($result) {
                $submission = Repo::submission()->get($result->submission_id);
                $context = Services::get('context')->get($submission->getData('contextId'));
                return [
                    'submissionId' => $submission?->getId(),
                    'metric' => $result->metric
                ];
            })
            ->filter(function($result) {
                return $result['submissionId'] && $result['metric'];
            })
            ->toArray();

        // Get list of 10 all time most read submissions
        $getLastMonthMostRead = Services::get('publicationStats')->getTotals([
            'contextIds' => [],
            'count' => 10,
            'dateStart' => date('Y-m-d', strtotime("-30 days")),
            'assocTypes' => [Application::ASSOC_TYPE_SUBMISSION_FILE],
        ]);
        $metrics['lastMonthMostRead'] = (new Collection($getLastMonthMostRead))
            ->map(function($result) {
                $submission = Repo::submission()->get($result->submission_id);
                $context = Services::get('context')->get($submission->getData('contextId'));
                return [
                    'submissionId' => $submission?->getId(),
                    'metric' => $result->metric
                ];
            })
            ->filter(function($result) {
                return $result['submissionId'] && $result['metric'];
            })
            ->toArray();

        // Set the cache
        $cache->setEntireCache($metrics);

        return $metrics;
    }

    /**
    * Localize the data in an array containing most read articles data.
    *
    * @param array $mostReadArray containing the raw data
    * @return array site statistics in an array
    */
    protected function getLocalizedMostReadArray(array $mostReadArray): array
    {
        $localizedMostReadArray = [];
        foreach($mostReadArray as $metric){
            $submission = Repo::submission()->get($metric['submissionId']);
            $context = Services::get('context')->get($submission->getData('contextId'));
            if(isset($submission) && $submission?->getCurrentPublication()->getData('status') === PKPSubmission::STATUS_PUBLISHED) 
            {
                $localizedMostReadArray[] = [
                    'url' => Application::get()->getRequest()->url($context?->getPath(), 'article', 'view', [$submission->getBestId()]),
                    'submission' => $submission?->getCurrentPublication()->getLocalizedFullTitle(null, 'html'),
                    'context' => $context?->getLocalizedName(),
                    'metric' => $metric['metric']
                ];
            }
        }
        return $localizedMostReadArray;
    }
 
}
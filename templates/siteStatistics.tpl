{**
 * siteStatistics.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Site statistics page.
 *
 *}
{capture assign="pageTitle"}{translate key="plugins.generic.siteStatistics.pageTitle" siteName=$siteName}{/capture}
{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}

<div class="page page_index_statistics">
    {include file="frontend/components/breadcrumbs.tpl" currentTitle=$pageTitle}
    <h1>
        {$pageTitle}
    </h1>

    <div class="container">
        <h2 class="heading">
            {translate key="plugins.generic.siteStatistics.keyNumbers"}
        </h2>
        <ul class="stats-list">
            <li class="stats-item">
                <div class="stats-title">
                    <i class="fa fa-server stats-icon"></i>
                    {translate key="plugins.generic.siteStatistics.journalCount"}
                </div>
                <div class="stats-value">
                    {$journalCount}
                </div>
            </li>
            <li class="stats-item">
                <div class="stats-title">
                    <i class="fa fa-book stats-icon"></i>
                    {translate key="plugins.generic.siteStatistics.issueCount"}
                </div>
                <div class="stats-value">
                    {$issueCount}
                </div>
            </li>
            <li class="stats-item">
                <div class="stats-title">
                    <i class="fa fa-files-o stats-icon"></i>
                    {translate key="plugins.generic.siteStatistics.submissionCount"}
                </div>
                <div class="stats-value">
                    {$submissionCount}
                </div>
            </li>
            <li class="stats-item">
                <div class="stats-title">
                    <i class="fa fa-download stats-icon"></i>
                    {translate key="plugins.generic.siteStatistics.allTimeDownloads"}
                </div>
                <div class="stats-value">
                    {$allTimeDownloads}
                </div>
            </li>
        </ul>
    </div>

    <div class="container">
        <div class="grid">
            <div>
                <h2 class="list-heading">
                    {translate key="plugins.generic.siteStatistics.allTimeMostReadSubmissions"}
                </h2>
                <ul class="submission-list">
                    {foreach from=$allTimeMostRead item="submission"}
                    <li>
                        <a href="{$submission.url}" class="submission-item">
                            <div class="submission-title">
                                {$submission.submission}
                            </div>
                            <div class="submission-meta">
                                <div class="submission-context">
                                    {$submission.context}
                                </div>
                                <div class="submission-metric">
                                    <i class="fa fa-eye metric-icon"></i> {$submission.metric}
                                </div>
                            </div>
                        </a>
                    </li>
                    {/foreach}
                </ul>
            </div>

            <div>
                <h2 class="list-heading">
                    {translate key="plugins.generic.siteStatistics.lastMonthMostReadSubmissions"}
                </h2>
                <ul class="submission-list">
                    {foreach from=$lastMonthMostRead item="submission"}
                    <li>
                        <a href="{$submission.url}" class="submission-item">
                            <div class="submission-title">
                                {$submission.submission}
                            </div>
                            <div class="submission-meta">
                                <div class="submission-context">
                                    {$submission.context}
                                </div>
                                <div class="submission-metric">
                                    <i class="fa fa-eye metric-icon"></i> {$submission.metric}
                                </div>
                            </div>
                        </a>
                    </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>

{include file="frontend/components/footer.tpl"}
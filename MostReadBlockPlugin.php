<?php

/**
 * @file plugins/blocks/mostRead/MostReadBlockPlugin.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MostReadBlockPlugin
 * @brief Plugin class for the most read articles block plugin
 */

namespace APP\plugins\blocks\mostRead;

use APP\core\Application;
use APP\facades\Repo;
use PKP\plugins\BlockPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;

class MostReadBlockPlugin extends BlockPlugin {
	
	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	public function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return string
	 */
	public function getDisplayName() {
		return __('plugins.blocks.mostRead.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return string
	 */
	public function getDescription() {
		return __('plugins.blocks.mostRead.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	public function getActions($request, $actionArgs) {
		$actions = parent::getActions($request, $actionArgs);
		if (!$this->getEnabled()) {
			return $actions;
		}
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();
		
		array_unshift(
			$actions,
			new LinkAction(
				'settings',
				new AjaxModal(
					$dispatcher->url(
						$request, 
						Application::ROUTE_COMPONENT,
						null,
						'grid.settings.plugins.SettingsPluginGridHandler',
						'manage',
						null,
						['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'blocks']
					),
					$this->getDisplayName()
				),
				__('manager.plugins.settings'),
				null
			)
		);
		
		return $actions;
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	public function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();
				$contextId = $context ? $context->getId() : Application::SITE_CONTEXT_ID;
				
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->registerPlugin('function', 'plugin_url', [$this, 'smartyPluginUrl']);
				
				$this->import('MostReadSettingsForm');
				$form = new MostReadSettingsForm($this, $contextId);
				
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	public function getSupportedContexts() {
		return [BLOCK_CONTEXT_SIDEBAR];
	}

	/**
	 * Get the block context
	 * @return int
	 */
	public function getBlockContext() {
		return BLOCK_CONTEXT_SIDEBAR;
	}

	/**
	 * Get the HTML contents for this block.
	 * @param $templateMgr PKPTemplateManager
	 * @param $request PKPRequest
	 * @return string
	 */
	public function getContents($templateMgr, $request = null) {
		$context = $request->getContext();
		if (!$context) {
			return '';
		}
		
		$contextId = $context->getId();
		
		// Get plugin settings
		$mostReadDays = $this->getSetting($contextId, 'mostReadDays');
		if (!$mostReadDays || $mostReadDays < 1) {
			$mostReadDays = 7; // default value
		}
		
		$mostReadBlockTitle = $this->getSetting($contextId, 'mostReadBlockTitle');
		$locale = Application::get()->getRequest()->getLocale();
		
		$blockTitle = '';
		if ($mostReadBlockTitle && is_array($mostReadBlockTitle)) {
			$blockTitle = $mostReadBlockTitle[$locale] ?? __('plugins.blocks.mostRead.settings.blockTitle');
		} else {
			$blockTitle = __('plugins.blocks.mostRead.settings.blockTitle');
		}
		
		// Get most read articles
		$mostReadArticles = $this->getMostReadArticles($contextId, $mostReadDays);
		
		$templateMgr->assign([
			'blockTitle' => $blockTitle,
			'mostReadArticles' => $mostReadArticles,
		]);
		
		return parent::getContents($templateMgr, $request);
	}

	/**
	 * Get most read articles from metrics
	 * @param int $contextId
	 * @param int $days
	 * @return array
	 */
	private function getMostReadArticles($contextId, $days) {
		$statsService = app()->get('submission')->getStatsService();
		
		$dateStart = date('Y-m-d', strtotime("-{$days} days"));
		$dateEnd = date('Y-m-d');
		
		// Get statistics for published articles
		$args = [
			'contextIds' => [$contextId],
			'dateStart' => $dateStart,
			'dateEnd' => $dateEnd,
			'count' => 5,
			'orderBy' => 'total',
			'orderDirection' => 'DESC',
		];
		
		$statsRecords = $statsService->getRecords($args);
		
		$articles = [];
		
		foreach ($statsRecords as $record) {
			$submission = Repo::submission()->get($record->submissionId);
			
			if (!$submission || $submission->getData('status') !== STATUS_PUBLISHED) {
				continue;
			}
			
			$publication = $submission->getCurrentPublication();
			if (!$publication) {
				continue;
			}
			
			$articles[] = [
				'id' => $submission->getId(),
				'title' => $publication->getLocalizedTitle(),
				'views' => $record->total,
				'url' => Application::get()->getRequest()->getDispatcher()->url(
					Application::get()->getRequest(),
					Application::ROUTE_PAGE,
					null,
					'article',
					'view',
					[$submission->getBestId()]
				)
			];
			
			if (count($articles) >= 5) {
				break;
			}
		}
		
		return $articles;
	}
}
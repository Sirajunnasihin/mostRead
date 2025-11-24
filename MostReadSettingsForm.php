<?php

/**
 * @file plugins/blocks/mostRead/MostReadSettingsForm.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MostReadSettingsForm
 * @brief Form for journal managers to modify Most Read plugin settings
 */

namespace APP\plugins\blocks\mostRead;

use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorCSRF;
use APP\core\Application;
use APP\template\TemplateManager;

class MostReadSettingsForm extends Form {

	/** @var int */
	private $_contextId;

	/** @var MostReadBlockPlugin */
	private $_plugin;

	/**
	 * Constructor
	 * @param $plugin MostReadBlockPlugin
	 * @param $contextId int
	 */
	public function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	public function initData() {
		$contextId = $this->_contextId;
		$plugin = $this->_plugin;

		$this->setData('mostReadDays', $plugin->getSetting($contextId, 'mostReadDays'));
		$this->setData('mostReadBlockTitle', $plugin->getSetting($contextId, 'mostReadBlockTitle'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	public function readInputData() {
		$this->readUserVars(['mostReadDays', 'mostReadBlockTitle']);
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	public function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save settings.
	 * @return null|mixed
	 */
	public function execute(...$functionArgs) {
		$plugin = $this->_plugin;
		$contextId = $this->_contextId;

		$plugin->updateSetting($contextId, 'mostReadDays', $this->getData('mostReadDays'), 'int');
		$plugin->updateSetting($contextId, 'mostReadBlockTitle', $this->getData('mostReadBlockTitle'), 'string');

		parent::execute(...$functionArgs);
	}
}
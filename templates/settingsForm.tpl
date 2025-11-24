{**
 * plugins/blocks/mostRead/settingsForm.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Most Read plugin settings
 *}
<script>
	$(function() {ldelim}
		$('#mostReadSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="mostReadSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="blocks" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="mostReadSettingsFormNotification"}

	<div id="description">{translate key="plugins.blocks.mostRead.settings.description"}</div>

	{fbvFormArea id="mostReadSettingsFormArea"}
		{fbvFormSection title="plugins.blocks.mostRead.settings.mostReadDays"}
			{fbvElement type="text" id="mostReadDays" value=$mostReadDays label="plugins.blocks.mostRead.settings.mostReadDaysDescription" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		
		{fbvFormSection title="plugins.blocks.mostRead.settings.blockTitle" for="context"}
			{fbvElement type="text" multilingual=true name="mostReadBlockTitle" id="mostReadBlockTitle" value=$mostReadBlockTitle label="plugins.blocks.mostRead.settings.blockTitleDescription"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
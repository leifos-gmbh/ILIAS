<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBlogDraftsDerivedTaskProvider
 * @author Thomas Famula <famula@leifos.de>
 */
class ilBlogDraftsDerivedTaskProvider implements ilDerivedTaskProvider
{
	/** @var ilTaskService */
	protected $taskService;

	/** @var \ilAccess */
	protected $accessHandler;

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilSetting */
	protected $settings;

	/** @var \ilCtrl */
	protected $ctrl;

	/**
	 * ilBlogDraftsDerivedTaskProvider constructor.
	 * @param \ilTaskService $taskService
	 * @param \ilAccessHandler $accessHandler
	 * @param \ilLanguage $lng
	 * @param \ilSetting $settings
	 * @param \ilCtrl $ctrl
	 */
	public function __construct(
		ilTaskService $taskService,
		\ilAccessHandler $accessHandler,
		\ilLanguage $lng,
		\ilSetting $settings,
		\ilCtrl $ctrl
	) {
		$this->taskService = $taskService;
		$this->accessHandler = $accessHandler;
		$this->lng = $lng;
		$this->settings = $settings;
		$this->ctrl = $ctrl;

		$this->lng->loadLanguageModule('blog');
	}

	/**
	 * @inheritDoc
	 */
	public function isActive(): bool
	{
		return (bool)$this->settings->get('save_post_drafts', false);
		//return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getTasks(int $user_id): array
	{
		$tasks = [];

		$blogs = ilBlogPosting::searchBlogsByAuthor($user_id);
		foreach ($blogs as $blog_id) {
			$posts = ilBlogPosting::getAllPostings($blog_id);
			//var_dump($posts);
			foreach ($posts as $post_id => $post) {
				if ((int)$post['author'] !== $user_id) {
					continue;
				}

				$active = ilBlogPosting::_lookupActive($post_id, "blp");
				if (!$active) {
					$refId = $this->getFirstRefIdWithPermission('read', $blog_id, $user_id);
					$wspId = 0;
					//var_dump($post, $refId);

					if ($refId === 0) {
						$wspId = $this->getWsId($blog_id, $user_id);
						//var_dump($wspId);
						//$aa = $this->getOId($wsId, $user_id);
						//var_dump($aa);
					}

					$title = sprintf(
						$this->lng->txt('frm_task_publishing_draft_title'),
						$post['title']
					);
					//var_dump($refId);

					$task = $this->taskService->derived()->factory()->task(
						$title,
						$refId,
						0,
						strtotime($post['date']),
						$wspId
					);

					$params['blpg'] = $post_id;
					//$params['cmd'] = 'edit';
					//$params['cmdClass'] = 'ilobjbloggui';
					/*$params['thr_pk'] = $draft->getThreadId();
					$params['pos_pk'] = $draft->getPostId();
					$params['cmd'] = 'viewThread';
					$anchor = '#draft_' . $draft->getDraftId();*/

					//$url = \ilLink::_getStaticLink($wspId, 'blog', true, "_" . $post_id . "_edit");
					$url = \ilLink::_getStaticLink($wspId, 'blog', true, "_" . $post_id . "_edit_wsp");
					//$url = \ilLink::_getLink($refId, 'blpg', $params);
					var_dump($url);

					$tasks[] = $task->withUrl($url);

					//$tasks[] = $task;
				}
			}
		}


		return $tasks;
	}

	/**
	 * @param string $operation
	 * @param int $objId
	 * @param int $userId
	 * @return int
	 */
	protected function getFirstRefIdWithPermission(string $operation, int $objId, int $userId): int
	{
		foreach (\ilObject::_getAllReferences($objId) as $refId) {
			if ($this->accessHandler->checkAccessOfUser($userId, $operation, '', $refId)) {
				return $refId;
			}
		}

		return 0;
	}

	protected function getWsId(int $objId, int $userId): int
	{
		$wst = new ilWorkspaceTree($userId);
		$nodeId = $wst->lookupNodeId($objId);
		return $nodeId;
	}

	protected function getOId(int $objId, int $userId): int
	{
		$wst = new ilWorkspaceTree($userId);
		$aa = $wst->lookupOwner($objId);
		return $aa;
	}
}
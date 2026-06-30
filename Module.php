<?php declare(strict_types = 1);

namespace Modules\MapAnimation;

use Zabbix\Core\CModule;
use CController as CAction;
use APP;
use CMenuItem;

class Module extends CModule {

	private const TARGET_ACTIONS = ['map.view'];

	public function init(): void {
		// Adds Monitoring -> Map Animation Settings menu item.
		APP::Component()->get('menu.main')
			->findOrAdd(_('Monitoring'))
			->getSubmenu()
			->add((new CMenuItem(_('Map Animation Settings')))
				->setAction('mapanimation.settings')
			);
	}

	public function onBeforeAction(CAction $action): void {
	}

	public function onTerminate(CAction $action): void {
		$action_name = $action->getAction();

		if ($action_name === null || !in_array($action_name, self::TARGET_ACTIONS, true)) {
			return;
		}

		$css_url = 'modules/technousher_map_animation/assets/css/map-animation.css';
		$js_url  = 'modules/technousher_map_animation/assets/js/map-animation.js';

		echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($css_url) . '">';
		echo '<script type="text/javascript" src="' . htmlspecialchars($js_url) . '"></script>';
	}
}

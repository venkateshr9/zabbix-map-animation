<?php declare(strict_types = 1);

namespace Modules\MapAnimation\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseRedirect;
use CMessageHelper;

/**
 * Backs the "Map Animation Settings" menu page.
 * GET  -> render the form, pre-filled from assets/config.json (or defaults).
 * POST -> validate + write assets/config.json, then redirect back with a message.
 *
 * NOTE: Zabbix modules cannot create their own database tables, so settings
 * are persisted as a JSON file on disk rather than in Zabbix's DB. This is
 * the standard workaround used by community modules needing GUI-configurable
 * state. The web server user (apache/nginx/php-fpm) needs write access to
 * assets/config.json - see README.md for the chown/chmod command.
 */
class SettingsView extends CController {

	private const CONFIG_PATH = __DIR__ . '/../assets/config.json';

	private const DEFAULTS = [
		'enabled' => true,
		'refresh_interval_ms' => 2000,
		'animation_style' => 'packet',
		'animation_duration_s' => 1.5,
		'direction' => 'forward',
		'randomize_start' => true,
		'packet_dash' => '8 500',
		'packet_stroke_width' => 4,
		'packet_count' => 3,
		'base_dash' => '2 10',
		'base_stroke_width' => 3,
		'glow_enabled' => true,
		'pulse_min_opacity' => 0.25,
		'exclude_color_enabled' => true,
		'exclude_r_min' => 150,
		'exclude_g_max' => 80,
		'exclude_b_max' => 80
	];

	protected function init(): void {
		$this->disableCsrfValidation();
	}

	protected function checkInput(): bool {
		$fields = [
			'form_submit' => 'in 1',
			'enabled' => 'in 0,1',
			'refresh_interval_ms' => 'int32',
			'animation_style' => 'in packet,multi_packet,pulse',
			'animation_duration_s' => 'string',
			'direction' => 'in forward,reverse',
			'randomize_start' => 'in 0,1',
			'packet_dash' => 'string',
			'packet_stroke_width' => 'int32',
			'packet_count' => 'int32',
			'base_dash' => 'string',
			'base_stroke_width' => 'int32',
			'glow_enabled' => 'in 0,1',
			'pulse_min_opacity' => 'string',
			'exclude_color_enabled' => 'in 0,1',
			'exclude_r_min' => 'int32',
			'exclude_g_max' => 'int32',
			'exclude_b_max' => 'int32'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseRedirect(
				(new \CUrl('zabbix.php'))->setArgument('action', 'mapanimation.settings')
			));
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		// Restrict to admins - adjust if regular users should also configure this.
		return $this->getUserType() >= USER_TYPE_ZABBIX_ADMIN;
	}

	protected function doAction(): void {
		if ($this->hasInput('form_submit')) {
			$this->saveConfig();

			CMessageHelper::setSuccessTitle(_('Settings updated'));

			$this->setResponse(new CControllerResponseRedirect(
				(new \CUrl('zabbix.php'))->setArgument('action', 'mapanimation.settings')
			));
			return;
		}

		$config = $this->loadConfig();

		$this->setResponse(new CControllerResponseData([
			'config' => $config
		]));
	}

	private function loadConfig(): array {
		if (is_readable(self::CONFIG_PATH)) {
			$json = json_decode((string) file_get_contents(self::CONFIG_PATH), true);
			if (is_array($json)) {
				return array_merge(self::DEFAULTS, $json);
			}
		}
		return self::DEFAULTS;
	}

	private function saveConfig(): void {
		$config = [
			'enabled' => (bool) $this->getInput('enabled', 0),
			'refresh_interval_ms' => (int) $this->getInput('refresh_interval_ms', 2000),
			'animation_style' => $this->getInput('animation_style', 'packet'),
			'animation_duration_s' => (float) $this->getInput('animation_duration_s', 1.5),
			'direction' => $this->getInput('direction', 'forward'),
			'randomize_start' => (bool) $this->getInput('randomize_start', 0),
			'packet_dash' => $this->getInput('packet_dash', '8 500'),
			'packet_stroke_width' => (int) $this->getInput('packet_stroke_width', 4),
			'packet_count' => (int) $this->getInput('packet_count', 3),
			'base_dash' => $this->getInput('base_dash', '2 10'),
			'base_stroke_width' => (int) $this->getInput('base_stroke_width', 3),
			'glow_enabled' => (bool) $this->getInput('glow_enabled', 0),
			'pulse_min_opacity' => (float) $this->getInput('pulse_min_opacity', 0.25),
			'exclude_color_enabled' => (bool) $this->getInput('exclude_color_enabled', 0),
			'exclude_r_min' => (int) $this->getInput('exclude_r_min', 150),
			'exclude_g_max' => (int) $this->getInput('exclude_g_max', 80),
			'exclude_b_max' => (int) $this->getInput('exclude_b_max', 80)
		];

		file_put_contents(self::CONFIG_PATH, json_encode($config, JSON_PRETTY_PRINT));
	}
}

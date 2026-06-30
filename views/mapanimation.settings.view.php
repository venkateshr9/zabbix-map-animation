<?php declare(strict_types = 1);
/**
 * @var CView $this
 * @var array $data
 */

$config = $data['config'];

function field_with_hint($field, $hint) {
	return (new CDiv([
		$field,
		(new CDiv($hint))
			->addClass('second-column')
			->addStyle('color: #8c8c8c; font-size: 11px; margin-top: 4px; max-width: 480px;')
	]));
}

$form = (new CForm('post'))
	->setId('map-animation-settings-form')
	->addVar('form_submit', 1);

$form_list = new CFormList('map-animation-form-list');

$form_list->addRow(
	new CLabel(_('Enable animation'), 'enabled'),
	field_with_hint(
		(new CCheckBox('enabled'))->setChecked((bool) $config['enabled']),
		_('Turn the whole link-flow animation on or off across all maps without uninstalling the module.')
	)
);

$form_list->addRow(
	new CLabel(_('Animation style'), 'animation_style'),
	field_with_hint(
		(new CSelect('animation_style'))
			->setValue($config['animation_style'])
			->addOptions(CSelect::createOptionsFromArray([
				'packet' => _('Single packet'),
				'multi_packet' => _('Multiple packets'),
				'pulse' => _('Pulse (no movement)')
			])),
		_('Single packet: one dash travels the link. Multiple packets: several evenly-spaced dashes travel together, useful for high-throughput links. Pulse: the link fades in/out in place instead of moving - lighter on rendering for maps with many links.')
	)
);

$form_list->addRow(
	new CLabel(_('Direction'), 'direction'),
	field_with_hint(
		(new CSelect('direction'))
			->setValue($config['direction'])
			->addOptions(CSelect::createOptionsFromArray([
				'forward' => _('Forward'),
				'reverse' => _('Reverse')
			])),
		_('Direction the packet(s) travel along the link. Zabbix map links don\'t inherently know "source to target" - this just flips the animation direction visually.')
	)
);

$form_list->addRow(
	new CLabel(_('Randomize start offset'), 'randomize_start'),
	field_with_hint(
		(new CCheckBox('randomize_start'))->setChecked((bool) $config['randomize_start']),
		_('When enabled, each link\'s animation starts at a random point in its cycle instead of all links pulsing/moving in perfect sync. Recommended on for a more natural, less robotic look on maps with many links.')
	)
);

$form_list->addRow(
	new CLabel(_('Refresh interval (ms)'), 'refresh_interval_ms'),
	field_with_hint(
		(new CNumericBox('refresh_interval_ms', (int) $config['refresh_interval_ms'], 6))->setWidth(120),
		_('How often (in milliseconds) the script re-scans the map for links to animate. Lower = more responsive to map changes, higher = less browser CPU usage. 2000 is a good default.')
	)
);

$form_list->addRow(
	new CLabel(_('Animation duration (seconds)'), 'animation_duration_s'),
	field_with_hint(
		(new CTextBox('animation_duration_s', (string) $config['animation_duration_s']))->setWidth(120),
		_('Time for one moving packet to travel the full length of a link. Smaller number = faster-moving packets.')
	)
);

$form_list->addRow(
	new CLabel(_('Packet dash pattern'), 'packet_dash'),
	field_with_hint(
		(new CTextBox('packet_dash', $config['packet_dash']))->setWidth(120),
		_('SVG stroke-dasharray for the moving "packet" segment, as two numbers: dash length, gap length. Example "8 500" draws a short 8px dash followed by a long gap, giving the look of one packet traveling alone.')
	)
);

$form_list->addRow(
	new CLabel(_('Packet stroke width'), 'packet_stroke_width'),
	field_with_hint(
		(new CNumericBox('packet_stroke_width', (int) $config['packet_stroke_width'], 3))->setWidth(120),
		_('Thickness in pixels of the moving packet line. Larger = more visually prominent.')
	)
);

$form_list->addRow(
	new CLabel(_('Packet count'), 'packet_count'),
	field_with_hint(
		(new CNumericBox('packet_count', (int) $config['packet_count'], 2))->setWidth(120),
		_('Only used when animation style is "Multiple packets". Number of evenly-spaced dashes traveling the link at once (1-8). Higher values suggest heavier traffic.')
	)
);

$form_list->addRow(
	new CLabel(_('Base link dash pattern'), 'base_dash'),
	field_with_hint(
		(new CTextBox('base_dash', $config['base_dash']))->setWidth(120),
		_('SVG stroke-dasharray applied to the underlying (non-moving) link line. Example "2 10" gives a faint dotted baseline so the moving packet stands out against it.')
	)
);

$form_list->addRow(
	new CLabel(_('Base link stroke width'), 'base_stroke_width'),
	field_with_hint(
		(new CNumericBox('base_stroke_width', (int) $config['base_stroke_width'], 3))->setWidth(120),
		_('Thickness in pixels of the underlying link line itself (separate from the moving packet).')
	)
);

$form_list->addRow(
	new CLabel(_('Glow effect'), 'glow_enabled'),
	field_with_hint(
		(new CCheckBox('glow_enabled'))->setChecked((bool) $config['glow_enabled']),
		_('Adds a soft drop-shadow glow around the moving packet, colored to match the link. Turn off for a flatter look or to reduce rendering cost on maps with many links.')
	)
);

$form_list->addRow(
	new CLabel(_('Pulse minimum opacity'), 'pulse_min_opacity'),
	field_with_hint(
		(new CTextBox('pulse_min_opacity', (string) $config['pulse_min_opacity']))->setWidth(120),
		_('Only used when animation style is "Pulse". Lowest opacity (0.0-1.0) the link fades down to between pulses. Lower = more dramatic fade, e.g. 0.25 fades to 25% visible.')
	)
);

$form_list->addRow((new CTag('h4', true, _('Color exclusion')))->addStyle('margin-top:20px;'));

$form_list->addRow(
	new CLabel(_('Exclude matching links'), 'exclude_color_enabled'),
	field_with_hint(
		(new CCheckBox('exclude_color_enabled'))->setChecked((bool) $config['exclude_color_enabled']),
		_('When enabled, links whose color matches the thresholds below are left static (no animation) - typically used to skip animating red "down/critical" links, since a moving packet on a dead link is misleading.')
	)
);

$form_list->addRow(
	new CLabel(_('Red channel minimum'), 'exclude_r_min'),
	field_with_hint(
		(new CNumericBox('exclude_r_min', (int) $config['exclude_r_min'], 3))->setWidth(120),
		_('A link is excluded only if its color\'s red value is ABOVE this number (0-255). Used together with the green/blue maximums below to detect "red" link colors.')
	)
);

$form_list->addRow(
	new CLabel(_('Green channel maximum'), 'exclude_g_max'),
	field_with_hint(
		(new CNumericBox('exclude_g_max', (int) $config['exclude_g_max'], 3))->setWidth(120),
		_('A link is excluded only if its color\'s green value is BELOW this number (0-255).')
	)
);

$form_list->addRow(
	new CLabel(_('Blue channel maximum'), 'exclude_b_max'),
	field_with_hint(
		(new CNumericBox('exclude_b_max', (int) $config['exclude_b_max'], 3))->setWidth(120),
		_('A link is excluded only if its color\'s blue value is BELOW this number (0-255). Tip: inspect your map\'s actual "down" link color in browser DevTools to set these three thresholds accurately.')
	)
);

$form->addItem($form_list);

$form->addItem(
	(new CDiv(
		(new CSubmitButton(_('Save settings'), 'action', 'mapanimation.settings'))
			->addClass('btn-primary')
	))->addStyle('margin-top: 16px;')
);

(new CHtmlPage())
	->setTitle(_('Map Animation Settings'))
	->addItem($form)
	->show();

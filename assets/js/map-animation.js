/**
 * Link flow animation for Zabbix native maps.
 * Settings are loaded from config.json, written by the GUI settings page
 * (Monitoring -> Map Animation Settings). Falls back to defaults if that
 * file is missing or fails to load.
 */
(function () {
	'use strict';

	var DEFAULTS = {
		enabled: true,
		refresh_interval_ms: 2000,
		animation_style: 'packet',        // 'packet' | 'multi_packet' | 'pulse'
		animation_duration_s: 1.5,
		direction: 'forward',              // 'forward' | 'reverse'
		randomize_start: true,
		packet_dash: '8 500',
		packet_stroke_width: 4,
		packet_count: 3,                   // used only by 'multi_packet' style
		base_dash: '2 10',
		base_stroke_width: 3,
		glow_enabled: true,
		pulse_min_opacity: 0.25,
		exclude_color_enabled: true,
		exclude_r_min: 150,
		exclude_g_max: 80,
		exclude_b_max: 80
	};

	var config = DEFAULTS;
	var intervalHandle = null;

	function randomDelay() {
		return config.randomize_start ? (Math.random() * config.animation_duration_s) : 0;
	}

	function buildPacket(line, stroke, dashOffsetSign) {
		var packet = line.cloneNode(true);
		packet.classList.add('packet-clone');
		packet.style.strokeDasharray = config.packet_dash;
		packet.style.strokeWidth = config.packet_stroke_width;
		packet.style.animationName = 'packetMove';
		packet.style.animationDuration = config.animation_duration_s + 's';
		packet.style.animationTimingFunction = 'linear';
		packet.style.animationIterationCount = 'infinite';
		packet.style.animationDirection = (dashOffsetSign === -1) ? 'reverse' : 'normal';
		packet.style.animationDelay = '-' + randomDelay() + 's';
		packet.style.filter = config.glow_enabled ? ('drop-shadow(0 0 6px ' + stroke + ')') : '';
		packet.style.pointerEvents = 'none';
		return packet;
	}

	function buildPulse(line, stroke) {
		var pulse = line.cloneNode(true);
		pulse.classList.add('packet-clone');
		pulse.style.strokeWidth = config.packet_stroke_width;
		pulse.style.animationName = 'linkPulse';
		pulse.style.animationDuration = config.animation_duration_s + 's';
		pulse.style.animationTimingFunction = 'ease-in-out';
		pulse.style.animationIterationCount = 'infinite';
		pulse.style.animationDelay = '-' + randomDelay() + 's';
		pulse.style.filter = config.glow_enabled ? ('drop-shadow(0 0 6px ' + stroke + ')') : '';
		pulse.style.pointerEvents = 'none';
		pulse.style.setProperty('--linkflow-pulse-min', config.pulse_min_opacity);
		return pulse;
	}

	function applyPacketFlow() {
		if (!config.enabled) {
			return;
		}

		var dashOffsetSign = (config.direction === 'reverse') ? -1 : 1;

		document.querySelectorAll('svg line').forEach(function (line) {
			var stroke = window.getComputedStyle(line).stroke;
			if (!stroke) return;

			var match = stroke.match(/\d+/g);
			if (!match) return;

			var r = parseInt(match[0], 10);
			var g = parseInt(match[1], 10);
			var b = parseInt(match[2], 10);

			var isExcluded = config.exclude_color_enabled
				&& (r > config.exclude_r_min && g < config.exclude_g_max && b < config.exclude_b_max);

			// Remove all previously inserted clones for this line before re-adding.
			var sibling = line.nextSibling;
			while (sibling && sibling.classList && sibling.classList.contains('packet-clone')) {
				var toRemove = sibling;
				sibling = sibling.nextSibling;
				toRemove.remove();
			}

			if (isExcluded) {
				line.style.animation = '';
				return;
			}

			line.style.strokeDasharray = config.base_dash;
			line.style.strokeWidth = config.base_stroke_width;

			if (config.animation_style === 'pulse') {
				var pulse = buildPulse(line, stroke);
				line.parentNode.insertBefore(pulse, line.nextSibling);
			}
			else if (config.animation_style === 'multi_packet') {
				var count = Math.max(1, Math.min(8, config.packet_count));
				for (var i = 0; i < count; i++) {
					var packet = buildPacket(line, stroke, dashOffsetSign);
					// Stagger evenly around the cycle instead of all starting together.
					packet.style.animationDelay = '-' + ((i / count) * config.animation_duration_s) + 's';
					line.parentNode.insertBefore(packet, line.nextSibling);
				}
			}
			else {
				var single = buildPacket(line, stroke, dashOffsetSign);
				line.parentNode.insertBefore(single, line.nextSibling);
			}
		});
	}

	function startLoop() {
		if (intervalHandle) {
			clearInterval(intervalHandle);
		}
		intervalHandle = setInterval(applyPacketFlow, config.refresh_interval_ms);
		applyPacketFlow();
	}

	function loadConfigAndStart() {
		fetch('modules/technousher_map_animation/assets/config.json', {cache: 'no-store'})
			.then(function (resp) {
				if (!resp.ok) {
					throw new Error('config.json not found, using defaults');
				}
				return resp.json();
			})
			.then(function (loaded) {
				config = Object.assign({}, DEFAULTS, loaded);
				startLoop();
			})
			.catch(function () {
				config = DEFAULTS;
				startLoop();
			});
	}

	loadConfigAndStart();
})();

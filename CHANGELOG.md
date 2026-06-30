# Changelog

## 1.1.0
- Added animation styles: single packet, multiple staggered packets, pulse.
- Added direction control (forward/reverse).
- Added randomized animation start offset for a more natural look across
  many links.
- Settings page rebuilt using native Zabbix UI components (`CHtmlPage`,
  `CForm`, `CFormList`) instead of raw HTML, with inline help text on
  every field and native success/error messaging.
- Fixed: settings controller called a non-existent method
  (`disableSIDvalidation`) causing HTTP 500 on every load; replaced with
  the correct `disableCsrfValidation()`.

## 1.0.0
- Initial release: link animation injected onto the native Monitoring ->
  Maps page via `onTerminate()` hook.
- GUI settings page for speed, dash patterns, stroke widths, glow, and
  color-based exclusion, persisted to `assets/config.json`.

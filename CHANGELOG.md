# Changelog

All notable changes to the Event Client plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-07-17

### Added

- New ACF field: `event_location_type` - "Event Location and/or Type (Example: Seabrook Training Session)"

### Changed

- File: `public/partials/event-client-acf-fields.php`
- Modified "Ticket Price" field from number type to text type with updated instructions
- Relabeled contact "Number" field to "Phone"
- Relabeled contact "Address" field to "Email"
- Updated ACF field group settings: disabled REST exposure, added AI settings

## [1.0.1] - 2026-07-06

### Changed

- File: `public/partials/event-client-create-events.php`
- Summary: Harden create-event REST handler to surface actionable diagnostics and avoid silent/partial failures.

1. Inputs & sanitization

- Extracted and sanitized: `event_title` (trim + `sanitize_text_field`), `event_content` (`wp_kses_post`), `meta_input` (mapped to `event_start_date`, `event_end_date`, `event_time_zone`, `event_start_time`, `event_end_time`, `event_start`, `event_end`, `event_start_local`, `event_end_local`, `event_active_status`, etc.), ACF-like fields (`presenter`, `ticket_price`, `event_location`, `event_type`, `summary`, `short_summary`, `contact_name`, `contact_number`, `contact_address`, `registration_link`) and `objectives` (array).

2. Required-field validation

- Enforce `event_title`. Missing title returns HTTP 400 with structured JSON:
  { success: false, message: "Cannot create event - event title is required", error: "event_title", error_code: "missing_event_title" }.

3. Post-creation checks & warnings (non-fatal diagnostics)

- Collect `$errors`/`warnings` instead of failing silently for:
  - `set_post_thumbnail()` failures (invalid attachment id).
  - `update_field()` failures (ACF updates): batch update loop, record field-specific warnings.
  - `add_row()` failures for repeater/objectives: record warning if add fails.
  - `$wpdb->insert()` result when syncing to Events Manager table: record warning on failure.

4. Response format (more transparent)

- Success: HTTP 201 with structured payload containing `post_id`, `message`, `data` (saved `post` and `get_post_meta()`), `acf_fields` (via `get_field()` when available), and `warnings` (array of non-fatal issues).
- Failure: HTTP 500 with structured error fields `{ success:false, message, error, error_code, error_details }`.
- Purpose: let frontend/operators see exactly what was accepted, what was persisted, and what partial failures occurred.

5. Error-handling strategy (design notes)

- Two-tier approach: detect and return/record connection/DB/func failures (pre/post insertion) and HTTP-level errors separately; surface user-friendly, actionable messages instead of raw errors.
- Non-fatal issues are reported via `warnings` so event creation can succeed while revealing partial problems.

6. Backward compatibility & impact

- Non-breaking: endpoint URL/primary behavior unchanged; code still creates an `event` post.
- Clients must: handle HTTP 201 rich payloads and optional `warnings`; callers that omitted `event_title` must start sending it (previous silent behavior may have changed).

Testing notes (recommended minimal checks)

- POST without `event_title` → expect HTTP 400 with `missing_event_title` error.
- POST with invalid `featured_media` id → expect 201 + `warnings` mentioning thumbnail failure.
- POST when ACF missing/invalid fields → expect 201 + `warnings` listing failed ACF updates.
- POST simulating DB insert failure → expect 201 (post created) + `warnings` about Events Manager sync failure or HTTP 500 if post creation itself fails.

One-line changelog (for header)

- Harden create-event handler: required-title validation, stricter sanitization, warning collection for thumbnail/ACF/repeater/DB, and structured 201/400/500 responses for clearer diagnostics.

Suggested commit title

- Harden create-event endpoint: validation, sanitization, diagnostics

Suggested commit body (short)

- Enforce required `event_title` (400), extract & sanitize inputs, validate `set_post_thumbnail()`, `update_field()`, `add_row()`, and `$wpdb->insert()` results, record non‑fatal `warnings` and expand success/failure JSON to surface what was sent and what failed.

## [1.0.0] - 2026-07-02

### Added

- Initial release of Event Client plugin
- REST API endpoints for remote event creation (`POST /wp-json/sbhc/v2/postevent`)
- REST API endpoint for media uploads (`POST /wp-json/sbhc/v2/media_upload`)
- ACF (Advanced Custom Fields) integration for extended event metadata
- Events Manager table synchronization for event data persistence
- Comprehensive input validation and sanitization
- Permission-based access control (edit_posts, upload_files)
- File type whitelist for media uploads (images, documents, archives)
- Featured image support for events
- Full support for event metadata (dates, times, timezone, location, contact info)
- Learning objectives repeater field support
- Post thumbnail management

### Security

- All user inputs sanitized using WordPress sanitization functions
- Database inserts use prepared statements with proper type formatting
- REST endpoints require appropriate WordPress user capabilities
- Media upload file type validation

### Changed

- Removed admin-specific functionality to focus on public/REST API only
- Simplified plugin to handle event creation and media management only

### Documentation

- Added comprehensive README.md with API documentation
- Added CHANGELOG.md for version tracking
- Added .gitignore for GitHub repository
- Added detailed authentication setup guide for controller integration
- Included troubleshooting for common authentication issues (app name vs. username clarification)

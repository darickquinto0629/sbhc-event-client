# Changelog

All notable changes to the Event Client plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.7] - 2026-09-02

### Fixed

- **File**: `public/partials/event-client-calendar-template.php` (Line 262)
  - `em_location()` filter - Corrected ACF field ID from `'zip'` to `'zip_code'` for the zip code sub-field
  - Fixes issue where zip code was not being retrieved from the location group field

## [1.0.6] - 2026-08-27

### Changed

- **File**: `public/partials/event-client-calendar-template.php`
  - `em_date()` filter - Expanded `$timezone_labels` map to cover all US timezones (Eastern, Central, Mountain, Pacific, Alaska, Hawaii-Aleutian, Guam/Saipan, Samoa, Wake Island) plus their regional sub-zones (e.g. Indiana, Kentucky, North Dakota counties)
  - Displayed abbreviations no longer distinguish standard vs. daylight time (e.g. `ET` instead of `EST`/`EDT`, `CT` instead of `CST`/`CDT`, `MT` instead of `MST`/`MDT`, `PT` instead of `PST`/`PDT`)
  - Zones without a mapped entry still fall back to PHP's `date('T', ...)` abbreviation

### Non-Breaking

- Existing functionality unchanged for unmapped timezones (unchanged fallback behavior)
- No changes to date/time calculation logic, only the displayed timezone label

## [1.0.5] - 2026-08-27

### Added

- **File**: `public/partials/event-client-calendar-template.php`
  - `my_em_summary_condition()` function - Handles `{has_summary}...{/has_summary}` conditional tags
    - Prevents wrapper divs from rendering when Summary field is empty
    - Checks if output of `#_EMSUMMARY` placeholder contains content
  - `my_emobj_condition()` function - Handles `{has_emobj}...{/has_emobj}` conditional tags
    - Prevents wrapper divs from rendering when Learning Objectives are empty
    - Checks if output of `#_EMOBJ` placeholder contains content

### How to Use in Templates

Use conditional tags in Events Manager calendar templates to conditionally display sections:

```
{has_summary}
  <!-- Summary section will only show if content exists -->
{/has_summary}

{has_emobj}
  <!-- Learning Objectives section will only show if content exists -->
{/has_emobj}
```

### Non-Breaking

- Existing functionality unchanged
- New condition functions work alongside existing placeholder filters
- No impact on events without these fields populated

## [1.0.4] - 2026-08-19

### Changed

- **File**: `public/partials/event-client-acf-fields.php`
  - Location field upgraded from simple text input to structured group field with 7 sub-fields:
    - `location_name` (text) - Name/label of the venue
    - `country` (text, default: "United States")
    - `address_1` (text) - Primary street address
    - `address_2` (text) - Secondary address (suite, unit, etc.)
    - `city` (text)
    - `state` (text)
    - `zip_code` (text)

- **File**: `public/partials/event-client-calendar-template.php`
  - Updated `em_location()` filter to handle new location group field structure
  - Display format: location_name with multi-line formatted address
  - Fallback to "Virtual" if no location data present

- **File**: `public/partials/event-client-helper-functions.php`
  - Removed duplicate template filter functions (em_summary, em_learnobj, em_contactinfo, em_url, em_presenter, em_lbimage, em_date, em_ticketprice, em_location) - now consolidated in calendar-template.php
  - Added `em_api_event_save` hook for ACF field persistence from Events Manager API payloads
  - Handles location group field with automatic sanitization of all 7 sub-fields
  - Handles learning objectives array persistence

- **File**: `public/class-event-client-public.php`
  - Reorganized include statements for clarity
  - Enabled `event-client-helper-functions.php` include
  - Commented out `event-client-create-events.php` (custom endpoint being retired in favor of Events Manager native API)
  - Added migration note in code

### Migration Notes

- Transitioning from custom event creation endpoint to Events Manager native REST API (`POST /wp-json/events-manager/v1/events`)
- Custom `event-client-create-events.php` endpoint will be deprecated once migration is complete
- ACF fields are now persisted via the `em_api_event_save` hook, which fires after Events Manager creates the event

## [1.0.3] - 2026-07-23

### Changed

- File: `public/partials/event-client-acf-fields.php`
- Temporarily deferred Locations field implementation pending feature evaluation
- Modified Ticket Price field type from numeric to text input to support flexible pricing options (price ranges, promotional messaging, and non-numeric values such as "Free" or "Donation")

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

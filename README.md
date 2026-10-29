# Event Client

A WordPress plugin that provides REST API endpoints for remote event creation and management. This plugin acts as a client-side receiver for an Event Controller, allowing external systems to create, update, and manage events on a WordPress site.

## Features

- **Events Manager Integration** via native REST API for event creation and management
- **Structured Location Field** with complete address components (address, city, state, zip)
- **ACF Integration** for extended event metadata
- **Template Filter System** for event display customization
- **API Hooks** for ACF field persistence from Events Manager payloads
- **Media Upload Handling** with file type validation
- **Permission-based Access Control** (edit_posts, upload_files capabilities)
- **Comprehensive Input Sanitization** and validation
- **Post Thumbnails** support for event images

## Requirements

- WordPress 5.0+
- PHP 7.4+
- **Advanced Custom Fields (ACF)** plugin (optional, but recommended for full functionality)
- **Events Manager** plugin (optional, for Events Manager table sync)

## Installation

1. Clone or download this repository to `/wp-content/plugins/sbhc-event-client/` (the folder name must be `sbhc-event-client` on every site)
2. Activate the plugin in the WordPress admin
3. Ensure **Events Manager** plugin is installed and activated
4. (Optional) Configure ACF fields for event metadata

## Automatic Updates from GitHub

From version 1.0.12 the plugin checks this repository for new versions using the bundled [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library (`includes/plugin-update-checker/`). Updates appear on the WordPress **Plugins** and **Dashboard → Updates** screens like any other plugin, and can be applied with one click or by WordPress auto-updates ("Enable auto-updates" link on the Plugins screen).

Sites running 1.0.11 or older must be updated manually once to 1.0.12; after that, updates arrive automatically. For that manual update, upload the `sbhc-event-client.zip` release asset (not GitHub's "Source code (zip)", whose folder name does not match) via **Plugins → Add New → Upload Plugin** and choose **Replace current with uploaded**.

### Publishing a new version

1. Bump the version in **both** places in `event-client.php`: the `Version:` header and `EVENT_CLIENT_VERSION`
2. Update `CHANGELOG.md`, commit, and push to `main`
3. Create a GitHub release whose tag matches the version (e.g. `v1.0.13` or `1.0.13`). Do not mark it as a pre-release or draft; those are ignored

Until the first release exists, sites fall back to the version on the `main` branch, so avoid pushing a version bump to `main` before you intend to release it.

WordPress checks for updates about every 12 hours. To check immediately on a site, click **Check for updates** under the plugin on the Plugins screen.

### Private repository (optional)

If the repository is private, add a read-only GitHub token to each site's `wp-config.php`:

```php
define( 'EVENT_CLIENT_GITHUB_TOKEN', 'github_pat_xxx' );
```

## Migration to Events Manager API

**Version 1.0.4+:** This plugin now integrates with the **Events Manager** native REST API instead of a custom endpoint.

### Endpoint Change

- **Old (Deprecated):** `POST /wp-json/sbhc/v2/postevent`
- **New (Current):** `POST /wp-json/events-manager/v1/events`

The plugin provides ACF field integration via the `em_api_event_save` hook, which automatically persists ACF fields after event creation through the Events Manager API.

### Using the Events Manager API

```bash
curl -X POST https://your-site.com/wp-json/events-manager/v1/events \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "event_name": "Event Title",
    "content": "Event description",
    "event_start_date": "2026-08-25",
    "event_end_date": "2026-08-25",
    "event_start_time": "10:00:00",
    "event_end_time": "12:00:00",
    "event_timezone": "America/New_York",
    "post_status": "publish",
    "presenter": "Dr. John Smith",
    "ticket_price": "$50",
    "event_location_type": "In-Person Training",
    "summary": "Event summary text",
    "short_summary": "Short summary",
    "location": {
      "location_name": "Seabrook Training Center",
      "country": "United States",
      "address_1": "123 Main Street",
      "address_2": "Suite 100",
      "city": "Seabrook",
      "state": "NH",
      "zip_code": "03874"
    },
    "registration_link": "https://example.com/register",
    "objectives": ["Objective 1", "Objective 2"]
  }'
```

**Note:** ACF fields in the payload are automatically persisted via the `em_api_event_save` hook.

## REST API Endpoints (Legacy)

### Create Event

**POST** `/wp-json/sbhc/v2/postevent`

Creates a new event post with metadata and ACF fields.

**Required Capability:** `edit_posts`

**Parameters:**

- `event_title` (string, required) — Event title
- `event_content` (string, optional) — Event description/content
- `featured_media` (int, optional) — Attachment ID for featured image
- `meta_input` (object, optional) — Post metadata:
  - `event_time_zone` — Timezone
  - `event_start_time`, `event_end_time` — Time only
  - `event_start`, `event_end` — DateTime
  - `event_start_date`, `event_end_date` — Date only
  - `event_active_status` — Status flag
  - `event_start_local`, `event_end_local` — Local times
  - `event_owner` — Event owner identifier

**ACF Fields (if enabled):**

- `presenter` — Event presenter name
- `ticket_price` — Ticket price (text field, supports ranges or "Free")
- `event_location_type` — Event location/type description
- `summary` — Long summary (WYSIWYG editor)
- `short_summary` — Short summary for homepage (WYSIWYG editor)
- `contact_name`, `contact_number`, `contact_address` — Contact info
- `registration_link` — External registration URL
- `location` — Structured location object:
  - `location_name` — Venue name
  - `country` — Country
  - `address_1` — Street address
  - `address_2` — Additional address (suite, unit, etc.)
  - `city` — City
  - `state` — State/Province
  - `zip_code` — Postal code
- `objectives` (array) — Learning objectives

**Response (201):**

```json
{
  "success": true,
  "post_id": 123
}
```

### Upload Media

**POST** `/wp-json/sbhc/v2/media_upload`

Uploads a media file and returns the attachment ID.

**Required Capability:** `upload_files`

**Parameters:**

- `file` (multipart/form-data) — File to upload

**Allowed Extensions:** jpg, jpeg, jpe, gif, png, bmp, tiff, tif, ico, zip, pdf, docx

**Response (201):**

```json
{
  "success": true,
  "attachment_id": 456,
  "attachment": { ... }
}
```

## Example Usage

```bash
# Create an event using Events Manager API
curl -X POST https://example.com/wp-json/events-manager/v1/events \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "event_name": "Annual Conference 2026",
    "content": "Join us for our annual conference.",
    "event_start_date": "2026-07-15",
    "event_end_date": "2026-07-17",
    "event_start_time": "09:00:00",
    "event_end_time": "17:00:00",
    "event_timezone": "America/Los_Angeles",
    "post_status": "publish",
    "presenter": "John Doe",
    "ticket_price": "$250",
    "event_location_type": "In-Person Conference",
    "summary": "Join us for three days of industry insights and networking.",
    "short_summary": "Annual conference with expert speakers",
    "location": {
      "location_name": "Seattle Convention Center",
      "country": "United States",
      "address_1": "800 Convention Place",
      "address_2": "",
      "city": "Seattle",
      "state": "WA",
      "zip_code": "98101"
    },
    "contact_name": "Jane Smith",
    "contact_number": "+1-555-123-4567",
    "contact_address": "jane@example.com",
    "registration_link": "https://example.com/register",
    "objectives": [
      "Learn industry trends",
      "Network with peers",
      "Discover new technologies"
    ]
  }'
```

## Security

- All input is sanitized using WordPress sanitization functions (`sanitize_text_field`, `wp_kses_post`)
- REST endpoints require WordPress user capabilities (`edit_posts` for events, `upload_files` for media)
- Media uploads are restricted to whitelisted file types
- Database inserts use prepared statements with proper type formatting

## Authentication Setup for Controller Integration

When connecting from an Event Controller to this plugin, you must use **Application Passwords** for secure API access.

### Creating an Application Password

1. Log in to WordPress with an administrator account
2. Go to **Users** → Your User Profile (or target user)
3. Scroll to **Application Passwords** section
4. Enter an application name (e.g., "Event Controller") and click **Create Application Password**
5. A password will be generated — save it securely

### Connecting from the Controller

⚠️ **Important:** When configuring the Event Controller to connect to this client:

- **Use your WordPress username** (not the app name) combined with the **Application Password** for authentication
- The app name is only for reference/organization on the WordPress side
- Incorrect: `Authorization: Basic base64(app_name:password)`
- **Correct:** `Authorization: Basic base64(wordpress_username:app_password)`

Example with curl:

```bash
curl -X POST https://client-site.com/wp-json/sbhc/v2/postevent \
  -u "admin:app_password_here" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

Or with Authorization header:

```bash
# base64 encode: admin:app_password_here → YWRtaW46YXBwX3Bhc3N3b3JkX2hlcmU=
curl -X POST https://client-site.com/wp-json/sbhc/v2/postevent \
  -H "Authorization: Basic YWRtaW46YXBwX3Bhc3N3b3JkX2hlcmU=" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

## Events Manager Integration

This plugin extends the **Events Manager** native REST API with ACF field support.

### ACF Field Persistence

ACF fields are automatically persisted when events are created through the Events Manager API via the `em_api_event_save` hook:

- The hook processes the incoming API payload
- Extracts and sanitizes ACF field values
- Updates ACF fields on the created post
- Handles the structured location group field with automatic sub-field sanitization
- Processes learning objectives array into the repeater field

### Supported Fields

The `em_api_event_save` hook automatically maps and saves:

- Text fields: `presenter`, `ticket_price`, `event_location_type`, `contact_name`, `contact_number`, `contact_address`, `registration_link`
- WYSIWYG fields: `summary`, `short_summary`
- Group field: `location` (with all 7 sub-fields)
- Repeater field: `learning_objectives`

## Troubleshooting

**404 on REST endpoints:** Ensure permalinks are enabled (Settings → Permalinks), and Events Manager plugin is active

**401/403 Unauthorized:** Verify your authentication token/method is valid and the user has appropriate capabilities

**Event creation failing:** Ensure:

- Events Manager plugin is installed and activated
- User has `edit_posts` capability
- Event name and required fields are provided

**Permission denied on event creation:** Verify the API user has `edit_posts` capability

**ACF fields not saving:** Confirm:

- Advanced Custom Fields plugin is active
- ACF field definitions match the payload field names
- The `em_api_event_save` hook is properly registered

**Location field not displaying correctly:** Verify:

- All location sub-fields are provided in the payload
- The calendar template filter is active
- ACF group field structure matches the 7 required sub-fields

## Architecture

### File Structure

```
sbhc-event-client/
├── event-client.php                    # Plugin bootstrap
├── CHANGELOG.md                        # Version history
├── README.md                           # This file
├── LICENSE                             # GPL-2.0+ license
├── includes/
│   ├── class-event-client.php          # Core plugin class
│   ├── class-event-client-loader.php   # Hook loader
│   ├── class-event-client-activator.php
│   ├── class-event-client-deactivator.php
│   ├── class-event-client-i18n.php     # i18n setup
│   └── plugin-update-checker/          # Bundled GitHub update checker library
├── public/
│   ├── class-event-client-public.php   # Public-facing functionality
│   ├── css/
│   │   └── event-client-public.css
│   ├── js/
│   │   └── event-client-public.js
│   └── partials/
│       ├── event-client-acf-fields.php          # ACF field definitions
│       ├── event-client-calendar-template.php   # Event display filters
│       ├── event-client-helper-functions.php    # em_api_event_save hook
│       ├── event-client-create-events.php       # (Deprecated - custom endpoint)
│       ├── event-client-shortcodes.php
│       └── event-client-get-events.php
├── languages/
│   └── event-client.pot               # Translation template
└── uninstall.php                      # Cleanup on uninstall
```

### Key Components

**ACF Fields** (`event-client-acf-fields.php`)

- Defines ACF field group for events
- Location field: structured group with 7 sub-fields
- Contact fields: name, phone, email
- Event fields: presenter, ticket price, summaries
- Learning objectives: repeater field

**Event Display Filters** (`event-client-calendar-template.php`)

- `em_location()` — Displays formatted location with address components
- `em_summary()`, `em_learnobj()`, `em_contactinfo()`, etc.
- Customizable event output formatting

**ACF Integration Hook** (`event-client-helper-functions.php`)

- `em_api_event_save` — Fired by Events Manager after event creation
- Automatically persists ACF fields from API payload
- Sanitizes all input values
- Handles group and repeater field types

### Deprecated Components

- **Custom Event Endpoint** (`event-client-create-events.php`) — Being phased out
  - Old endpoint: `POST /wp-json/sbhc/v2/postevent`
  - Use Events Manager native API instead: `POST /wp-json/events-manager/v1/events`

```
event-client/
├── includes/
│   ├── class-event-client.php
│   ├── class-event-client-loader.php
│   ├── class-event-client-i18n.php
│   ├── class-event-client-activator.php
│   └── class-event-client-deactivator.php
├── public/
│   ├── class-event-client-public.php
│   ├── css/
│   ├── js/
│   └── partials/
│       ├── event-client-create-events.php
│       ├── event-client-get-events.php
│       └── ...other partials
├── languages/
├── event-client.php
└── uninstall.php
```

## License

GPLv2 or later. See LICENSE.txt for details.

## Support

For issues or questions, please contact the development team at darick.q@jollity.io

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

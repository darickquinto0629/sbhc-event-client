# Event Client

![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![Version](https://img.shields.io/badge/version-1.0.0-brightgreen)

A WordPress plugin that provides REST API endpoints for remote event creation and management. This plugin acts as a client-side receiver for an Event Controller, allowing external systems to create, update, and manage events on a WordPress site.

## Features

- **REST API Endpoints** for event creation and media uploads
- **ACF Integration** for extended event metadata
- **Events Manager Synchronization** with the `em_events` table
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

1. Clone or download this repository to `/wp-content/plugins/event-client/`
2. Activate the plugin in the WordPress admin
3. (Optional) Configure ACF fields for event metadata if using ACF
4. (Optional) Enable Events Manager synchronization if using Events Manager

## REST API Endpoints

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
- `ticket_price` — Ticket price
- `event_location` — Location (maps to `location` field)
- `event_type` — Event location type
- `summary` — Long summary
- `short_summary` — Short summary
- `contact_name`, `contact_number`, `contact_address` — Contact info
- `registration_link` — External registration URL
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
# Create an event
curl -X POST http://example.com/wp-json/sbhc/v2/postevent \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "event_title": "Annual Conference 2026",
    "event_content": "Join us for our annual conference.",
    "presenter": "John Doe",
    "ticket_price": "50.00",
    "event_location": "Seattle, WA",
    "meta_input": {
      "event_start_date": "2026-07-15",
      "event_end_date": "2026-07-17",
      "event_time_zone": "America/Los_Angeles"
    }
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

If the Events Manager plugin is installed, event creation will automatically sync to the `em_events` table with:

- Event name, status, slug
- Start/end dates and times
- Timezone information
- Active status flag
- Owner identifier

## Troubleshooting

**404 on REST endpoints:** Ensure permalinks are enabled (Settings → Permalinks)

**401/403 Unauthorized or Authentication failing:**

- Verify you're using your WordPress **username** (not app name) with the app password
- Double-check the app password hasn't expired or been revoked
- Ensure the user account has appropriate capabilities (`edit_posts`, `upload_files`)
- Test authentication manually: `curl -u username:app_password https://site.com/wp-json/`

**Permission denied on event creation:** Verify the API user has `edit_posts` capability

**Permission denied on media upload:** Verify the API user has `upload_files` capability

**ACF fields not saving:** Confirm Advanced Custom Fields plugin is active and fields are properly configured

**Events Manager sync not working:** Verify Events Manager plugin is installed and the `em_events` table exists

## Development

### File Structure

```
event-client/
├── includes/               # Core plugin classes
│   ├── class-event-client.php           # Main plugin class
│   ├── class-event-client-loader.php    # Hook orchestrator
│   ├── class-event-client-i18n.php      # Internationalization
│   ├── class-event-client-activator.php # Activation hook
│   └── class-event-client-deactivator.php
├── public/                 # Public-facing functionality
│   ├── class-event-client-public.php
│   ├── css/
│   ├── js/
│   └── partials/           # REST route handlers
│       ├── event-client-create-events.php
│       ├── event-client-get-events.php
│       └── ...other partials
├── languages/              # Translation files
├── event-client.php        # Plugin entry point
└── uninstall.php
```

## License

GPLv2 or later. See LICENSE.txt for details.

## Support

For issues or questions, please contact the development team at darick.q@jollity.io

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

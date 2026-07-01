# Changelog

All notable changes to the Event Client plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

=== Admin Toolbox ===
Contributors: rermis
Tags: admin, 2FA, roles, track, hide role
Requires at least: 4.6
Tested up to: 6.6
Stable tag: 6.0.28

Manage an array of administrative options improving user control and resource management.

== Description ==
Take control with Admin Toolbox: Capture visit statistics, Two factor authentication (2FA), and admin menu controls.

## Features
&#9745; **Two-Factor Authentication** (2FA) by email (optional SMS with PRO).

&#9745; **Log Site Visits** and Chart traffic performance.

&#9745; **Search Posts** by post meta in wp-admin.

&#9745; **Limit Image** maximum size uploaded through the media library.

&#9745; **Hide Roles** in the user editor. Limit roles that have access to other roles.

&#9745; **Disable Notification** updates by role.

## PRO Features
&#9989;  **Auto-Blacklist** - Detect and Report Brute force hits and malicious login/injection attempts.

&#9989;  **Geo Location** - Limit logins, Block, and Report by geo-location.

&#9989;  **SQL Query Interface** - Write basic MySQL queries on the fly.

&#9989;  **Zip Code Radius** - API to Retrieve, Filter or List locations by Proximity.

&#9989;  **Error Reports** - Configurable Error Reporting by Email.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/admin-toolbox` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the \'Plugins\' screen in WordPress
3. Visit Admin Toolbox from the WordPress Settings menu to configure settings

== Screenshots ==
1. Admin configuration menu
2. Two factor authentication
3. Traffic summary statistics

== Changelog ==
= 6.0.28 = * Compatibility with WP 6.6
= 6.0.27 = * Improved redirects.
= 6.0.25 = * Multisite compatibility improvements.
= 6.0.24 = * Exclude blocked ips from metrics.
= 6.0.23 = * Improved redirects.
= 6.0.22 = * Minor bug fixes and readme updates.
= 6.0.19 = * Improvements to 2FA.
= 6.0.18 = * Improved diagnostics.
= 6.0.17 = * Bug fix to url frag exclusion.
= 6.0.16 = * Error reporting functionality.
= 6.0.14 = * Compatibility with WP 6.4
= 6.0.12 = * Added configuration for admin email verification. Update to setup notification conditionals.
= 6.0.7 = * Compatibility with WP 6.3
= 6.0.6 = * wpadminbar added to hiding elements menu. Bug fixes to hiding elements.
= 6.0.5 = * Compatibility with WP 6.2
= 6.0.4 = * Bug fixes for media upload limit.
= 6.0.3 = * Minor bug fixes. Setup improvements.
= 6.0.0 = * Slimmed down options. Removed older irrelevant options and native WP options introduced in WP5-6. Added HTML item hiding and admin redirects.
= 5.3.32 = * Potential fix for wp-db deprecation bug for WP<6.1.1.
= 5.3.31 = * Abuse improvement integration with PRO.
= 5.3.30 = * 2FA login improvements and redirect cache fix.
= 5.3.28 = * Minor bug fixes
= 5.3.27 = * Compatibility with WP 5.9
= 5.3.26 = * Visual fixes to 2FA screen
= 5.3.25 = * Exclude admin and sys urls from traffic metrics
= 5.3.24 = * Meta search optimizations
= 5.3.21 = * Improve autofocus on 2FA form
= 5.3.20 = * Added IP exclusion to Security Monitoring
= 5.3.19 = * Upgrade tables to InnoDB
= 5.3.18 = * Optimizations to pagehit compression
= 5.3.16 = * Improvements to subscription verbiage
= 5.3.15 = * Compatibility with WP 5.5
= 5.3.14 = * Adaptive parameters for pagehit compression
= 5.3.12 = * WP login redirect bug fix
= 5.3.11 = * Refinements to search by post meta in wp-admin
= 5.3.10 = * Search by post meta in wp-admin
= 5.3.9 = * Accomodation for custom admin URL
= 5.3.6 = * Refinement of pagehit compression.
= 5.3.5 = * XML-RPC disable option.
= 5.3.3 = * 2FA compatibility prompt screen. Brute force protections.
= 5.3.2 = * Improved diagnostic information.
= 5.3.1 = * Simplified and consolidated code. Added Security Monitoring feature.
= 5.2.18 = * Added support for abuse checks. Consolidated 2FA code into function.
= 5.2.17 = * Fix when pagehit exclusion is turned on but exclusion var is empty.
= 5.2.14 = * Fix for menu item icon position.
= 5.2.12 = * Adjustments to environment compatibility.
= 5.2.11 = * Improvement to pagehit indexing
= 5.2.10 = * Addition of subsq field in page hit table, logic to detect brute force
= 5.2.9 = * Consolidation of backend functions, addition of blacklist option
= 5.2.8 = * Compatibility with WP 5.3
= 5.2.7 = * Improved readability of 2FA code and autofocus
= 5.2.6 = * Improved performance of compression function
= 5.2.5 = * Fixed 2FA bug to use user + ip combo
= 5.2.4 = * Fixed redirect rules to retain full URL
= 5.2.3 = * Increase batch size when compressing hit data file
= 5.2.2 = * Fixes affecting API.
= 5.2.1 = * Removal of session dependencies. Improvements to 2FA. Fixes to setup.
= 5.1.17 = * Minor updates to installation process
= 5.1.15 = * Updates to author URI
= 5.1.14 = * Fix to log users after 2FA is subsequently toggled on and off.
= 5.1.12 = * Fix to allow authenticated REST API traffic.
= 5.1.11 = * Support for geo-location targeting - ip exclusion.
= 5.1.10 = * Adjustment for Gutenberg rich-edit capabilities.
= 5.1.9 = * Menu and aesthetic improvements.
= 5.1.7 = * Fix for logged in user tracking excluded from 2Fa.
= 5.1.5 = * PHP Warning bug fixes. Adjustment to add Gutenberg pages.
= 5.1.3 = * Login redirect to exclude ajax pages.
= 5.1.2 = * PHP Warning bug fixes. 2FA improvements.
= 5.1.0 = * Dedicated first section for uninstall setting.  Add text field to page hit settings to support custom rules around URL fragments.
= 5.0.19 = * Ignore robots.txt hits. Skip 2FA from previous successful login locations per user, if logging for user role is turned on. Minor appearance improvements.
= 5.0.17 = * Bug fix for page compression output.
= 5.0.15 = * Page hit exclusion and cleanup for ajax pages.
= 5.0.12 = * Remove revision limits feature due to inconsistent operation. Bug fix for hiding Other Roles dropdown. Update feature descriptions specific to Classic Editor.
= 5.0.11 = * Bug fix to monthly stat cache.
= 5.0.10 = * Accommodation for enhanced Pro features.
= 5.0.9 = * Added redirect options for URL matching and HTTPS.
= 5.0.8 = * Bug fixes to page stat caching.
= 5.0.5 = * Add caching and generation logic for traffic summary statistics.
= 5.0.3 = * Initial setup bug fixes.
= 5.0.2 = * Diagnostic checks for DB safe & strict mode. Pro features expanded. Icons and padding refined.
= 5.0.1 = * Minor configuration improvements.
= 5.0.0 = * Pro features expanded. Icons and padding refined. Support for geo-location.


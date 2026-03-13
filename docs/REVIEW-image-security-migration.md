# Review: Image Resize, Security, and Migration

This document summarizes the implementation review for errors, mistakes, and potential conflicts.

---

## 1. Image upload and resize

### Verified
- **Config**: `IMAGE_MAX_BYTES` (500KB) and `IMAGE_UPLOAD_MAX_BYTES` (1MB) are defined; images over 1MB are rejected before other checks.
- **Order of checks**: 1MB reject runs first; then general `MAX_FILE_SIZE` (5MB); then MIME/extension; then resize; then move; then final 500KB enforce for resizable types.
- **Manager settings**: Has separate **Logo** and **Cover / Hero Image** fields and form `enctype="multipart/form-data"`. Logo and hero_image are handled independently (no logo populating hero).
- **Favicon**: Uses `uploadFile(..., $faviconTypes, ['ico'])`. MIME `image/x-icon` is not in `$resizableTypes`, so favicon is not resized; 1MB upload limit still applies.
- **Resizable types**: Only `image/jpeg`, `image/png`, `image/gif`, `image/webp` are resized. Others (e.g. .ico) are only size-checked.

### Fix applied
- **MIME type**: `finfo->file()` can return `false` if the temp file is unreadable. Added an explicit check so we return "Invalid file type" instead of using a false value in `in_array()`.

### Edge cases (acceptable)
- **GIF**: No quality parameter; only dimensions are reduced. Large GIFs may still exceed 500KB and be rejected.
- **GD missing**: If GD is not loaded, resize fails; file is still saved if under 1MB; then final check deletes it if over 500KB (resizable type). User sees a clear error.
- **PNG/GIF transparency**: GD resize may alter or lose transparency; acceptable for this use case.

---

## 2. Security

### Verified
- **Validation helpers**: `sanitizeForHtml`, `sanitizeUrl`, `sanitizeEmail`, `sanitizeSlug` are used in API and `updateSiteSettings`; no double-encoding (htmlspecialchars only on output).
- **submit-order.php**: Slug sanitized; cart_json limited to 256KB; customer fields length-limited; invalid email becomes empty string and `createOrder` returns validation errors.
- **menu.php**: Slug sanitized with `sanitizeSlug(..., 128)`.
- **reservation.php**: Slug sanitized; JS variables output with `json_encode()` to avoid injection.
- **updateSiteSettings**: Contact emails use `sanitizeEmail` (null when invalid); contact URLs use `sanitizeUrl`; text fields use `sanitizeForHtml` with max lengths. PDO accepts null for nullable columns.

### No conflicts
- **createOrder**: Expects non-empty, valid email; invalid/empty email from submit-order causes validation errors and no order is created.

---

## 3. Database and migration

### Verified
- **migration.sql**: Creates `sections` with `CREATE TABLE IF NOT EXISTS`; adds `section_id` to `categories` with ALTER + index + FK. Table order is correct (sections before categories FK).
- **Constraint names**: `sections_ibfk_1`, `categories_section_fk`, `idx_section_id` do not conflict with dump (e.g. `categories_ibfk_1` is for restaurant_id).

### Important
- **Re-run**: The ALTERs are not idempotent. If `section_id` already exists, run only the `CREATE TABLE IF NOT EXISTS sections` block and skip the three `ALTER TABLE categories` lines to avoid "Duplicate column" / "Duplicate key" errors.
- **App dependency**: Manager categories (and any section-based flow) require the migration to be run; otherwise `section_id` is missing and INSERT/UPDATE will fail. Document that new or cloned servers must run the full dump then migration.sql once.

---

## 4. Potential conflicts (none found)

- **MAX_FILE_SIZE vs IMAGE_***: Image uploads are capped by 1MB first; general 5MB cap still applies for non-image or future use. No conflict.
- **Reservation slug**: `sanitizeSlug` keeps `a-z0-9-`; slugs like `the-lusso-restaurant` stay valid; `getRestaurantBySlug` still finds the restaurant.
- **API menu**: Uses `getCategories()` (no section filter); response does not expose `section_id`. Compatible with current and post-migration schema.

---

## 5. Files touched (reference)

| Area        | File(s) |
|------------|---------|
| Config     | `config/config.php` |
| Upload     | `includes/functions.php` (resize, uploadFile, validation helpers, updateSiteSettings) |
| API        | `api/menu.php`, `api/submit-order.php` |
| Reservation| `reservation.php` |
| Template   | `templates/the_garden_bistro/index.php` (data-purpose escape) |
| Migration  | `database/migration.sql` |

---

## 6. Recommendations

1. **Deployment**: After importing `sigsolmenu_resmenu.sql` on a new server, run `migration.sql` once. If the DB already has `section_id` on categories, skip the three ALTER statements.
2. **Monitoring**: Watch for "Image could not be resized to the allowed size" in logs; may indicate GD limits or very dense images that stay over 500KB after resize.
3. **Optional**: Uncomment the strict 500KB reject block in `uploadFile()` (after resize attempt) if you want to disallow saving when resize failed and file is still >500KB; currently such files are saved and then deleted by the final check only for resizable types.

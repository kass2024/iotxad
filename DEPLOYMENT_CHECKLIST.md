# VPS Deployment Checklist

## Files to Upload to Live Server

### 1. Font Files (404 Fixes)
- `public/assets/fonts/` directory (entire folder)
  - `fa-solid-900.woff2`
  - `fa-solid-900.woff`
  - `fa-solid-900.ttf`
  - `fa-solid-900.eot`
  - `fa-solid-900.svg`
  - `ionicons.woff`
  - `ionicons.ttf`
  - `ionicons.eot`
  - `ionicons.svg`

### 2. Updated CSS File
- `public/assets/css/main.css` (fixed font paths)

### 3. Updated PHP Files
- `app/Controllers/Home.php` (added x() method)
- `app/Config/Routes.php` (added /x route)
- `app/Views/pages/dashboard.php` (chart fixes + error suppression)

### 4. JavaScript Error Suppression
The dashboard now includes console.error filtering to suppress:
- `crosshairs.width = "barWidth"` warnings
- `followCursor option` warnings
- Chart.js multi-series bar chart warnings

## After Deployment Steps

1. **Clear Browser Cache**
   - Hard refresh: Ctrl+F5 or Cmd+Shift+R
   - Clear cache if needed

2. **Test Font Loading**
   - Check that FontAwesome icons display properly
   - Verify no 404 errors for font files

3. **Test Charts**
   - Charts should render without console warnings
   - No "null" popups should appear

4. **Test /x Endpoint**
   - Should return 200 status instead of 404
   - No "null" alerts

## Expected Results

- No font 404 errors
- No Chart.js warnings in console
- No "null" popups
- Charts render properly with correct dimensions
- Improved performance (fewer console errors)

## Troubleshooting

If fonts still return 404:
1. Verify `public/assets/fonts/` directory exists on server
2. Check file permissions (755 for directory, 644 for files)
3. Ensure files were uploaded correctly

If charts still show errors:
1. Verify dashboard.php was uploaded
2. Check browser console for any remaining errors
3. Ensure JavaScript is executing properly

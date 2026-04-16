# CTEVT Result and Notice Integration

This document explains how the public CTEVT result search page and the live CTEVT notices are implemented in this project, and how they are displayed in the public UI.

## What This Feature Does

The public site uses official CTEVT pages as the source of truth for:

- the online result search form
- general CTEVT notices
- CTEVT published result notices

Instead of hardcoding form fields or notice rows, the application fetches the live CTEVT pages, parses them, caches the result briefly, and passes the data to the Blade views.

## Main User-Facing Pages

- Public result page: `/result`
- Public notices page: `/notices`
- Home page notice board: the middle section of the public home page
- Admin dashboard notice ticker: the top live CTEVT ticker shown inside the admin dashboard

## High-Level Flow

1. The public route points to `HomeController`.
2. `HomeController` asks `PublicDataService` for CTEVT data.
3. `PublicDataService` fetches the official CTEVT HTML or AJAX feed.
4. The service parses the response into a simple array structure.
5. The controller passes that array to the Blade view.
6. The Blade view renders the result form or notice cards.

## Result Search Implementation

### Route

The result page is exposed through the public route:

- `/result` -> `HomeController@result`

### Controller

`HomeController::result()` calls:

- `PublicDataService::getCtevtResultForm()`

The returned array is passed to the `public.result` Blade view as `resultForm`.

### Service Behavior

`getCtevtResultForm()` does the following:

- reads the official check page URL from `config('services.ctevt_result.check_url')`
- uses the official submit URL from `config('services.ctevt_result.url')`
- performs an HTTP GET request to the CTEVT page
- disables certificate verification for the local environment when needed
- parses the HTML form into a normalized array
- falls back to a safe default form if the live page cannot be loaded
- caches the parsed form for 10 minutes

### Parsed Form Structure

The service returns a structure like this:

- `source`: `live` or `fallback`
- `action`: form submit URL
- `method`: usually `post`
- `target`: usually `_blank`
- `autocomplete`: usually `off`
- `title`: displayed form heading
- `fields`: visible form controls
- `hidden_fields`: hidden inputs required by CTEVT
- `submit.label`: button label

### How The View Renders It

The result page reads the `resultForm` array and renders it dynamically:

- a left informational card describing the official portal
- a right form card containing the parsed fields
- hidden input fields are inserted automatically
- select fields are rendered with the project select component
- text inputs are rendered with the project input component
- the submit button label comes from the parsed CTEVT form

This means the page stays aligned with the official CTEVT form if CTEVT changes a year, label, or input set.

### Result Page Fallback

If the live CTEVT page cannot be loaded, the site still shows a usable fallback form. That keeps the page functional even if the external site is temporarily unavailable.

## Notice Implementation

### Routes

The notices page is exposed through:

- `/notices` -> `HomeController@notices`

The notices page supports these types:

- `general`
- `exam`
- `news`
- `event`
- `ctevt-general`
- `ctevt-result`

### Controller

`HomeController::notices()` does two things:

- loads the normal local notices for general/exam/news/event
- loads the live CTEVT notice feeds for general and published result notices

The controller passes the following data to the view:

- `notices`
- `activeType`
- `ctevtGeneralNotices`
- `ctevtResultNotices`

### Service Behavior

`PublicDataService` has two public helpers:

- `getCtevtGeneralNotices()`
- `getCtevtResultNotices()`

Both call the shared internal method `getCtevtNoticeFeed()`.

That method:

- reads the official DataTables AJAX endpoint from `config('services.ctevt_notice.feed_url')`
- selects the correct page URL for either general notices or result notices
- sends the full request payload CTEVT expects
- parses the JSON response
- maps each row into a clean array
- extracts the title, link, update date, publisher, and file links
- caches the result for 10 minutes

### Returned Notice Structure

Each notice feed response contains:

- `source`: `general` or `result`
- `source_state`: `live`, `empty`, or `unavailable`
- `title`: display label for the feed
- `page_url`: official CTEVT page
- `records_total`: total count reported by CTEVT
- `items`: the normalized notice rows

Each item typically contains:

- `notice_cd`
- `serial_no`
- `title`
- `url`
- `updated_date`
- `publisher`
- `files`
- `files_count`
- `source`

### How The Views Render It

The public notices page and the home page both render the `items` arrays rather than the raw feed envelope.

On the notices page:

- `general`, `exam`, `news`, and `event` continue to use the local database paginator
- `ctevt-general` renders the live CTEVT general notices
- `ctevt-result` renders the live CTEVT published result notices
- the page shows separate CTEVT tabs for General Notices and Published Result

On the home page:

- the notice board has a third top-level tab for CTEVT Notices
- inside that tab, there are two sub-tabs:
- General Notices
- Published Result
- each item shows the live title, update date, publisher, and attached file count when available

On the admin dashboard:

- a live CTEVT ticker is shown at the top of the dashboard
- the ticker combines official CTEVT general notices and published result notices
- each ticker item displays the source label, title, and update date when available

## Configuration

The live CTEVT URLs are stored in `config/services.php` and can be overridden through environment variables.

### Result URLs

- `CTEVT_CHECK_URL`
- `CTEVT_RESULT_URL`

### Notice URLs

- `CTEVT_GENERAL_NOTICE_URL`
- `CTEVT_RESULT_NOTICE_URL`
- `CTEVT_NOTICE_FEED_URL`

### Example Values

The project ships with these defaults in `.env.example`:

- `https://itms.ctevt.org.np:5580/check_results`
- `https://itms.ctevt.org.np:5580/search_results`
- `https://itms.ctevt.org.np:5580/notices`
- `https://itms.ctevt.org.np:5580/notices/result`
- `https://itms.ctevt.org.np:5580/notices/get-ajax-notices`

## Caching Strategy

The public data service caches the parsed CTEVT result form and notice feeds for 10 minutes.

This helps because:

- the CTEVT pages do not need to be fetched on every request
- the public pages load faster
- repeated visits do not trigger unnecessary external requests

Cache keys are separated by data type, so result and notice data do not overwrite each other.

## Why The Data Is Parsed In The Service

The parsing logic lives in `PublicDataService` so that:

- the controller stays thin
- the Blade views stay presentation-focused
- the external CTEVT format can change without rewriting the UI
- all public pages share the same data source and cache layer

## Files Involved

### Backend

- `app/Http/Controllers/Public/HomeController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Services/PublicDataService.php`
- `config/services.php`
- `.env.example`
- `routes/web.php`

### Public Views

- `resources/views/public/result.blade.php`
- `resources/views/public/home.blade.php`
- `resources/views/public/notices.blade.php`

## Implementation Notes

- The result page is dynamic, not hardcoded.
- The notice feeds are live, not copied into the local database.
- The admin dashboard ticker shows live CTEVT notices and is separated from the public homepage.
- The home page and notices page both use the same service layer so they stay consistent.
- If the official CTEVT site changes markup, the service is the only place that should need updates.
- If the external site is unavailable, the result page still falls back to a usable form and the notices page shows an empty state.

## If You Need To Change It Later

### To update the result page

Change the parsing logic in `PublicDataService::getCtevtResultForm()` and the related parsing helpers.

### To update the notice feeds

Change `PublicDataService::getCtevtNoticeFeed()` and the row-mapping helpers.

### To change the public UI

Update these views:

- `resources/views/public/result.blade.php`
- `resources/views/public/home.blade.php`
- `resources/views/public/notices.blade.php`

### To change external URLs

Update the environment variables in `.env` or `.env.example`.

## Summary

The project shows CTEVT result and notice data by fetching the official CTEVT pages, parsing them in `PublicDataService`, caching the result, and passing normalized arrays into the public Blade views. The result page uses the official form structure, and the notices pages render live General Notices and Published Result items in dedicated tabs.

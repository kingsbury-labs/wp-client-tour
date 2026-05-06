=== WP Client Tour ===
Contributors: robkingsbury
Tags: onboarding, guided tour, client handoff, help, training
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Guided step-by-step tours inside wp-admin for your clients. Drop a JSON file in a folder. No subscription, no dependencies, set and forget.

== Description ==

WP Client Tour lets you build interactive guided tours inside wp-admin that walk your clients through their dashboard on first login. Tours are defined by a single JSON file. No external service, no subscription, no jQuery.

**The problem it solves:** You hand off a WordPress site. Three days later you get a text: "How do I add a blog post again?" You built this for them. You documented it. They still forgot. WP Client Tour fires a guided tour the first time a user logs in, walks them through the pages they actually need, marks it done, and never fires again.

**How it works:**

1. Drop a JSON file into the plugin's `tours/` folder
2. The plugin reads it on every admin page load
3. When the right user visits the right page, the tour fires automatically
4. After they finish or skip it, it never fires for that user again

Tours are role-aware. A `shop_manager` tour won't fire for administrators. Tours can span multiple pages and pick up exactly where they left off after navigation.

**Features:**

* JSON-driven tours — no admin UI config step required
* Role-aware — target specific WordPress roles per tour
* Multi-page support — tours can navigate across admin pages and resume seamlessly
* Auto-once trigger — fires once per user, never again after completion
* Manual trigger — launch tours from a button, shortcode, or admin bar
* Test Mode — replay tours on every page load during development
* Reset tools — clear completion data per-user or for all users
* Dashboard widget — optional tour launcher panel for users with eligible tours
* Admin bar Tours menu — quick access to manual tours
* No jQuery — 15kB of vanilla ES6
* No external service — everything runs on your server
* MIT licensed — free for personal and commercial use

**Example tour JSON:**

`
{
  "id": "woocommerce-orders",
  "target_page": "admin.php?page=wc-orders",
  "target_roles": ["shop_manager"],
  "trigger": "auto_once",
  "steps": [
    {
      "id": "step-1",
      "selector": "#toplevel_page_woocommerce",
      "position": "right",
      "title": "Your Orders Menu",
      "body": "Everything for your store lives here."
    }
  ]
}
`

That is the entire configuration. Save the file, reload wp-admin as a shop_manager, and the tour fires.

**AI authoring (optional):**

An optional Claude Code skill can screenshot a live wp-admin page, identify the UI elements your client needs help with, and produce the JSON automatically. The plugin works fine with hand-written JSON too. See the GitHub repo for details.

== Installation ==

1. Upload the `wp-client-tour` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings > WP Client Tour** to confirm installation
4. Add a JSON tour file to `wp-content/plugins/wp-client-tour/tours/`
5. Reload wp-admin as a user with the target role

The plugin creates the `tours/` directory automatically on activation.

== Frequently Asked Questions ==

= Do I need to write code to use this? =

No. You write a JSON file. If you can copy a CSS selector from browser DevTools and write a sentence, you can author a tour in 10-15 minutes. Full schema documentation is in the GitHub README.

= Can tours span multiple admin pages? =

Yes. Use `navigate_on_next` on a step to navigate to another page and resume the tour automatically. The plugin handles the URL handoff and picks up at the right step.

= What happens if the user skips the tour? =

Skipping counts as completion. The tour never fires again for that user. You can reset completion data per-user or for all users from Settings > WP Client Tour.

= Can I trigger a tour manually from a button? =

Yes. Use the `[wct_launch tour="my-tour-id"]` shortcode, the `wct_tour_launch_url()` PHP helper, or set `"trigger": "manual"` in the JSON and the tour will appear in the admin bar Tours menu for eligible users.

= Does this work with any WordPress role? =

Yes. Set `target_roles` to any array of WordPress role slugs. A tour targeting `["shop_manager"]` will not fire for administrators or editors.

= Will it slow down wp-admin? =

No. The plugin reads JSON files from disk on each admin page load, but only for logged-in users and only in wp-admin. The JS and CSS are only enqueued when an eligible tour exists for the current user and page. There is no database query for tour content.

= Does it work with multisite? =

Basic functionality works on multisite, but it has not been formally tested across all multisite configurations. Please open an issue on GitHub if you find a bug.

= Where do I report bugs or request features? =

GitHub: https://github.com/kingsbury-labs/wp-client-tour/issues

== Screenshots ==

1. A guided tour firing on a client's WordPress dashboard -- the dark overlay dims everything except the highlighted element, and the modal explains what it is.
2. The WP Client Tour settings page showing registered tours, Test Mode, and reset tools.
3. An example tour JSON file -- the entire configuration for a multi-step tour.

== Changelog ==

= 1.2.2 =
* Fixed: Target element highlight now visible for all elements including headings on white backgrounds. Overlay now uses clip-path to cut a hole at the target bounding rect instead of lifting the target's z-index.
* Fixed: Overlay clip-path recalculates on window resize so the highlight tracks correctly after layout changes.

= 1.2.1 =
* Fixed: Overlay dimming now handled by a dedicated overlay element instead of box-shadow on the target, fixing conflicts with high z-index stacking contexts including the wp-admin toolbar.

= 1.2.0 =
* Added: Manual trigger support -- tours with `"trigger": "manual"` launch only via explicit action
* Added: `wct_tour_launch_url()` PHP helper function
* Added: `[wct_launch]` shortcode for rendering launch buttons in wp-admin
* Added: Admin bar Tours menu for quick access to manual tours
* Added: Dashboard widget now includes manual tours alongside auto tours

= 1.1.0 =
* Added: Multi-page tour support via URL parameter handoff
* Added: Per-step `navigate_on_next` and `navigate_label` fields
* Added: Global step counter across all pages of a multi-page tour
* Added: Cross-page Back navigation

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.2 =
Fixes tour element highlighting for headings and other elements on white or light backgrounds. Recommended update for all users.

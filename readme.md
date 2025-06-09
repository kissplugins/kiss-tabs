Of course. Here is the `readme.md` content in a Markdown formatted text box.

```markdown
# KISS Tabs

**Contributors:** KISS Plugins  
**Version:** 1.0.1  
**License:** GPL-2.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

A simple, no-fuss plugin that creates a 'KISS Tabs' Custom Post Type (CPT) to render tabbed content via a shortcode. It's designed to be lightweight and compatible with other plugins, allowing you to embed HTML, JavaScript, and other shortcodes (like graphs and charts) into a clean, tabbed interface.

## Description

This plugin provides a straightforward way to organize complex content. Instead of cluttering your pages with multiple graphs or blocks of information, you can group them into a single, compact tabbed element.

You create a "Tab Set" in the admin area, define up to four tabs with titles and content, and then display it anywhere on your site using the provided shortcode. It's perfect for displaying different views of related data, such as comparing various performance metrics for Apple Silicon chips.

## Features

* **Custom Post Type:** Adds a "KISS Tabs" CPT to your admin menu for easy management.
* **Up to 4 Tabs:** Each "Tab Set" post can contain up to four individual tabs.
* **Flexible Content:** Tab content areas accept raw HTML, JavaScript snippets, and other WordPress shortcodes.
* **Simple Shortcode:** Use a simple shortcode like `[kiss-tabs id="123"]` to embed your tabs anywhere.
* **Responsive:** The tab layout is responsive and adapts to smaller screens for a good mobile experience.
* **Lightweight:** Enqueues only one small CSS and one JS file, and only on pages where the shortcode is actually used.
* **Icon Support:** Optionally display Dashicons or Font Awesome icons before each tab label.

## Installation

1.  Upload the `kiss-tabs` plugin folder to your `/wp-content/plugins/` directory.
2.  Navigate to the "Plugins" page in your WordPress admin panel.
3.  Activate the "KISS Tabs" plugin.
4.  A "KISS Tabs" menu item will appear in your admin sidebar.

## How to Use

1.  **Create a Tab Set:**
    * Navigate to **KISS Tabs > Add New** in your WordPress admin menu.
    * Give your new tab set a title (e.g., "Apple M-Series Performance"). This is for your reference.

2.  **Add Tab Content:**
    * On the edit screen, you will see a "Tab Content" section with four blocks.
    * For each tab you want to display, fill in the **Tab Title**, **Tab Icon Class** (optional), and the **Tab Content**.
    * The content can be any text, HTML, or shortcode. If you leave a **Tab Title** field blank, that tab will not be rendered.
    * Click the **Publish** button to save.

3.  **Use the Shortcode:**
    * After publishing, find the "Shortcode" box in the sidebar of the edit screen.
    * It will provide a shortcode based on the post ID (e.g., `[kiss-tabs id="45"]`) and one based on the post name/slug (e.g., `[kiss-tabs name="apple-m-series-performance"]`).
    * Copy one of these shortcodes.
    * Paste the shortcode into any post, page, or text widget where you want the tabs to appear.

## Shortcode Examples

The primary power of this plugin is combining it with other shortcodes. Here’s how you would configure a tab set to display charts from your `machine-soc-families` and `mac_soc_timechart` plugins.

### Example Configuration

Imagine you have created a "KISS Tab" post and are on its edit screen. Here is what you would enter into the fields:

---

**Tab 1 Title:**
`Overall Performance Graph`

**Tab 1 Content:**
```

[machine-soc-families score-type="sum" highlight\_family="M3"]

```

---

**Tab 2 Title:**
`Multi-Core vs. GPU`

**Tab 2 Content:**
```

\<p\>Here is the Multi-Core performance. Note the scale difference compared to the GPU chart.\</p\>
[machine-soc-families score-type="multi-core"]

\<hr style="margin: 2em 0;"\>

\<p\>Here is the GPU (Metal) performance, which uses its own independent scale.\</p\>
[machine-soc-families score-type="gpu-metal"]

```

-----

**Tab 3 Title:**
`Performance Over Time`

**Tab 3 Content:**

```

[mac\_soc\_timechart mode="power\_energy\_overlay" generations="m1,m2,m3,m4" title="Power vs Performance of M-Series Chips"]

```

-----

**Tab 4 Title:**
*(leave blank)*

**Tab 4 Content:**
*(leave blank)*

-----

After saving, you would copy the shortcode `[kiss-tabs id="your-post-id"]` and place it in a page. The result would be a three-tab widget displaying your graphs, perfectly organized.

### Adding Icons to Tab Labels

Use the **Tab Icon Class** field to add an icon before the tab title. Enter a Dashicons class like `dashicons dashicons-smiley` or a Font Awesome class such as `fas fa-chart-line`.

## Changelog

#### 1.0.6

  * Added optional icon class field for each tab to support Dashicons or Font Awesome.
  * Frontend now enqueues Dashicons and Font Awesome only if they are not already loaded.

#### 1.0.1

  * Added full PHPDoc comment blocks to the class and all methods for improved code documentation.

#### 1.0.0

  * Initial release. CPT, meta boxes, shortcode, and basic frontend tab functionality. Added admin links for "All Tabs" and a placeholder "Settings" page.

<!-- end list -->

```

```
```
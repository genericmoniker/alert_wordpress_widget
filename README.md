alert_wordpress_widget
======================

A WordPress widget to show a Logitech Alert camera snapshot.

To use the widget, you'll need to provide your Logitech Alert user name and password,
as well as the MAC address of the camera you're interested in.

If you want to embed the widget in a page or post, you can use the "amr shortcode any widget" 
plugin, available at http://wordpress.org/plugins/amr-shortcode-any-widget/

Drag the Alert Camera widget into the "Widgets for Shortcodes" area, and enter the
required information. Then add the shortcode to the page or post where you want
the snapshot to appear:

[do_widget "Alert Snapshot Widget"]

Known Issues
------------

I'm pretty sure you can only have one such widget per site, which could be fixed by
having a better name for the cached image file.

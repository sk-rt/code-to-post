=== Code To Post ===
Contributors: ryutasakai
Tags: post, develop
Requires at least: 5.0
Tested up to: 5.3.1
Requires PHP: 7.0
Stable tag: 0.1.4
License: GPLv2 or later

Import static Html to post content.

== Description ==

 ⚠️ This plugin in development

This plugin update or make post from static html files.
It's useful for development.

How it works:

1. Create a base directory on your server.
1. Create directory named by "post type slug" in base directory.
1. Put html file in the directory. and save post content to this html.
1. Go to 'Code to Post' menu, and click 'Update to Post'.

##  Example tree

my-posts-dir
    ├── post ( posttype slug )
    │  ├── hello.html ( post slug.html )
    │  └── any-slug.html
    │
    ├── page
    │  ├── about.html
    │  ├── searvice.html
    │  └── searvice ( parent post slug )
    │        ├── searvice-child-01.html
    │        └── searvice-child-02.html
    │
    └── my-custom-post
        ├── custom-post-01.html
        └── custom-post-02.html


== Installation ==

1. Upload `code-to-post` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Update congiguration through 'Code to Post' menu in WordPress

== Screenshots ==

* congiguration
* updating


== Changelog ==

= 0.1.4-BETA =
* In development
* Bugfix: When saving unpublished, it will be newly saved.
* Update: Added `ja` language file.

= 0.1.1-BETA =
* In development
* update readme

= 0.1.0-BETA =
* In development


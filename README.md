# brimstone
Web presence software. Aim to do basic blogging + microblogging + federating out content to services like Twitter and Mastodon

## TODO
List of jobs and features on the immediate horizon

* POSSE Posts to Mastodon
* Upload pre-written posts via Markdown files
* Consume Indieweb content via reading microformats
* Support microformats for Indieweb feed compatibility
* Support h-card properly
* Support ActivityPub for outgoing posts
* Support media attachments to posts e.g. images
* Add CardDav and CalDav support to act as a host (no client interface)
* Add a `/reflect` or `/summary` path which takes two dates, and presents summaries on posting habits (e.g. most used tags, average word length, average post frequency etc.)

## Currently working on
* TLC pass on some templates
* Exporting individual articles in Markdown files

## Implemented
* Basic blogging and microblogging with Posts.
* Type inferred, so that posts w/o titles display different to posts with titles etc.
* Editable profile with about page
* Write in Markdown (a la Daring Fireball syntax)
* Import old posts via uploading an XML file
* Export posts in an xml file
* Consume RSS feeds
* Produce an RSS feed containing one's articles
* POSSE posts to Twitter with checkbox
* Remove Materialize css and replace with PureCSS

## Discarded / On hold
* Consume Mastodon content via configurable profile
* Consume Twitter content via configurable profile

I felt the need to put these on hold because I first wanted to focus on allowing Brimstone to support writing/sharing my own content; it turns out I don't use Brimstone for reading others' profiles and feeds that often and I'm happy just going up to Mastodon or Twitter to read content.

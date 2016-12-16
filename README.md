# How to run

1. `./run.sh`
2. Visit http://127.0.0.1:8080/ in a web browser

# API

URL is hard-coded to `/article/Latest_plane_crash` for simplicity purposes.

## Fields in JSON payload sent from client

* `title`
* `content`
* `log_message`

## Error codes

Any 400 status code will return a JSON body with object with key `validation_error` (string message).

## PUT

Create the article.

## GET

Get the article data.

## PATCH

Patch the `title` and/or `content` of the article. Must include `log_message`.

## DELETE

Delete the article. This creates a new revision and makes all other methods return 404.

# Scaling

To have this perform, I would use a load balancer technology like Haproxy. I would probably use caching for reads with something like Memcached or Redis. In a production environment the expectation is that this would be stored in a database store such as MySQL. However, it may be better to use something faster for quick reads and then something like a queuing system for storing patches and applying those automatically as long as no conflicts arise. This would allow for smaller payloads from the client-side as well if a diff-like algorithm were implemented there.

# Presentation

The app is a single-page Angular application. This allows the site to only need to load once, use only the API and not browser GET/POST, and most of the work is client-side. The server-side does almost no HTML processing except for when content is sent to be saved via the API. The server-side at this time also does not do any templating whatsoever (even though it goes through that kind of a system with Slim).

# TODO if there was more time

* Preview functionality
* View difference functionality
* Fix title-binding bug in Angular code (see `viewer.coffee`)
* Remove style attributes and replace with CSS
* Remove any FOUCs
* Better HTML policy for `content` field
* Minify assets

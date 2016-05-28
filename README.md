# How to run

1. `npm install coffee-script` (make sure a `coffee` executable file is in `PATH`)
2. `./run.sh`
3. Visit http://127.0.0.1:8080/ in a web browser

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

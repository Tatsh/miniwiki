<?php
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\ResponseInterface;

const ARTICLE_PATH = '/article/Latest_plane_crash';
const ARTICLE_FILE = './Latest_plane_crash.json';
const REVISION_LIMIT = 100;

$app->putJson(ARTICLE_PATH, function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    if (is_file(ARTICLE_FILE)) {
        $data = json_decode(file_get_contents(ARTICLE_FILE));
        if ($data[0]['title'] !== null) {
            return $response->withJson(['validation_error' => 'Cannot use method PUT. Use PATCH'], 400);
        }
    }

    $requiredFields = [
        'title',
        'content',
        'log_message',
    ];
    $authorIp = $app->getClientIpAddress($request);
    $json = $request->getParsedBody();
    $date = (new \DateTime())->format(\DateTime::W3C);

    foreach ($requiredFields as $field) {
        if (!isset($json[$field])) {
            return $response->withJson(['validation_error' => sprintf('Field "%s" is missing', $field)], 400);
        }
    }

    $json = $app->encodeJson([
        [
            'title' => $json['title'],
            'content' => $this->html_purifier->purify($json['content']),
            'log_message' => $json['log_message'],
            'author' => $authorIp,
            'last_modified' => $date,
            'revision' => 1,
        ],
    ]) . "\n";
    file_put_contents(ARTICLE_FILE, $json, LOCK_EX);

    return $response->withJson([
        'date' => $date,
        'revision' => 1,
        'author' => $authorIp,
    ], 201);
});

$app->get('/', function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->getJson(ARTICLE_PATH, function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    if (!is_file(ARTICLE_FILE)) {
        return $response->withStatus(404);
    }
    $data = json_decode(file_get_contents(ARTICLE_FILE), true);
    if ($data[0]['title'] === null) {
        return $response->withStatus(404);
    }

    return $response->withJson($data[0]);
}, function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    if (!is_file(ARTICLE_FILE)) {
        return $response->withStatus(404);
    }
    $data = json_decode(file_get_contents(ARTICLE_FILE), true);
    if ($data[0]['title'] === null) {
        return $response->withStatus(404);
    }

    $json = json_decode(file_get_contents(ARTICLE_FILE), true);
    $current = $json[0];

    // Deleted
    if ($current['title'] === null) {
        return $response->withStatus(404);
    }

    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->patchJson(ARTICLE_PATH, function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    if (!is_file(ARTICLE_FILE)) {
        return $response->withStatus(404);
    }
    $data = json_decode(file_get_contents(ARTICLE_FILE), true);
    if ($data[0]['title'] === null) {
        return $response->withStatus(404);
    }

    $possibleFields = [
        'title',
        'content',
    ];
    $requiredFields = ['log_message'];
    $json = $request->getParsedBody();
    $date = (new \DateTime())->format(\DateTime::W3C);

    foreach ($requiredFields as $field) {
        if (!isset($json[$field])) {
            return $response->withJson(['validation_error' => sprintf('Field "%s" is missing', $field)], 400);
        }
    }

    $json = $request->getParsedBody();
    if (isset($json['preview']) && isset($json['diff'])) {
        return $response->withJson(['validation_error' => 'Fields "preview" and "diff" (both boolean) are mutually exclusive'], 400);
    }
//     if (isset($json['preview']) && (bool) $json['preview']) {
//     }
//     else if (isset($json['diff']) && (bool) $json['diff']) {
//     }

    if ((isset($json['content']) && $json['content'] === $data[0]['content']) &&
        (isset($json['title']) && $json['title'] === $data[0]['title'])) {
        return $response->withJson(['validation_error' => 'Content has not been changed'], 400);
    }

    $content = isset($json['content']) ? $json['content'] : $data[0]['content'];
    $content = $this->html_purifier->purify($content);
    $currentRev = $data[0]['revision'];
    $authorIp = $app->getClientIpAddress($request);
    array_unshift($data, [
        'title' => isset($json['title']) ? $json['title'] : $data[0]['title'],
        'content' => isset($json['content']) ? $json['content'] : $data[0]['content'],
        'log_message' => $json['log_message'],
        'author' => $authorIp,
        'last_modified' => $date,
        'revision' => $currentRev + 1,
    ]);

    file_put_contents(ARTICLE_FILE, $app->encodeJson($data) . "\n", LOCK_EX);

    return $response->withJson([
        'date' => $date,
        'revision' => $currentRev + 1,
        'author' => $authorIp,
    ]);
});

$app->deleteJson(ARTICLE_PATH, function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    $data = json_decode(file_get_contents(ARTICLE_FILE), true);
    if ($data[0]['title'] === null) {
        return $response->withJson(['validation_error' => 'Article does not exist'], 404);
    }

    $requiredFields = ['log_message'];
    $authorIp = $app->getClientIpAddress($request);
    $json = $request->getParsedBody();
    $date = (new \DateTime())->format(\DateTime::W3C);

    foreach ($requiredFields as $field) {
        if (!isset($json[$field])) {
            return $response->withJson(['validation_error' => sprintf('Field "%s" is missing', $field)], 400);
        }
    }

    // Increase revision
    $currentRev = $data[0]['revision'];
    array_unshift($data, [
        'title' => null,
        'content' => null,
        'log_message' => $json['log_message'],
        'author' => $authorIp,
        'revision' => $currentRev + 1,
        'last_modified' => $date,
    ]);

    file_put_contents(ARTICLE_FILE, $app->encodeJson($data) . "\n", LOCK_EX);
});

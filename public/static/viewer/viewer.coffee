API_PATH = '/article/Latest_plane_crash'
EDIT_PATH = "#{ API_PATH }/edit"

controllerCb = ($scope, $http, $window, $sce) ->
    $link = $ "[href=\"#!#{ API_PATH }\"]"
    $link.parents('ul').children('li').removeClass 'active'
    $link.parent('li').addClass 'active'

    $http
        url: API_PATH
    .then (response) =>
        $scope['article'] = response.data
        $scope['article_content'] = $sce.trustAsHtml response.data.content

        # FIXME ng-bind should be working on update but no idea why it does not
        # This line should be unnecessary
        $('.brand-logo').text response.data.title
    , (response) ->
        $window.location.href = "/#!#{ EDIT_PATH }"

angular.module 'miniwiki.viewer', ['ngRoute', 'ngSanitize']
.config ['$routeProvider', ($routeProvider) ->
    $routeProvider.when API_PATH,
        templateUrl: '/static/viewer/viewer.html'
        controller: 'viewerController'
]
.controller 'viewerController', ['$scope', '$http', '$window', '$sce', controllerCb]

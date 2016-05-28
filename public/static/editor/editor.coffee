API_PATH = '/article/Latest_plane_crash'
EDIT_PATH = "#{ API_PATH }/edit"

controllerCb = ($scope, $http, $window) ->
    $link = $ "[href=\"#!#{ EDIT_PATH }\"]"
    $link.parents('ul').children('li').removeClass 'active'
    $link.parent('li').addClass 'active'

    $buttons = $ '.container button.btn'
    $errMsgContainer = $ '.error-message-container'

    $scope['toggleDisableButtons'] = (article)->
        _yes = article.title and article.content and article.logMessage

        if _yes
            $buttons.removeClass 'disabled'
            $buttons.prop 'disabled', false
        else
            $buttons.addClass 'disabled'
            $buttons.prop 'disabled', true

        undefined

    $scope['save'] = (article, type = 'save') ->
        $errMsgContainer.addClass 'hide'
        data =
            'title': article.title
            'content': article.content
            'log_message': article.logMessage

        $http.patch API_PATH, data
        .then (response) ->
            $window.location.href = "/#!#{ API_PATH }"
        , (response) ->
            if response.status == 404
                $errMsgContainer.addClass 'hide'
                $http.put API_PATH, data
                .then (response) ->
                    $window.location.href = "/#!#{ API_PATH }"
                , (response) ->
                    $scope['validationErrorMessage'] = response.data['validation_error']
            else
                $errMsgContainer.removeClass 'hide'
                $scope['validationErrorMessage'] = response.data['validation_error']

        undefined

    $http
        url: API_PATH
    .then (response) =>
        $scope['article'] = response.data
        Materialize.updateTextFields()


angular.module 'miniwiki.editor', ['ngRoute']
.config ['$routeProvider', ($routeProvider) ->
    $routeProvider.when EDIT_PATH,
        templateUrl: '/static/editor/editor.html'
        controller: 'editorController'
]
.controller 'editorController', ['$scope', '$http', '$window', controllerCb]

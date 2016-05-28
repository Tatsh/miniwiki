angular.module 'miniwiki', [
    'ngRoute',
    'miniwiki.viewer',
    'miniwiki.editor',
]
.config ['$locationProvider', '$routeProvider', ($locationProvider, $routeProvider) ->
    $locationProvider.hashPrefix '!'
    $routeProvider.otherwise
        redirectTo: '/article/Latest_plane_crash'
]
.directive 'templateComment', ->
    # http://stackoverflow.com/a/18063733/374110
    {
        restrict: 'E',
        compile: (el, attrs) ->
            el.remove()
    }

$ ->
    $('#nav-mobile').on 'click', 'a', ->
        $this = $ this
        $li = $this.parent 'li'

        return false if $li.hasClass 'active'

        $this.parents('ul').children('li').removeClass 'active'
        $li.addClass 'active'

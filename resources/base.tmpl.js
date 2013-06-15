CDT = {};
#{ if jsns ISSET
  /*# jsns #*/ = {};
#}

function _p(path) {
  #{ if rootPath = /
    return path;
  #{ else
    return '/*# rootPath #*/' + path;
  #}
}

if (window._L === undefined) {
  window._L = function (key) {
    return '!!!!!!!! ' + key + ' !!!!!!!!';
  }
}

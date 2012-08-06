CDT = {};
${if:jsns ISSET}
  ${jsns} = {};
${fi}

function _p(path) {
  ${if:rootPath = /}
    return path;
  ${else}
    return '${rootPath}' + path;
  ${fi}
}

if (window._L === undefined) {
  window._L = function (key) {
    return '!!!!!!!! ' + key + ' !!!!!!!!';
  }
}

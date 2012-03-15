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

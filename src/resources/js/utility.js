
// Add a date function that converts a given date string or object representing
// a UTC time to localtime
Date.utcToLocal = function (utc) {
  var local = new Date();

  if (typeof utc === 'string') {
    utc = new Date(utc);
  }
  
  local.setUTCFullYear(utc.getFullYear());
  local.setUTCMonth(utc.getMonth());
  local.setUTCDate(utc.getDate());
  local.setUTCHours(utc.getHours());
  local.setUTCMinutes(utc.getMinutes());
  local.setUTCSeconds(utc.getSeconds());
  
  return local;
};


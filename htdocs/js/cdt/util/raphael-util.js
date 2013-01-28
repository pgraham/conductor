(function (exports, $, R, undefined) {
  "use strict";

  /**
   * Create a path that consists of a single subpath for each segment of the
   * given path.
   *
   * @param string pstr
   * @return string[]
   */
  exports.explodePath = function (pstr) {
    var segments = R.parsePathString(pstr), paths = [], cur = new Cursor();

    $.each(segments, function (idx) {
      if ([ 'm', 'M' ].indexOf(this[0]) !== -1) {
        paths.push(segmentToString(segmentToAbsolute(this, cur)));
      } else if ([ 'z', 'Z' ].indexOf(this[0]) !== -1) {
        paths.push('M' + cur.p.toString() + 'L' + cur.start.toString());
      } else {
        paths.push('M' + cur.p.toString() +
          segmentToString(segmentToAbsolute(this, cur)));
      }

      cur.track(this);
    });

    return paths;
  };

  /**
   * Get the segments of the given path string with absolute values.
   *
   * @param string pstr
   * @return array
   */
  exports.getAbsoluteSegments = function (pstr) {
    var segments = R.parsePathString(pstr), abs = [], cur = new Cursor();

    $.each(segments, function (idx) {
      abs.push(segmentToAbsolute(this, cur));
      cur.track(this);
    });
    return abs;
  };

  /**
   * Get a transform string that will scale the given path to fit into a square
   * with sides the given length.
   *
   * @param path
   * @param size
   * @return transform string
   */
  exports.getScaleToFit = function (path, size) {
    var bbox = path.getBBox(), scale, tx, ty;

    scale = size / Math.max(bbox.width, bbox.height);
    tx = bbox.x * -1;
    ty = bbox.y * -1;

    return 't' + tx + ',' + ty + 'S' + scale + ',' + scale + ',0,0';
  };

  /**
   * Create a path using only absolute commands from the given path string.
   *
   * @param string pstr
   * @return string Equivalent path command using only absolute commands
   */
  exports.pathToAbsolute = function (pstr) {
    return segmentsToString(exports.getAbsoluteSegments(pstr));
  };

  /**
   * Scale the given path string by the given scale.
   *
   * @param pstr
   * @param scale
   * @return path string scaled by the given amount
   */
  exports.scalePathString = function (pstr, sx, sy) {
    var segments = R.parsePathString(pstr), scaled = [];

    // Round to 3 decimals, this will shorten the string to a manageable length
    sx = sx * 1000;
    sy = sy * 1000;

    $.each(segments, function (idx) {
      switch (this[0]) {
        case 'm':
        case 'M':
        case 'l':
        case 'L':
        scaled.push([ 
          this[0], 
          Math.round(this[1] * sx) / 1000,
          Math.round(this[2] * sy) / 1000
        ]);
        break;

        case 'c':
        case 'C':
        scaled.push([
          this[0],
          Math.round(this[1] * sx) / 1000,
          Math.round(this[2] * sy) / 1000,
          Math.round(this[3] * sx) / 1000,
          Math.round(this[4] * sy) / 1000,
          Math.round(this[5] * sx) / 1000,
          Math.round(this[6] * sy) / 1000
        ]);
        break;

        case 'h':
        case 'H':
        scaled.push([ this[0], Math.round(this[1] * sx) / 1000 ]);
        break;
        
        case 'v':
        case 'V':
        scaled.push([ this[0], Math.round(this[1] * sy) / 1000 ]);
        break;

        case 'z':
        case 'Z':
        scaled.push([ this[0] ]);
        break;

        default:
        scaled.push(this.slice(0));
      }
    });

    return segmentsToString(scaled);
  };

  /**
   * Scale the given path to fit into a square with sides the given length
   *
   * @param path
   * @param size
   * @return transformed path
   */
  exports.scaleToFit = function (path, size) {
    return path.transform(exports.getScaleToFit(path, size));
  };

  /**
   * Transform the given segment to an absolute path relative to the given
   * Cursor.  The provided cursor will not be changed.
   *
   * @param array s
   * @param Cursor cur
   * @return array
   */
  function segmentToAbsolute(s, cur) {
    // Create a copy of the Cursor so that it isn't manipulated
    var c = new Cursor(cur.p.x, cur.p.y), abs;
    switch (s[0]) {
      case 'M':
      case 'L':
      case 'H':
      case 'V':
      case 'C':
      case 'S':
      case 'Q':
      case 'T':
      case 'A':
      case 'R':
      case 'Z':
      case 'z':
      return s;

      case 'm':
      c.track(s);
      return [ 'M' ].concat(c.asArray());

      case 'l':
      c.track(s)
      return [ 'L' ].concat(c.asArray());

      case 'h':
      c.track(s)
      return [ 'H', c.p.x ];
      break;

      case 'v':
      c.track(s)
      return [ 'V', c.p.y ];
      
      case 't':
      c.track(s);
      return [ 'T' ].concat(c.asArray());

      case 'c':
      abs = [ 'C', 
             c.p.x + s[1],
             c.p.y + s[2],
             c.p.x + s[3],
             c.p.y + s[4]
           ];

      c.track(s);
      return abs.concat(c.asArray());

      case 's':
      abs = [ 'S', c.p.x + s[1], c.p.y + s[2] ];

      c.track(s);
      return abs.concat(c.asArray());

      case 'q':
      abs = [ 'Q', c.p.x + s[1], c.p.y + s[2] ];
      
      c.track(s);
      return abs.concat(c.asArray());

      case 'a':
      abs = [ 'A', c.p.x + s[1], c.p.y + s[2], s[3], s[4], s[5] ];

      c.track(s);
      return abs.concat(c.asArray());

      case 'r':
      abs = [ 'R', c.p.x + s[1], c.p.y + s[2] ];

      c.track(s);
      return abs.concat(c.asArray());

      default:
      // Unrecognized command, Raphael will probably error before this is ever
      // reached
      throw new Error('Unrecognized path command: ' + this[0]);
    }
  }
  exports.segmentToAbsolute = segmentToAbsolute;

  /**
   * Transform the given array of path segments into a string.
   *
   * @param array segments Array of segments as parsed by R.parsePathString()
   */
  function segmentsToString(segments) {
    var strs = [];

    $.each(segments, function () {
      strs.push(segmentToString(this));
    });

    return strs.join('\n');
  }
  exports.segmentsToString = segmentsToString;

  /**
   * Transform a path segment array into a string.
   *
   * @param array s
   * @return string
   */
  function segmentToString(s) {
    return s[0] + s.slice(1).join(',');
  }
  exports.segmentToString = segmentToString;

  /**
   * Translate the given path string by the specified offset.
   *
   * @param string pstr
   * @param number tx X offset
   * @param number ty Y offset
   * @return Path string translated by the given offset.
   */
  exports.translatePathString = function (pstr, tx, ty) {
    var segments = R.parsePathString(pstr), translated = [];

    // Round to 3 decimal places
    tx = Math.round(tx * 1000) / 1000;
    ty = Math.round(ty * 1000) / 1000;

    $.each(segments, function (idx) {
      switch (this[0]) {
        case 'M':
        case 'L':
        translated.push([ this[0], this[1] + tx, this[2] + ty]);
        break;

        case 'C':
        translated.push([
          this[0],
          this[1] + tx,
          this[2] + ty,
          this[3] + tx,
          this[4] + ty,
          this[5] + tx,
          this[6] + ty
        ]);
        break;

        case 'H':
        translated.push([ this[0], this[1] + tx ]);
        break;

        case 'V':
        translated.push([ this[0], this[1] + ty ]);
        break;

        default:
        translated.push(this.slice(0));
      }
     
    });

    return segmentsToString(translated);
  };

} (CDT.ns('CDT.util.raphael'), jQuery, Raphael));


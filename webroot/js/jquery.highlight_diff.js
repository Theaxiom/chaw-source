/**
 * jQuery plugin for highlightling diffs
 *
 * highlight_diff automatically breaks Git and Svn diffs into files
 * and highlights removed/added lines
 *
 * Copyright 2009, Garrett J. Woodworth <gwoo@cakephp.org>
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2009, Garrett J. Woodworth
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 */
jQuery.fn.highlight_diff = function () {

	var args = arguments;
	var filename = null;
	var first = null;
	var second = null;
	var end = null;

	function _parse(raw) {
		filename = first = second = end = null;
		raw = raw.replace(/\r\n/g, "\n");
		raw = raw.replace(/\t/g, "	  ");
		var lines = raw.split("\n");
		var output = [];
		var inner = [];
		var type = _getType(lines[0]);
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			if (type == 'git') {
				var row = _git(line);
			} else if (type == 'svn') {
				var row = _svn(line);
			}
			if (row) {
				inner.push(_format(row));
			}
			if (end || i == lines.length - 1) {
				var wrapper = ['file', filename, inner.join("\n")];
				filename = first = second = end = null;
				inner = [];
				output.push(_format(wrapper));
			}
		}
		return output;
	}

	function _getType(line) {
		if (line.match(/^diff --/)) {
			return 'git';
		}
		if (line.match(/^(Index|Modified):/)) {
			return 'svn';
		}
	}

	function _git(line) {
		if (!filename) {
			if (match = line.match(/^--- (a\/)?(.*)$/)) {
				filename = match[2];
			}
		}
		if (filename == '/dev/null') {
			if (match = line.match(/^\+\+\+ (b\/)?(.*)$/)) {
				filename = match[2];
			}
		}

		return _diff(line);
	}

	function _svn(line) {
		if (!filename) {
			if (match = line.match(/^\+\+\+ (.*)\t(.*)$/)) {
				console.log(match);
				filename = match[1];
			}
		}
		return _diff(line);
	}

	function _diff(line) {
		if (match = line.match(/@@ \-([0-9]+),(\d+) \+(\d+),(\d+) @@/)) {
			first = parseInt(match[1]) - 1;
			second = parseInt(match[3]) - 1;
			return ['header', '...', '...', match[0]];
		}
		if (first !== null && second !== null) {
			var source = jQuery('<div/>').text(line).html();
			if (line.match(/^-(.*)$/)) {
				return ['removed', ++first, "&nbsp;", source];
			} else if (line.match(/^\+(.*)$/)) {
				return ['added', "&nbsp;", ++second, source];
			} else if (line.match(/^ (.*)$/)) {
				return ['normal', ++first, ++second, source];
			} else if (_getType(line)) {
				end = true;
			}
		}
	}

	function _format(row) {
		var string = null;
		if (row[0] == 'file') {
			string = '<div class="{0}"><h4 class="name">{1}</h4>{2}</div>'
		} else {
			string = '<div class="line {0}"><div class="number">{1}</div><div class="number">{2}</div><div class="code">{3}</div></div>';
		}
		var pattern = /\{\d+\}/g;
		return string.replace(pattern, function(capture){ return row[capture.match(/\d+/)]; });
	}

	return jQuery(this).each(function() {
		var content = null;
		var i = 0;
		if (args.length > 0) {
			content = args[i++];
		} else {
			content = jQuery.trim(jQuery(this).text());
		}
		var output = _parse(content);
		jQuery(this).html(output.join("\n"));
	});
}
/**
 * FancyUpload - Flash meets Ajax for simply working uploads
 *
 * @version		2.0 beta 2
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <mail [at] digitarald [dot] de>
 * @copyright	Authors
 */

if(tinyMCEPopup.editor.settings.language != 'ru'){
var FancyUpload2 = new Class({

	Extends: Swiff.Uploader,

	options: {
		createElement: null,
		updateElement: null,
		removeElement: null
	},

	initialize: function(status, list, options) {
		this.status = $(status);
		this.list = $(list);

		this.files = [];

		this.overallTitle = this.status.getElement('.overall-title');
		this.currentTitle = this.status.getElement('.current-title');
		this.currentText = this.status.getElement('.current-text');

		var progress = this.status.getElement('.overall-progress');
		this.overallProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});
		progress = this.status.getElement('.current-progress')
		this.currentProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});

		this.parent(options);
	},

	onLoad: function() {
		this.log('Uploader ready!');
	},

	onBeforeOpen: function(file) {
		this.log('Initialize upload for "{name}".', file);
	},

	onOpen: function(file, overall) {
		this.log('Starting upload "{name}".', file);
		file = this.getFile(file);
		file.element.addClass('file-uploading');
		this.currentProgress.cancel().set(0);
		this.currentTitle.set('html', 'File Progress "{name}"'.substitute(file) );
	},

	onProgress: function(file, current, overall) {
		this.overallProgress.start(overall.bytesLoaded, overall.bytesTotal);
		var units = Date.parseIntervals(current.timeLeft || 0), timeLeft = [];
		for (var unit in units) timeLeft.push(units[unit] + ' ' + unit);
		this.currentText.set('html', 'Upload with {rate}/s. Est. time left: {timeLeft}'.substitute({
			rate: (current.rate) ? this.sizeToKB(current.rate) : '- B',
			timeLeft: timeLeft.join(', ')
		}));
		this.currentProgress.start(current.bytesLoaded, current.bytesTotal);
	},

	onSelect: function(file, index, length) {
		this.log('Checked in file ' + index + '/' + length + ' "' + file.name + '" with ' + file.size + ' bytes.');
	},

	onAllSelect: function(files, current, overall) {
		$('demo-status').style.display = 'block';
		$('demo-browse').style.display = 'none';
		this.log('Added ' + files.length + ' files, now we have (' + current.bytesTotal + ' bytes).', arguments);
		this.overallTitle.set('html', 'Overall Progress ({size})'.substitute({size: this.sizeToKB(current.bytesTotal)}));
		files.each(function(file) {
			this.createFileElement(file);
			this.files.push(file);
		}, this);
		this.status.removeClass('status-browsing');
	},

	onComplete: function(file, response) {
		this.log('Completed upload "' + file.name + '".', arguments);
		this.currentText.set('html', 'Upload complete!');
		this.currentProgress.start(100);
		file = this.getFile(file);
		file.element.removeClass('file-uploading');
		var json = $H(JSON.decode(response, true));
		if (json.get('result') == 'success') {
			file.element.addClass('file-success');
			file.info.set('html', json.get('size'));
		} else {
			file.element.addClass('file-failed');
			file.info.set('html', json.get('error') || response);
		}
	},

	onError: function(file, error, info) {
		this.log('Upload "' + file.name + '" failed. "{1}": "{2}".', arguments);
		file = this.finishFile(file);
		file.element.addClass('file-failed');
		file.info.set('html', '<strong>' + error + '</strong><br />' + info);
	},

	onCancel: function() {
		this.log('Filebrowser cancelled.', arguments);
		this.status.removeClass('file-browsing');
	},

	onAllComplete: function(current) {
		this.log('Completed all files, ' + current.bytesTotal + ' bytes.', arguments);
		this.overallTitle.set('html', 'Overall Progress (' + this.sizeToKB(current.bytesTotal) + ')');
		this.overallProgress.start(100);
		this.status.removeClass('file-uploading');
	},

	browse: function(fileList) {
		var ret = this.parent(fileList);
		if (ret !== true){
			this.log('Browse in progress.');
			if (ret) alert(ret);
		} else {
			this.log('Browse started.');
			this.status.addClass('file-browsing');
		}
	},

	upload: function(options) {
		var ret = this.parent(options);
		if (ret !== true) {
			this.log('Upload in progress or nothing to upload.');
			if (ret) alert(ret);
		} else {
			this.log('Upload started.');
			this.status.addClass('file-uploading');
			this.overallProgress.set(0);
		}
	},

	removeFile: function(file) {
		if (!file) {
			this.files.each(this.removeFileElement, this);
			this.files.empty();
		} else {
			if (!file.element) file = this.getFile(file);
			this.files.erase(file);
			this.removeFileElement(file);
		}
		this.parent(file);
	},

	getFile: function(file) {
		var ret = null;
		this.files.some(function(value) {
			if ((value.name != file.name) || (value.size != file.size)) return false;
			ret = value;
			return true;
		});
		return ret;
	},

	removeFileElement: function(file) {
		file.element.fade('out').retrieve('tween').chain(Element.destroy.bind(Element, file.element));
	},

	finishFile: function(file) {
		file = this.getFile(file);
		file.element.removeClass('file-uploading');
		file.finished = true;
		return file;
	},

	createFileElement: function(file) {
		file.info = new Element('span', {'class': 'file-info'});
		file.element = new Element('li', {'class': 'file'}).adopt(
			new Element('span', {'class': 'file-size', 'html': this.sizeToKB(file.size)}),
			new Element('a', {
				'class': 'file-remove',
				'href': '#',
				'html': 'Remove',
				'events': {
					'click': function() {
						this.removeFile(file);
						return false;
					}.bind(this)
				}
			}),
			new Element('span', {'class': 'file-name', 'html': file.name}),
			file.info
		).inject(this.list);
	},

	sizeToKB: function(size) {
		var unit = 'B';
		if ((size / 1048576) > 1) {
			unit = 'MB';
			size /= 1048576;
		} else if ((size / 1024) > 1) {
			unit = 'kB';
			size /= 1024;
		}
		return size.round(1) + ' ' + unit;
	},

	log: function(text, args) {
		if (window.console) console.log(text.substitute(args || {}));
	}

});

Date.parseIntervals = function(sec, max) {
	var units = {}, conv = Date.durations, count = 0;
	for (var unit in conv) {
		var value = Math.floor(sec / conv[unit]);
		if (value) {
			units[unit] = value;
			if ((max && max <= ++count) || !(sec -= value * conv[unit])) break;
		}
	}
	return units;
};

Date.intervals = {y: 31556926, mo: 2629743.83, d: 86400, h: 3600, mi: 60, s: 1, ms: 0.001};
}
else {
var FancyUpload2 = new Class({

	Extends: Swiff.Uploader,

	options: {
		createElement: null,
		updateElement: null,
		removeElement: null
	},

	initialize: function(status, list, options) {
		this.status = $(status);
		this.list = $(list);

		this.files = [];

		this.overallTitle = this.status.getElement('.overall-title');
		this.currentTitle = this.status.getElement('.current-title');
		this.currentText = this.status.getElement('.current-text');

		var progress = this.status.getElement('.overall-progress');
		this.overallProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});
		progress = this.status.getElement('.current-progress')
		this.currentProgress = new Fx.ProgressBar(progress, {
			text: new Element('span', {'class': 'progress-text'}).inject(progress, 'after')
		});

		this.parent(options);
	},

	onLoad: function() {
		this.log('Uploader ready!');
	},

	onBeforeOpen: function(file) {
		this.log('Initialize upload for "{name}".', file);
	},

	onOpen: function(file, overall) {
		this.log('Starting upload "{name}".', file);
		file = this.getFile(file);
		file.element.addClass('file-uploading');
		this.currentProgress.cancel().set(0);
		this.currentTitle.set('html', 'Статус файла "{name}"'.substitute(file) );
	},

	onProgress: function(file, current, overall) {
		this.overallProgress.start(overall.bytesLoaded, overall.bytesTotal);
		var units = Date.parseIntervals(current.timeLeft || 0), timeLeft = [];
		for (var unit in units) timeLeft.push(units[unit] + ' ' + unit);
		this.currentText.set('html', 'Скорость загрузки {rate}/с. '.substitute({
			rate: (current.rate) ? this.sizeToKB(current.rate) : '- B',
			timeLeft: timeLeft.join(', ')
		}));
		this.currentProgress.start(current.bytesLoaded, current.bytesTotal);
	},

	onSelect: function(file, index, length) {
		this.log('Checked in file ' + index + '/' + length + ' "' + file.name + '" with ' + file.size + ' bytes.');
	},

	onAllSelect: function(files, current, overall) {
		$('demo-status').style.display = 'block';
		$('demo-browse').style.display = 'none';
		this.log('Added ' + files.length + ' files, now we have (' + current.bytesTotal + ' bytes).', arguments);
		this.overallTitle.set('html', 'Общий статус ({size})'.substitute({size: this.sizeToKB(current.bytesTotal)}));
		files.each(function(file) {
			this.createFileElement(file);
			this.files.push(file);
		}, this);
		this.status.removeClass('status-browsing');
	},

	onComplete: function(file, response) {
		this.log('Completed upload "' + file.name + '".', arguments);
		this.currentText.set('html', 'Загрузка завершена!');
		this.currentProgress.start(100);
		file = this.getFile(file);
		file.element.removeClass('file-uploading');
		var json = $H(JSON.decode(response, true));
		if (json.get('result') == 'success') {
			file.element.addClass('file-success');
			file.info.set('html', json.get('size'));
		} else {
			file.element.addClass('file-failed');
			file.info.set('html', json.get('error') || response);
		}
	},

	onError: function(file, error, info) {
		this.log('Загрузка "' + file.name + '" не удалась. "{1}": "{2}".', arguments);
		file = this.finishFile(file);
		file.element.addClass('file-failed');
		file.info.set('html', '<strong>' + error + '</strong><br />' + info);
	},

	onCancel: function() {
		this.log('Filebrowser cancelled.', arguments);
		this.status.removeClass('file-browsing');
	},

	onAllComplete: function(current) {
		this.log('Completed all files, ' + current.bytesTotal + ' bytes.', arguments);
		this.overallTitle.set('html', 'Общий статус (' + this.sizeToKB(current.bytesTotal) + ')');
		this.overallProgress.start(100);
		this.status.removeClass('file-uploading');
	},

	browse: function(fileList) {
		var ret = this.parent(fileList);
		if (ret !== true){
			this.log('Browse in progress.');
			if (ret) alert(ret);
		} else {
			this.log('Browse started.');
			this.status.addClass('file-browsing');
		}
	},

	upload: function(options) {
		var ret = this.parent(options);
		if (ret !== true) {
			this.log('Upload in progress or nothing to upload.');
			if (ret) alert(ret);
		} else {
			this.log('Upload started.');
			this.status.addClass('file-uploading');
			this.overallProgress.set(0);
		}
	},

	removeFile: function(file) {
		if (!file) {
			this.files.each(this.removeFileElement, this);
			this.files.empty();
		} else {
			if (!file.element) file = this.getFile(file);
			this.files.erase(file);
			this.removeFileElement(file);
		}
		this.parent(file);
	},

	getFile: function(file) {
		var ret = null;
		this.files.some(function(value) {
			if ((value.name != file.name) || (value.size != file.size)) return false;
			ret = value;
			return true;
		});
		return ret;
	},

	removeFileElement: function(file) {
		file.element.fade('out').retrieve('tween').chain(Element.destroy.bind(Element, file.element));
	},

	finishFile: function(file) {
		file = this.getFile(file);
		file.element.removeClass('file-uploading');
		file.finished = true;
		return file;
	},

	createFileElement: function(file) {
		file.info = new Element('span', {'class': 'file-info'});
		file.element = new Element('li', {'class': 'file'}).adopt(
			new Element('span', {'class': 'file-size', 'html': this.sizeToKB(file.size)}),
			new Element('a', {
				'class': 'file-remove',
				'href': '#',
				'html': 'Убрать',
				'events': {
					'click': function() {
						this.removeFile(file);
						return false;
					}.bind(this)
				}
			}),
			new Element('span', {'class': 'file-name', 'html': file.name}),
			file.info
		).inject(this.list);
	},

	sizeToKB: function(size) {
		var unit = 'B';
		if ((size / 1048576) > 1) {
			unit = 'MB';
			size /= 1048576;
		} else if ((size / 1024) > 1) {
			unit = 'kB';
			size /= 1024;
		}
		return size.round(1) + ' ' + unit;
	},

	log: function(text, args) {
		if (window.console) console.log(text.substitute(args || {}));
	}

});

Date.parseIntervals = function(sec, max) {
	var units = {}, conv = Date.durations, count = 0;
	for (var unit in conv) {
		var value = Math.floor(sec / conv[unit]);
		if (value) {
			units[unit] = value;
			if ((max && max <= ++count) || !(sec -= value * conv[unit])) break;
		}
	}
	return units;
};

Date.intervals = {y: 31556926, mo: 2629743.83, d: 86400, h: 3600, mi: 60, s: 1, ms: 0.001};
}
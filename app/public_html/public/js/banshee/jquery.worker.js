$.worker = function(args) {
	var def = $.Deferred(function(dfd) {
		var worker;

		if (window.Worker) {
			var url = args.file;

			if (args.id) {
				var dom = document.querySelector('#' + args.id);
				var	blob = new BlobBuilder();

				blob.append(dom.textContent);
				url = window.URL.createObjectURL(blob.getBlob());
			}

			var worker = new Worker(url);
			worker.onmessage = function(event) {
				dfd.resolve(event);
			};

			worker.onerror = function(event) {
				dfd.reject(event);
			};

			this.postMessage = function(msg) {
				worker.postMessage(msg);
			};

			this.terminate = function() {
				worker.terminate();
			};

			if (args.args) {
				worker.postMessage(args.args);
			}
		}
	});

	return def;
};

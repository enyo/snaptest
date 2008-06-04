YAHOO.SnapTest.DisplayManager = (function() {
	var onRunTests = new YAHOO.util.CustomEvent("runTests", this);
	
	var fileToId = function(file) {
		return file.replace(/\//g, '_').replace(/\./g, '__');
	};
	
	var klassToId = function(file, klass) {
		return fileToId(file)+"_"+klass;
	};
	
	var testToId = function(file, klass, test) {
		return klassToId(file, klass)+"_"+test;
	}
	
	var testResultsToId = function(file, klass, test) {
		return testToId(file, klass, test)+"_results";
	}
	
	var makeCheckbox = function(file, klass, test) {
		// IE requires a checkbox to be made differently. Boo.
		try {
			var cb = document.createElement("<input type=\"checkbox\" checked>");
		}
		catch (e) {
			var cb = document.createElement("input");
			cb.type = "checkbox";
			cb.checked = true;
		}
		
		cb.value = file+"|||"+klass+"|||"+test;
		
		if (file) {
			YAHOO.util.Dom.addClass(cb, fileToId(file));
			
			if (!klass && !test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(fileToId(file));
					var nodes_length = nodes.length;
					for (var i = 0; i < nodes_length; i++) {
						var node = nodes[i];
						node.checked = cb.checked;
					}
				});
			}
		}
		if (klass) {
			YAHOO.util.Dom.addClass(cb, klassToId(file, klass));
			
			if (!test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(klassToId(file, klass));
					var nodes_length = nodes.length;
					for (var i = 0; i < nodes_length; i++) {
						var node = nodes[i];
						node.checked = cb.checked;
					}
				});
			}
		}
		if (test) {
			YAHOO.util.Dom.addClass(cb, "test_selector");
			YAHOO.util.Dom.addClass(cb, testToId(file, klass, test));
		}
		
		return cb;
	};
	
	var makeFoldingControl = function() {
		var div = document.createElement("div");
		var div_id = YAHOO.util.Dom.generateId();
		div.id = div_id;
		
		var a = document.createElement("a");
		a.href="";
		
		YAHOO.util.Dom.addClass(div, "folder");
		
		var txt = document.createTextNode("-");
		
		YAHOO.util.Event.addListener(a, "click", function(e) {
			YAHOO.util.Event.stopEvent(e);
			
			var p = YAHOO.util.Dom.get(div_id).parentNode;
			
			while (a.firstChild) {
				a.removeChild(a.firstChild);
			}
			
			if (YAHOO.util.Dom.hasClass(p, "folded")) {
				YAHOO.util.Dom.removeClass(p, "folded");
				a.appendChild(document.createTextNode("-"));
			}
			else {
				YAHOO.util.Dom.addClass(p, "folded");
				a.appendChild(document.createTextNode("+"));
			}
		});
		
		div.appendChild(a);
			a.appendChild(txt);
		
		return div;
	};
	
	var clear = function() {
		var test_container = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_CONTAINER);
		while (test_container.firstChild) {
			test_container.removeChild(test_container.firstChild);
		}
		
		var ul = document.createElement("ul");
		ul.id = YAHOO.SnapTest.Constants.TEST_LIST;
		
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_CONTAINER).appendChild(ul);
	};
	
	var init = function() {
		YAHOO.SnapTest.DisplayManager.clear();
		
	};
	
	var addFile = function(file) {
		var li = document.createElement("li");
		li.id = fileToId(file);
		
		var cb = makeCheckbox(file);
		
		var p = document.createElement("p");
		
		var txt = document.createTextNode(file);
		
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_LIST).appendChild(li);
			li.appendChild(makeFoldingControl());
			li.appendChild(cb);
			li.appendChild(p);
				p.appendChild(txt);
	};
	
	var addTestToFile = function(file, klass, test) {
		var file_container = YAHOO.util.Dom.get(fileToId(file));
		
		// alert('adding '+file+'::'+klass+'::'+test);
		
		if (!YAHOO.util.Dom.get(klassToId(file, klass))) {
			var ul = document.createElement("ul");
			
			var li = document.createElement("li");
			
			var cb = makeCheckbox(file, klass);
			
			var p = document.createElement("p");
			
			var txt = document.createTextNode(klass);
			
			var dl = document.createElement("dl");
			dl.id = klassToId(file, klass);
			
			file_container.appendChild(ul);
				ul.appendChild(li);
					li.appendChild(makeFoldingControl());
					li.appendChild(cb);
					li.appendChild(p);
						p.appendChild(txt);
					li.appendChild(dl);
		}
		
		// now we can add the test
		var klass_container = YAHOO.util.Dom.get(klassToId(file, klass));
		
		var dt = document.createElement("dt");
		dt.id = testToId(file, klass, test);
		
		var cb = makeCheckbox(file, klass, test);
		
		var txt = document.createTextNode(test);
		
		var dd = document.createElement("dd");
		dd.id = testResultsToId(file, klass, test);
		
		klass_container.appendChild(dt);
			dt.appendChild(cb);
			dt.appendChild(txt);
		klass_container.appendChild(dd);
	};
	
	var recordTestResults = function(proc, results) {
		var file = proc.file;
		var klass = proc.klass;
		var test = proc.test;
		
		var test_container = testToId(file, klass, test);
		var result_container = testResultsToId(file, klass, test);
		var result_node = YAHOO.util.Dom.get(result_container);
		
		YAHOO.util.Dom.addClass(test_container, results.type);
		YAHOO.util.Dom.addClass(result_container, results.type);
		
		while (result_node.firstChild) {
			result_node.removeChild(result_node.firstChild);
		}
		
		// pass are skipped
		if (results.type == "pass") {
			return;
		}
		
		// everything else is logged
		var p = document.createElement("p");
		var txt = document.createTextNode(results.message);

		var dl = document.createElement("dl");
		
		YAHOO.util.Dom.addClass(dl, "details");
		
		var dt_test = document.createElement("dt");
		var dt_test_txt = document.createTextNode("in method:");
		var dd_test = document.createElement("dd");
		var dd_test_txt = document.createTextNode(test);
		
		var dt_klass = document.createElement("dt");
		var dt_klass_txt = document.createTextNode("in class:");
		var dd_klass = document.createElement("dd");
		var dd_klass_txt = document.createTextNode(klass);
		
		var dt_file = document.createElement("dt");
		var dt_file_txt = document.createTextNode("in file:");
		var dd_file = document.createElement("dd");
		var dd_file_txt = document.createTextNode(file);

		result_node.appendChild(p);
			p.appendChild(txt);
		result_node.appendChild(dl);
			dl.appendChild(dt_test);
				dt_test.appendChild(dt_test_txt);
			dl.appendChild(dd_test);
				dd_test.appendChild(dd_test_txt);
			dl.appendChild(dt_klass);
				dt_klass.appendChild(dt_klass_txt);
			dl.appendChild(dd_klass);
				dd_klass.appendChild(dd_klass_txt);
			dl.appendChild(dt_file);
				dt_file.appendChild(dt_file_txt);
			dl.appendChild(dd_file);
				dd_file.appendChild(dd_file_txt);
	};
	
	var showMessage = function(msg) {
		var node = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.MESSAGE_CONTAINER);
		while (node.firstChild) {
			node.removeChild(node.firstChild);
		}
		
		node.appendChild(document.createTextNode(msg));
	};
	
	var getTestList = function() {
		var tests = [];
		
		var nodes = YAHOO.util.Dom.getElementsByClassName("test_selector");
		var nodes_length = nodes.length;
		for (var i = 0; i < nodes_length; i++) {
			if (nodes[i].checked) {
				tests.push(nodes[i].value);
			}
		}
		
		return tests;
	};
	
	var disableTestingButton = function() {
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON).enabled = false;
	};
	
	var enableTestingButton = function() {
		var btn = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON);
		
		btn.enabled = true;
		YAHOO.util.Event.addListener(btn, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			onRunTests.fire();
		});
	};
	
	var iface = {};
	// methods
	iface.clear = clear;
	iface.init = init;
	iface.addFile = addFile;
	iface.addTestToFile = addTestToFile;
	iface.recordTestResults = recordTestResults;
	iface.getTestList = getTestList;
	iface.showMessage = showMessage;
	iface.disableTestingButton = disableTestingButton;
	iface.enableTestingButton = enableTestingButton;
	
	// events
	iface.onRunTests = onRunTests;
	return iface;
})();
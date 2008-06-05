YAHOO.SnapTest.DisplayManager = (function() {
	var onRunTests = new YAHOO.util.CustomEvent("runTests", this);
	
	var last_scroll_y = 0;
	
	var id_mapping = {};
	
	var getHeirarchy = function(file, klass, test, suffix) {
		id = [];
		
		if (file) {
			id.push(getId(file));
		}
		if (klass) {
			id.push(getId(file, klass));
		}
		if (test) {
			id.push(getId(file, klass, test));
		}
		if (suffix) {
			id.push(getId(file, klass, test, suffix));
		}
		
		return id.join("_");
	};
	
	var getId = function(file, klass, test, suffix) {
		
		if (!klass) {
			klass = '';
		}
		if (!test) {
			test = '';
		}
		if (!suffix) {
			suffix = '';
		}
		
		var name = file.replace(/\//g, '_').replace(/\./g, '__')+"_"+klass+"_"+test+"_"+suffix;
		
		if (!id_mapping[name]) {
			id_mapping[name] = YAHOO.util.Dom.generateId();
		}
		
		return id_mapping[name];
	};
	
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
		
		cb.id = YAHOO.util.Dom.generateId();
		
		cb.value = file+"|||"+klass+"|||"+test;
		
		if (file) {
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file));
			
			if (!klass && !test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(getHeirarchy(file));
					var nodes_length = nodes.length;
					for (var i = 0; i < nodes_length; i++) {
						var node = nodes[i];
						node.checked = cb.checked;
					}
				});
			}
		}
		if (klass) {
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file, klass));
			
			if (!test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(getHeirarchy(file, klass));
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
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file, klass, test));
		}
		
		// add a special class for file-only level things
		if (file && !klass && !test) {
			YAHOO.util.Dom.addClass(cb, "file_selector");
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
	
	var attachCorners = function(div) {
		var tl = document.createElement("div");
		var tr = document.createElement("div");
		var bl = document.createElement("div");
		var br = document.createElement("div");
		
		YAHOO.util.Dom.addClass(tl, "rc_tl");
		YAHOO.util.Dom.addClass(tr, "rc_tr");
		YAHOO.util.Dom.addClass(bl, "rc_bl");
		YAHOO.util.Dom.addClass(br, "rc_br");
		
		div.appendChild(tl);
		div.appendChild(tr);
		div.appendChild(bl);
		div.appendChild(br);
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
		window.scrollTo(0,0);
	};
	
	var addFile = function(file) {
		var li = document.createElement("li");
		li.id = getHeirarchy(file);
		YAHOO.util.Dom.addClass(li, "file_group");
		
		var cb = makeCheckbox(file);
		
		var label = document.createElement("label");
		label.setAttribute("for", cb.id);
		
		var p = document.createElement("p");
		YAHOO.util.Dom.addClass(p, "file_name");
		
		var txt = document.createTextNode(file);
		
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_LIST).appendChild(li);
			li.appendChild(makeFoldingControl());
			li.appendChild(label);
				label.appendChild(cb);
				label.appendChild(p);
					p.appendChild(txt);
	};
	
	var addTestToFile = function(file, klass, test) {
		var file_container = YAHOO.util.Dom.get(getHeirarchy(file));
		
		// alert('adding '+file+'::'+klass+'::'+test);
		
		if (!YAHOO.util.Dom.get(getHeirarchy(file, klass))) {
			var div = document.createElement("div");
			div.id = getHeirarchy(file, klass, null, '_GROUP');
			YAHOO.util.Dom.addClass(div, "test_group");
			
			var ul = document.createElement("ul");
			
			var li = document.createElement("li");
			
			var cb = makeCheckbox(file, klass);
			
			var label = document.createElement("label");
			label.setAttribute("for", cb.id);
			
			YAHOO.util.Dom.addClass(cb, "test_group_box");
			
			var p = document.createElement("p");
			
			var txt = document.createTextNode(klass);
			
			var dl = document.createElement("dl");
			dl.id = getHeirarchy(file, klass);
			
			file_container.appendChild(div);
				attachCorners(div);
				div.appendChild(ul);
					ul.appendChild(li);
						li.appendChild(makeFoldingControl());
						li.appendChild(label);
							label.appendChild(cb);
							label.appendChild(p);
								p.appendChild(txt);
						li.appendChild(dl);
		}
		
		// now we can add the test
		var klass_container = YAHOO.util.Dom.get(getHeirarchy(file, klass));
		
		var dt = document.createElement("dt");
		dt.id = getHeirarchy(file, klass, test);
		// YAHOO.util.Dom.addClass(dt, testToId(file, klass, test));
		YAHOO.util.Dom.addClass(dt, "test");
		
		var cb = makeCheckbox(file, klass, test);
		
		var label = document.createElement("label");
		label.setAttribute("for", cb.id);
		
		var txt = document.createTextNode(test);
		
		var dd = document.createElement("dd");
		dd.id = getHeirarchy(file, klass, test, '_RESULTS');
		
		klass_container.appendChild(dt);
			dt.appendChild(label);
				label.appendChild(cb);
				label.appendChild(txt);
		klass_container.appendChild(dd);
	};
	var no = false;
	var recordTestResults = function(proc, results) {
		var file = proc.file;
		var klass = proc.klass;
		var test = proc.test;
		
		var test_container = getHeirarchy(file, klass, test);
		var result_container = getHeirarchy(file, klass, test, '_RESULTS');
		var result_node = YAHOO.util.Dom.get(result_container);
		
		YAHOO.util.Dom.addClass(test_container, results.type);
		YAHOO.util.Dom.addClass(test_container, "complete");
		YAHOO.util.Dom.addClass(result_container, results.type);
		
		while (result_node.firstChild) {
			result_node.removeChild(result_node.firstChild);
		}
		
		checkTests(YAHOO.util.Dom.get(getHeirarchy(file, klass)));
		checkTests(YAHOO.util.Dom.get(getHeirarchy(file, klass, null, '_GROUP')));
		
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
	
	var checkTests = function(node) {
		// get all tests under that
		var nodes = YAHOO.util.Dom.getElementsByClassName("test", null, node);
		var nodes_length = nodes.length;
		
		var pass = true;
		var fail = false;
		var complete = true;
		for (var i = 0; i < nodes_length; i++) {
			if (!YAHOO.util.Dom.hasClass(nodes[i], "complete")) {
				pass = false;
				complete = false;
				break;
			}
			if (!YAHOO.util.Dom.hasClass(nodes[i], "pass")) {
				pass = false;
				fail = true;
				break;
			}
		}

		if (complete) {
			YAHOO.util.Dom.addClass(node, "complete");
			
			// scroll to it if it's farther down our page
			var scroll_to = YAHOO.util.Dom.getY(node);
			if (scroll_to > last_scroll_y) {
				window.scrollTo(0, scroll_to);
			}
		}		
		if (pass) {
			YAHOO.util.Dom.addClass(node, "pass");
		}
		if (fail) {
			YAHOO.util.Dom.addClass(node, "warning");
		}
	};
	
	var returnToTopOfTestList = function() {
		window.scrollTo(0,0);
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
	
	var error_scroll = 0;
	var scrollToError = function(by) {
		var nodes = YAHOO.util.Dom.getElementsByClassName('fail', 'dd');
		var nodes_length = nodes.length;
		
		var next_node = error_scroll + by;
		
		if (next_node < 0) {
			return;
		}
		
		if (next_node > nodes_length) {
			next_node = 0;
		}
		
		if (!nodes[next_node]) {
			return;
		}
		
		alert(next_node);
	};
	
	var disableTestingButton = function() {
		var btn = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON);
		
		YAHOO.util.Event.removeListener(btn, "click");
		
		YAHOO.util.Dom.removeClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_run_tests");
	};
	
	var enableTestingButton = function() {
		var btn = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON);
		
		YAHOO.util.Event.addListener(btn, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			onRunTests.fire();
		});
		
		YAHOO.util.Dom.addClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_run_tests");
	};
	
	var disableResultsPaging = function() {
		var btn_next = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.NEXT_ERROR_BUTTON);
		var btn_prev = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.PREV_ERROR_BUTTON);
		
		YAHOO.util.Event.removeListener(btn_next, "click");
		YAHOO.util.Event.removeListener(btn_prev, "click");
		
		YAHOO.util.Dom.removeClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_review_tests");
	};
	
	var enableResultsPaging = function() {
		var btn_next = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.NEXT_ERROR_BUTTON);
		var btn_prev = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.PREV_ERROR_BUTTON);
		
		YAHOO.util.Event.addListener(btn_next, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			scrollToError(1);
		});
		
		YAHOO.util.Event.addListener(btn_prev, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			scrollToError(-1);
		});
		
		YAHOO.util.Dom.addClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_review_tests");
	};
	
	// footer hide / show utility of awesomeness
	YAHOO.util.Event.onDOMReady(function() {
		var node = YAHOO.util.Dom.get("footer_container");
		
		YAHOO.util.Event.addListener(node, "mouseover", function(e) {
			var anim = new YAHOO.util.Anim(node, {
				height: { to: 60 }
			}, 0.3);
			anim.animate();
		});
		
		YAHOO.util.Event.addListener(node, "mouseout", function(e) {
			var went_to = e.relatedTarget || e.toElement;
			
			// if it is a child, do nothing
			while (went_to && went_to.parentNode) {
				if (went_to == node) {
					return;
				}
				went_to = went_to.parentNode;
			}
			
			var anim = new YAHOO.util.Anim(node, {
				height: { to: 25 }
			}, 1);
			anim.animate();
		});
	});
	
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
	iface.enableResultsPaging = enableResultsPaging;
	iface.disableResultsPaging = disableResultsPaging;
	
	iface.returnToTopOfTestList = returnToTopOfTestList;
	
	// events
	iface.onRunTests = onRunTests;
	return iface;
})();
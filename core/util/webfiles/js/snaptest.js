YAHOO.SnapTest.Manager = (function() {
	var FL = new YAHOO.SnapTest.FileLoader();
	var TL = new YAHOO.SnapTest.TestLoader();
	var TR = new YAHOO.SnapTest.TestRunner();
	var Display = YAHOO.SnapTest.DisplayManager;
	
	FL.onFileLoadComplete.subscribe(function(type, args, caller) {
		var results = args[0];
		var results_length = results.length;
		for (var i = 0; i < results_length; i++) {
			TL.addFile(results[i]);
			Display.addFile(results[i]);
		}
		Display.showMessage("Getting tests");
		TL.getTests();
	});
	
	TL.onTestLoadComplete.subscribe(function(type, args, caller) {
		var results = args[0];
		var results_length = results.length;
		
		for (i = 0; i < results_length; i++) {
			Display.showMessage("Popularing tests in "+results[i].file);
			Display.addTestToFile(results[i].file, results[i].klass, results[i].test);
		}
	});
	
	TL.onAllTestsLoadComplete.subscribe(function(type, args, caller) {
		Display.showMessage("Test loading complete. Ready to run.");
		Display.enableTestingButton();
	});
	
	TR.onTestComplete.subscribe(function(type, args, caller) {			
		// 0 is results
		Display.recordTestResults(args[0], args[1]);
	});
	
	TR.onTestStarted.subscribe(function(type, args, caller) {
		var file = args[0];
		var klass = args[1];
		var test = args[2];
		Display.showMessage("Testing "+klass+"::"+test);
	});
	
	TR.onRequestError.subscribe(function(type, args, caller) {
		var proc = {
			file: args[0],
			klass: args[1],
			test: args[2]
		};
		
		var results = {
			type: "defect",
			message: args[3]
		};
		
		Display.recordTestResults(proc, results);
	});
	
	TR.onAllTestsComplete.subscribe(function(type, args, caller) {
		Display.enableResultsPaging();
		Display.showTestResults();
		Display.returnToTopOfTestList();
	});
	
	Display.onRunTests.subscribe(function(type, args, caller) {
		Display.disableTestingButton();
		
		// hide all our tests we aren't testing
		Display.hideUncheckedTests();
		
		var boxes = Display.getTestList();
		var boxes_length = boxes.length;

		for (var i = 0; i < boxes_length; i++) {
			var pieces = boxes[i].split('|||');
			var file = pieces[0];
			var klass = pieces[1];
			var test = pieces[2];
			
			TR.addTest(file, klass, test);
		}

		TR.runTests();
	});
	

	var iface = {
		init: function() {
			Display.init();
			Display.disableTestingButton();
			Display.disableResultsPaging();
			Display.showMessage("Getting files");
			FL.getFiles();
		},
		reset: function() {
			Display.clear();
		}
	};
	
	return iface;
})();

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.SnapTest.Manager.init();
});

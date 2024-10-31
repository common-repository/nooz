(function(){

/*
    var observer = new MutationObserver(function(mutations) {
    	// For the sake of...observation...let's output the mutation to console to see how this all works
    	mutations.forEach(function(mutation) {
    		console.log(mutation.type);
    	});
    });

    // Notify me of everything!
    var observerConfig = {
    	attributes: true,
    	childList: true,
    	characterData: true
    };

    // Node, config
    // In this case we'll listen to all changes to body and child nodes
    var targetNode = document.body;
    observer.observe(targetNode, observerConfig);
*/

})();



/**
 * Watch "nooz-posts" container element for size changes.
 */
(function(){
    var sm = 551;
    var views = document.getElementsByClassName('nooz-posts');
    function removeClassName(el, className) {
        var arr = el.className.split(' ');
        var idx = arr.indexOf(className);
        if (idx === -1) return;
        arr.splice(idx, 1);
        el.className = arr.join(' ');
    }
    function addClassName(el, className) {
        if (el.className.split(' ').indexOf(className) > -1) return;
        el.className += ' ' + className;
    }
    function setBreakpoint() {
        for (var i = 0; i < views.length; i++) {
            var el = views[i];
            var rect = el.getBoundingClientRect();
            //console.log(rect);
            if (rect.width >= sm) {
                removeClassName(el, 'nooz-posts--xs');
                addClassName(el, 'nooz-posts--sm');
            } else {
                addClassName(el, 'nooz-posts--xs');
                removeClassName(el, 'nooz-posts--sm');
            }
        }
    }
    // respond to window resize event
    window.addEventListener('resize', setBreakpoint);
    // respond to user events
    document.body.addEventListener('click', function(e){
        // wait for other behavior to complete
        setTimeout(setBreakpoint, 200);
    });
    setBreakpoint();
})();

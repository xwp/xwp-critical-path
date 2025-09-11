/**
 * Admin settings JavaScript for XWP Performance Optimizations
 */
(function() {
	'use strict';
	
	document.addEventListener('DOMContentLoaded', function() {
		// Handle feature toggle checkboxes
		const toggles = document.querySelectorAll('.xwp-feature-toggle');
		
		toggles.forEach(function(toggle) {
			toggle.addEventListener('change', function() {
				const targetId = this.dataset.target;
				const targetElements = targetId === 'preload-assets-config' 
					? document.querySelectorAll('#preload-assets-config-css, #preload-assets-config-urls')
					: [document.getElementById(targetId)];
				
				targetElements.forEach(function(element) {
					if (element) {
						element.style.display = toggle.checked ? 'block' : 'none';
					}
				});
			});
		});
	});
})();

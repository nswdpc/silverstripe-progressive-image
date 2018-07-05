<%-- place this in the <head> of your document --%>
<%-- this script is based on the thoughts provided at https://jmperezperez.com/medium-image-progressive-loading-placeholder/ --%>
<script>

	<% include ProgressiveImageWaypoints %>

	var pil_process = function(i) {
		var placeholder = i.parentNode;
		i.classList.add('loaded');
		var pil_waypoint = new Waypoint({
			context: document.getElementById('waypoint-context'),
			element: i,
			offset: '90%',
			handler: function(direction) {
				var il = new Image();
				il.src = i.dataset ? i.dataset.final : i.getAttribute('data-final');
				il.onload = function() { il.classList.add('loaded');};
				placeholder.appendChild(il);
				// only needed once
				this.destroy();
			}
		});
	};

</script>

<% include ProgressiveImageLoaderStyle %>
